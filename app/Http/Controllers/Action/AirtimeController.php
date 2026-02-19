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
use Illuminate\Support\Facades\DB;


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
        
        // 0. Preliminary Status Checks
        if ($user->status !== 'active') {
             return redirect()->back()->with('error', "Your account is currently {$user->status}. Access denied.");
        }

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
            // Assuming 'user_type' column exists in users table, or default to 'agent'/'user'
            $userType = $user->user_type ?? 'personal'; 
            
            // Try to get price from ServicePrice
            $servicePrice = \App\Models\ServicePrice::where('service_fields_id', $serviceField->id)
                ->where('user_type', $userType)
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

        if ($wallet->status !== 'active') {
            return redirect()->back()->with('error', 'Your wallet is not active. Please contact support.');
        }

        DB::beginTransaction();

        try {
            // 5. Create Preliminary Records & Charge Wallet
            $oldBalance = $wallet->balance;
            $wallet->decrement('balance', $payableAmount);
            $newBalance = $wallet->balance;

            // Create Transaction Record (Pending)
            $transaction = Transaction::create([
                'transaction_ref' => $requestId,
                'user_id'         => $user->id,
                'amount'          => $payableAmount,
                'description'     => "Airtime purchase of ₦{$amount} for {$mobile} ({$networkKey})",
                'type'            => 'debit',
                'status'          => 'pending',
                'performed_by'    => $user->first_name . ' ' . $user->last_name,
                'approved_by'     => $user->id,
            ]);

            // Create Report Record (Pending)
            $report = \App\Models\Report::create([
                'user_id'      => $user->id,
                'phone_number' => $mobile,
                'network'      => $networkKey,
                'ref'          => $requestId,
                'amount'       => $amount,
                'status'       => 'pending',
                'type'         => 'airtime',
                'description'  => "Airtime purchase for {$mobile}",
                'old_balance'  => $oldBalance,
                'new_balance'  => $newBalance,
                'service_id'   => $serviceField ? $serviceField->id : null,
            ]);

            // 6. Call Airtime API
            $response = Http::withHeaders([
                'api-key'    => env('API_KEY'),
                'secret-key' => env('SECRET_KEY'),
            ])->post(env('MAKE_PAYMENT'), [
                'request_id' => $requestId,
                'serviceID'  => $networkKey,
                'amount'     => $amount,
                'phone'      => $mobile,
            ]);

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
                // Finalize Records
                $transaction->update([
                    'status'   => 'completed',
                    'metadata' => json_encode([
                        'phone'        => $mobile,
                        'network'      => $networkKey,
                        'original_amt' => $amount,
                        'discount'     => $discountAmount,
                        'api_response' => $data,
                    ]),
                ]);

                $report->update(['status' => 'successful']);

                DB::commit();

                return redirect()->route('thankyou')->with([
                    'success' => 'Airtime purchase successful!',
                    'ref'     => $requestId,
                    'mobile'  => $mobile,
                    'amount'  => $amount,
                    'paid'    => $payableAmount
                ]);
            }

            // API Failed - REFUND
            $wallet->increment('balance', $payableAmount);
            $transaction->update(['status' => 'failed']);
            $report->update([
                'status'      => 'failed',
                'description' => "Failed: " . ($data['message'] ?? 'Unknown error'),
            ]);

            DB::commit();
            Log::error('Airtime API Response Error', ['response' => $data]);
            return redirect()->back()->with('error', 'Airtime purchase failed. ' . ($data['message'] ?? 'Unknown error'));

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Airtime Purchase Exception: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Something went wrong. Please try again later.');
        }
    }
}
