<?php

namespace App\Http\Controllers\Action;

use App\Helpers\RequestIdHelper;
use App\Http\Controllers\Controller;
use App\Models\Service;
use App\Models\Transaction;
use App\Models\Wallet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AirtimeController extends Controller
{
    protected $loginUserId;

    public function __construct()
    {
        $this->loginUserId = Auth::id();
    }

    /**
     * Show Airtime purchase form
     */
    public function airtime()
    {
        $user = Auth::user();

        if (!$user) {
            return redirect()->route('login')->with('error', 'Please log in to access this page.');
        }

        $wallet = Wallet::firstOrCreate(
            ['user_id' => $user->id],
            ['balance' => 0.00, 'status' => 'active']
        );

        return view('utilities.index', [
            'user'   => $user,
            'wallet' => $wallet,
        ]);
    }

    /**
     * Handle Airtime Purchase
     */
    /**
     * Handle Airtime Purchase
     */
    public function buyAirtime(Request $request)
    {
        $request->validate([
            'network'   => ['required', 'string', 'in:mtn,airtel,glo,etisalat'],
            'mobileno'  => 'required|numeric|digits:11',
            'amount'    => 'required|numeric|min:50|max:10000',
        ]);

        $user   = Auth::user();
        $networkKey = strtolower($request->network); // mtn, airtel, etc.
        $mobile  = $request->mobileno;
        $amount  = $request->amount;
        $requestId = RequestIdHelper::generateRequestId();

        // 1. Find the Airtime Service
        $service = Service::where('name', 'Airtime')->first();
        if (!$service) {
            // Fallback if 'Airtime' service doesn't exist, maybe try 'Utility' or just proceed with 0 discount
            // For now, let's assume it exists or create a fallback
             $service = Service::firstOrCreate(['name' => 'Airtime'], ['status' => 'active']);
        }

        // 2. Find the specific Network Field (e.g., MTN)
        // We assume ServiceField stores the network name in 'field_name' or 'field_code'
        $serviceField = \App\Models\ServiceField::where('service_id', $service->id)
            ->where(function($q) use ($networkKey) {
                $q->where('field_name', 'LIKE', "%{$networkKey}%")
                  ->orWhere('field_code', 'LIKE', "%{$networkKey}%");
            })->first();

        // 3. Calculate Discount
        $discountPercentage = 0;
        if ($serviceField) {
            // Check for specific price/discount for this user type
            // Assuming 'role' column exists in users table, or default to 'agent'/'user'
            $userType = $user->role ?? 'personal'; 
            
            // Try to get price from ServicePrice
            $servicePrice = \App\Models\ServicePrice::where('service_fields_id', $serviceField->id)
                ->where('role', $userType)
                ->first();

            if ($servicePrice) {
                $discountPercentage = $servicePrice->price; // e.g., 10 means 10%
            } else {
                $discountPercentage = $serviceField->base_price ?? 0; 
            }
        }

        // If discount is 10, it means 10% off.
        // Payable = Amount - (Amount * 10 / 100)
        $discountAmount = ($amount * $discountPercentage) / 100;
        $payableAmount = $amount - $discountAmount;

        // 4. Check Wallet Balance
        $wallet = Wallet::where('user_id', $user->id)->first();
        if (!$wallet || $wallet->balance < $payableAmount) {
            return redirect()->back()->with('error', 'Insufficient wallet balance! You need ₦' . number_format($payableAmount, 2));
        }

        // 5. Call Airtime API
        try {
            $response = Http::withHeaders([
                'api-key'    => env('API_KEY'),
                'secret-key' => env('SECRET_KEY'),
            ])->post(env('MAKE_PAYMENT'), [
                'request_id' => $requestId,
                'serviceID'  => $networkKey,
                'amount'     => $amount, // Send full amount to provider
                'phone'      => $mobile,
            ]);

        } catch (\Exception $e) {
            Log::error('Airtime API Connection Error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Could not connect to airtime provider. Please try again later.');
        }

        // 6. Process Response
        $data = $response->json();
        $successCodes = ['0', '00', '000', '200'];
        $isSuccessful = false;
        
        if ($response->successful()) {
             if (isset($data['code']) && in_array((string)$data['code'], $successCodes)) {
                $isSuccessful = true;
            } elseif (isset($data['status']) && strtolower($data['status']) === 'success') {
                $isSuccessful = true;
            }
        }

        if ($isSuccessful) {
            // Deduct Wallet (Payable Amount)
            $oldBalance = $wallet->balance;
            $wallet->decrement('balance', $payableAmount);
            $newBalance = $wallet->balance;

            // Create Transaction Record
            Transaction::create([
                'transaction_ref' => $requestId,
                'user_id'         => $user->id,
                'amount'          => $payableAmount, // Amount deducted
                'description'     => "Airtime purchase of ₦{$amount} for {$mobile} ({$networkKey})",
                'type'            => 'debit',
                'status'          => 'completed',
                'metadata'        => json_encode([
                    'phone'        => $mobile,
                    'network'      => $networkKey,
                    'original_amt' => $amount,
                    'discount'     => $discountAmount,
                    'api_response' => $data,
                ]),
                'performed_by' => $user->first_name . ' ' . $user->last_name,
                'approved_by'  => $user->id,
            ]);

            // Create Report Record (as requested)
            \App\Models\Report::create([
                'user_id'      => $user->id,
                'phone_number' => $mobile,
                'network'      => $networkKey,
                'ref'          => $requestId,
                'amount'       => $amount, // Face value
                'status'       => 'successful',
                'type'         => 'airtime',
                'description'  => "Airtime purchase for {$mobile}",
                'old_balance'  => $oldBalance,
                'new_balance'  => $newBalance,
                'service_id'   => $serviceField ? $serviceField->id : null,
            ]);

            return redirect()->route('thankyou')->with([
                'success' => 'Airtime purchase successful!',
                'ref'     => $requestId,
                'mobile'  => $mobile,
                'amount'  => $amount,
                'paid'    => $payableAmount
            ]);
        }

        Log::error('Airtime API Response Error', ['response' => $data]);
        return redirect()->back()->with('error', 'Airtime purchase failed. Provider response: ' . ($data['message'] ?? 'Unknown error'));
    }
}
