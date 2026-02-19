<?php

namespace App\Http\Controllers\Action;

use App\Helpers\RequestIdHelper;
use App\Http\Controllers\Controller;
use App\Models\Report;
use App\Models\Transaction;
use App\Models\Wallet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;


class ElectricityController extends Controller
{
    /**
     * Show Electricity Purchase Page
     */
    public function index()
    {
        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login')->with('error', 'Please log in to access this page.');
        }

        $wallet = Wallet::firstOrCreate(
            ['user_id' => $user->id],
            ['balance' => 0.00, 'status' => 'active']
        );

        // Fetch Electricity purchase history
        $history = Report::where('user_id', $user->id)
            ->where('type', 'electricity')
            ->latest()
            ->paginate(10);

        return view('utilities.buy-electricity', compact('wallet', 'history'));
    }


    /**
     * Verify Meter Number
     */
    public function verifyMeter(Request $request)
    {
        $user = Auth::user();
        // 0. Preliminary Status Checks
        if (($user->status ?? 'inactive') !== 'active') {
            return response()->json(['success' => false, 'message' => "Your account is currently " . ($user->status ?? 'inactive') . ". Access denied."]);
        }

        $request->validate([
            'service_id'   => 'required|string',
            'meter_type'   => 'required|string|in:prepaid,postpaid',
            'meter_number' => 'required|string',
        ]);

        try {
            $response = Http::withHeaders([
                'api-key'    => env('API_KEY'),
                'secret-key' => env('SECRET_KEY'),
            ])->post(env('BASE_URL', 'https://sandbox.vtpass.com/api') . '/merchant-verify', [
                'serviceID'   => $request->service_id,
                'billersCode' => $request->meter_number,
                'type'        => $request->meter_type,
            ]);

            if ($response->successful()) {
                $data = $response->json();
                if (isset($data['code']) && $data['code'] == '000') {
                    $customerName = $data['content']['Customer_Name'] ?? 'Unknown';
                    $address = $data['content']['Address'] ?? '';
                    
                    return response()->json([
                        'success'       => true,
                        'customer_name' => $customerName,
                        'address'       => $address,
                    ]);
                }
            }

            return response()->json(['success' => false, 'message' => 'Unable to verify meter number. Please check and try again.']);

        } catch (\Exception $e) {
            Log::error('Electricity Verification Error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Verification failed due to a system error.']);
        }
    }

    /**
     * Purchase Electricity
     */
    public function purchase(Request $request)
    {
        $request->validate([
            'service_id'   => 'required|string',
            'meter_type'   => 'required|string|in:prepaid,postpaid',
            'meter_number' => 'required|string',
            'amount'       => 'required|numeric|min:100',
            'phone'        => 'required|numeric|digits:11',
        ]);

        $user = Auth::user();
        
        // 0. Preliminary Status Checks
        if (($user->status ?? 'inactive') !== 'active') {
             return redirect()->back()->with('error', "Your account is currently " . ($user->status ?? 'inactive') . ". Access denied.");
        }

        $requestId = RequestIdHelper::generateRequestId();

        DB::beginTransaction();

        try {
            // 4. Create Preliminary Records & Charge Wallet
            $wallet->decrement('balance', $amount);

            // Create Transaction (Pending)
            $transaction = Transaction::create([
                'transaction_ref' => $requestId,
                'user_id'         => $user->id,
                'amount'          => $amount,
                'description'     => "Electricity Payment (Pending) - Meter: {$request->meter_number}",
                'type'            => 'debit',
                'status'          => 'pending',
                'performed_by'    => $user->first_name . ' ' . $user->last_name,
                'approved_by'     => $user->id,
            ]);

            // Create Report (Pending)
            $report = Report::create([
                'user_id'      => $user->id,
                'phone_number' => $request->meter_number,
                'network'      => $request->service_id,
                'ref'          => $requestId,
                'amount'       => $amount,
                'status'       => 'pending',
                'type'         => 'electricity',
                'description'  => "Electricity Payment (Pending) - Meter: {$request->meter_number}",
                'old_balance'  => $wallet->balance + $amount,
                'new_balance'  => $wallet->balance,
            ]);

            // 5. Call VTPass API
            $response = Http::withHeaders([
                'api-key'    => env('API_KEY'),
                'secret-key' => env('SECRET_KEY'),
            ])->post(env('MAKE_PAYMENT'), [
                'request_id'     => $requestId,
                'serviceID'      => $request->service_id,
                'billersCode'    => $request->meter_number,
                'variation_code' => $request->meter_type,
                'amount'         => $amount,
                'phone'          => $request->phone,
            ]);

            if ($response->successful()) {
                $result = $response->json();
                
                $successCodes = ['0', '00', '000', '200'];
                $isSuccessful = (isset($result['code']) && in_array((string)$result['code'], $successCodes)) ||
                                (isset($result['status']) && strtolower($result['status']) === 'success');

                if ($isSuccessful) {
                    // Extract Token (for prepaid)
                    $token = $result['token'] ?? ($result['purchased_code'] ?? null);
                    if ($token && str_contains($token, ':')) {
                        $parts = explode(':', $token);
                        $token = trim(end($parts));
                    }
                    
                    $finalToken = $token ?? 'Transaction Successful';
                    $discoName = strtoupper(str_replace('-', ' ', $request->service_id));
                    $description = "Electricity Payment - {$discoName} ({$request->meter_type}) - Meter: {$request->meter_number}";
                    if($request->meter_type == 'prepaid' && $token) {
                        $description .= " - Token: {$token}";
                    }

                    // Finalize Records
                    $transaction->update([
                        'status'      => 'completed',
                        'description' => $description,
                        'metadata'    => json_encode([
                            'meter_number' => $request->meter_number,
                            'meter_type'   => $request->meter_type,
                            'service_id'   => $request->service_id,
                            'token'        => $finalToken,
                            'api_response' => $result,
                        ]),
                    ]);

                    $report->update([
                        'status'      => 'successful',
                        'description' => $description,
                    ]);

                    DB::commit();

                    return redirect()->route('thankyou')->with([
                        'success' => 'Electricity payment successful!',
                        'ref'     => $requestId,
                        'mobile'  => $request->meter_number,
                        'amount'  => $amount,
                        'token'   => $finalToken,
                        'network' => $discoName
                    ]);
                }

                Log::error('Electricity API Error', ['response' => $result]);
                $errorMsg = 'Payment failed. ' . ($result['response_description'] ?? 'Try again.');
            } else {
                Log::error('Electricity HTTP Error', ['body' => $response->body()]);
                $errorMsg = 'Service unavailable.';
            }

            // API Failed - REFUND
            $wallet->increment('balance', $amount);
            $transaction->update(['status' => 'failed']);
            $report->update([
                'status'      => 'failed',
                'description' => "Failed: " . (isset($result['response_description']) ? $result['response_description'] : 'API Error'),
            ]);

            DB::commit();
            return back()->with('error', $errorMsg);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Electricity Exception: ' . $e->getMessage());
            return back()->with('error', 'An error occurred.');
        }
    }
}
