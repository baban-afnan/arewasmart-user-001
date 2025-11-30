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
        $requestId = RequestIdHelper::generateRequestId();

        try {
            $amount = $request->amount;
            $wallet = Wallet::where('user_id', $user->id)->first();

            if (!$wallet || $wallet->balance < $amount) {
                return back()->with('error', 'Insufficient wallet balance.');
            }

            // Call VTPass API
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
                    $wallet->decrement('balance', $amount);

                    // Extract Token (for prepaid)
                    $token = $result['token'] ?? ($result['purchased_code'] ?? null);
                    // Sometimes token is in 'mainToken' or 'token' inside content? 
                    // Documentation says: "token": "Token : 26362054405982757802" in top level or "purchased_code"
                    
                    // Clean up token string if it contains "Token :"
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

                    // Transaction Record
                    Transaction::create([
                        'transaction_ref' => $requestId,
                        'user_id'         => $user->id,
                        'amount'          => $amount,
                        'description'     => $description,
                        'type'            => 'debit',
                        'status'          => 'completed',
                        'metadata'        => json_encode([
                            'meter_number' => $request->meter_number,
                            'meter_type'   => $request->meter_type,
                            'service_id'   => $request->service_id,
                            'token'        => $finalToken,
                            'api_response' => $result,
                        ]),
                        'performed_by' => $user->first_name . ' ' . $user->last_name,
                        'approved_by'  => $user->id,
                    ]);

                    // Report Record
                    Report::create([
                        'user_id'      => $user->id,
                        'phone_number' => $request->meter_number, // Saving Meter Number here for reference
                        'network'      => $request->service_id,
                        'ref'          => $requestId,
                        'amount'       => $amount,
                        'status'       => 'successful',
                        'type'         => 'electricity',
                        'description'  => $description,
                        'old_balance'  => $wallet->balance + $amount,
                        'new_balance'  => $wallet->balance,
                    ]);

                    return redirect()->route('thankyou')->with([
                        'success' => 'Electricity payment successful!',
                        'ref'     => $requestId,
                        'mobile'  => $request->meter_number,
                        'amount'  => $amount,
                        'token'   => $finalToken,
                        'network' => $discoName
                    ]);

                } else {
                    Log::error('Electricity API Error', ['response' => $result]);
                    return back()->with('error', 'Payment failed. ' . ($result['response_description'] ?? 'Try again.'));
                }
            } else {
                Log::error('Electricity HTTP Error', ['body' => $response->body()]);
                return back()->with('error', 'Service unavailable.');
            }

        } catch (\Exception $e) {
            Log::error('Electricity Exception: ' . $e->getMessage());
            return back()->with('error', 'An error occurred.');
        }
    }
}
