<?php

namespace App\Http\Controllers\Action;

use App\Helpers\RequestIdHelper;
use App\Http\Controllers\Controller;
use App\Mail\JambPurchaseNotification;
use App\Models\Transaction;
use App\Models\Wallet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Traits\ActiveUsers;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class EducationalController extends Controller
{
    use ActiveUsers;

    protected $loginUserId;

    public function __construct()
    {
        $this->loginUserId = Auth::id();
    }

    /**
     * Show Educational Pin Services & Price Lists
     */
    public function pin(Request $request)
    {
        $user = Auth::user();

        if (!$user) {
            return redirect()->route('login')->with('error', 'Please log in to access this page.');
        }

        $wallet = Wallet::firstOrCreate(
            ['user_id' => $user->id],
            ['balance' => 0.00, 'status' => 'active']
        );

        // Load pin variations
        $pins = DB::table('data_variations')->whereIn('service_id', ['waec', 'waec-registration'])->get();

        // Fetch purchase history
        $history = \App\Models\Report::where('user_id', $user->id)
            ->where('type', 'education')
            ->latest()
            ->paginate(10);

        return view('utilities.buy-educational-pin')->with(compact('pins', 'wallet', 'history'));
    }

    /**
     * Verify Transaction PIN
     */
    public function verifyPin(Request $request)
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json(['valid' => false, 'message' => 'Unauthorized']);
        }

        $isValid = Hash::check($request->pin, $user->pin);
        return response()->json(['valid' => $isValid]);
    }

    /**
     * Fetch variations dynamically from VTpass and store in DB
     */
    /**
     * Fetch variations dynamically from VTpass and store in DB
     */
    public function getVariation(Request $request)
    {
        try {
            // Determine serviceID based on type
            $type = $request->type;
            $url = env('VARIATION_URL') . $type;

            // Special handling for JAMB if needed, but usually VTPass uses 'jamb' as serviceID for variations too
            // If type is 'jamb', URL is .../service-variations?serviceID=jamb

            $response = Http::withHeaders([
                'api-key'    => env('API_KEY'),
                'secret-key' => env('SECRET_KEY'),
            ])->get($url);

            if ($response->successful()) {
                $data = $response->json();
                if (isset($data['content']['variations'])) {
                    $serviceName = $data['content']['ServiceName'];
                    $serviceId = $data['content']['serviceID'];
                    $convenienceFee = $data['content']['convinience_fee'] ?? '0%';

                    foreach ($data['content']['variations'] as $variation) {
                        DB::table('data_variations')->updateOrInsert(
                            ['variation_code' => $variation['variation_code']],
                            [
                                'service_name'     => $serviceName,
                                'service_id'       => $serviceId,
                                'convenience_fee'  => $convenienceFee,
                                'name'             => $variation['name'],
                                'variation_amount' => $variation['variation_amount'],
                                'fixed_price'      => $variation['fixedPrice'],
                                'created_at'       => Carbon::now(),
                                'updated_at'       => Carbon::now(),
                            ]
                        );
                    }

                    return response()->json(['success' => true, 'message' => 'Variation list updated successfully.']);
                }
            }

            Log::error('VTpass Variation Fetch Failed', ['response' => $response->json()]);
            return response()->json(['success' => false, 'message' => 'Failed to fetch variations.']);
        } catch (\Exception $e) {
            Log::error('VTpass Variation Exception', ['error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => 'Something went wrong.']);
        }
    }

    /**
     * Buy Educational Pin (WAEC / WAEC Registration)
     */
    public function buypin(Request $request)
    {
        $request->validate([
            'service'  => ['required', 'string', 'in:waec-registration,waec'],
            'type'     => ['required', 'string'],
            'mobileno' => 'required|numeric|digits:11',
        ]);

        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login')->with('error', 'Please log in first.');
        }

        $requestId = RequestIdHelper::generateRequestId();

        try {
            // Get the selected variation details
            $variation = DB::table('data_variations')->where('variation_code', $request->type)->first();

            if (!$variation) {
                return back()->with('error', 'Invalid educational pin type selected.');
            }

            $fee = $variation->variation_amount;
            $description = $variation->name ?? 'Educational Pin';

            $wallet = Wallet::where('user_id', $this->loginUserId)->first();
            if (!$wallet || $wallet->balance < $fee) {
                return back()->with('error', 'Insufficient wallet balance for this transaction.');
            }

            // Call VTpass API
            $response = Http::withHeaders([
                'api-key'    => env('API_KEY'),
                'secret-key' => env('SECRET_KEY'),
            ])->post(env('MAKE_PAYMENT'), [
                'request_id'     => $requestId,
                'serviceID'      => $request->service,
                'billersCode'    => '0123456789', // Dummy biller code for WAEC/Result Checker
                'variation_code' => $request->type,
                'phone'          => $request->mobileno,
            ]);

            if ($response->successful()) {
                $result = $response->json();

                // Check success codes
                $successCodes = ['0', '00', '000', '200'];
                $isSuccessful = (isset($result['code']) && in_array((string)$result['code'], $successCodes)) ||
                                (isset($result['status']) && strtolower($result['status']) === 'success');

                if ($isSuccessful) {
                    // Deduct wallet balance
                    $wallet->decrement('balance', $fee);

                    // Extract Purchased Code (PIN)
                    // VTpass usually returns it in 'purchased_code' or inside 'cards' array
                    $purchasedCode = $result['purchased_code'] ?? null;
                    
                    if (!$purchasedCode && isset($result['cards']) && is_array($result['cards']) && count($result['cards']) > 0) {
                         $purchasedCode = $result['cards'][0]['Pin'] ?? null;
                    }
                    
                    // Fallback if code is not found but transaction is successful
                    $finalToken = $purchasedCode ?? 'Check Transaction History';

                    $payer_name = $user->first_name . ' ' . $user->last_name;
                    $transDescription = "Educational pin purchase ({$description}) - PIN: {$finalToken}";

                    // Save transaction record
                    Transaction::create([
                        'transaction_ref' => $requestId,
                        'user_id'         => $this->loginUserId,
                        'amount'          => $fee,
                        'description'     => $transDescription,
                        'type'            => 'debit',
                        'status'          => 'completed',
                        'metadata'         => json_encode([
                            'phone'          => $request->mobileno,
                            'service'        => $request->service,
                            'purchased_code' => $finalToken,
                            'payer_name'     => $payer_name,
                            'payer_email'    => $user->email,
                            'payer_phone'    => $user->phone_number,
                            'gateway'        => 'Wallet',
                            'api_response'   => $result,
                        ]),
                        'performed_by' => $payer_name,
                        'approved_by'  => $this->loginUserId,
                    ]);

                    // Create Report
                    \App\Models\Report::create([
                        'user_id'      => $user->id,
                        'phone_number' => $request->mobileno,
                        'network'      => $request->service, // e.g. waec
                        'ref'          => $requestId,
                        'amount'       => $fee,
                        'status'       => 'successful',
                        'type'         => 'education',
                        'description'  => $transDescription,
                        'old_balance'  => $wallet->balance + $fee,
                        'new_balance'  => $wallet->balance,
                    ]);

                    return redirect()->route('thankyou')->with([
                        'success' => 'Educational pin purchase successful!',
                        'ref'     => $requestId,
                        'mobile'  => $request->mobileno,
                        'amount'  => $fee,
                        'token'   => $finalToken, // Pass the PIN as 'token' for thankyou page
                        'network' => strtoupper($request->service) // Display name
                    ]);
                } else {
                    Log::error('VTpass Educational Pin API Error', ['response' => $result]);
                    return back()->with('error', 'Purchase failed. ' . ($result['response_description'] ?? 'Please try again later.'));
                }
            } else {
                Log::error('VTpass Educational Pin HTTP Error', [
                    'status' => $response->status(),
                    'body'   => $response->body(),
                ]);
                return back()->with('error', 'Service temporarily unavailable. Try again later.');
            }
        } catch (\Exception $e) {
            Log::error('Educational Pin Purchase Exception', ['error' => $e->getMessage()]);
            return back()->with('error', 'Something went wrong. Please try again.');
        }
    }

    /**
     * Show JAMB Purchase Page
     */
    public function jamb(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login')->with('error', 'Please log in to access this page.');
        }

        $wallet = Wallet::firstOrCreate(
            ['user_id' => $user->id],
            ['balance' => 0.00, 'status' => 'active']
        );

        // Fetch JAMB purchase history
        $history = \App\Models\Report::where('user_id', $user->id)
            ->where('type', 'jamb')
            ->latest()
            ->paginate(10);

        // Fetch JAMB variations
        $variations = DB::table('data_variations')->where('service_id', 'jamb')->get();

        return view('utilities.buy-jamb', compact('wallet', 'history', 'variations'));
    }

    /**
     * Verify JAMB Profile ID
     */
    public function verifyJamb(Request $request)
    {
        $request->validate([
            'service'    => 'required|string',
            'profile_id' => 'required|string',
        ]);

        try {
            // Get variation details to extract the type
            $variationCode = $request->service; // 'utme' or 'de'
            $variation = DB::table('data_variations')->where('variation_code', $variationCode)->first();
            
            if (!$variation) {
                Log::error('JAMB Verification Error: Variation not found', [
                    'variation_code' => $variationCode
                ]);
                return response()->json([
                    'success' => false, 
                    'message' => 'Invalid JAMB service selected. Please refresh the page and try again.'
                ]);
            }

            // VTPass requires: serviceID, billersCode, and type
            $vtpassServiceId = 'jamb'; 
            $type = $variationCode; // The variation_code is the type (e.g., 'utme', 'de')

            $requestPayload = [
                'serviceID'   => $vtpassServiceId,
                'billersCode' => $request->profile_id,
                'type'        => $type,
            ];

            Log::info('JAMB Verification Request', $requestPayload);

            $response = Http::withHeaders([
                'api-key'    => env('API_KEY'),
                'secret-key' => env('SECRET_KEY'),
            ])->post(env('BASE_URL', 'https://vtpass.com/api') . '/merchant-verify', $requestPayload);

            $data = $response->json();
            
            // Log the full response for debugging
            Log::info('JAMB Verification Response', [
                'status_code' => $response->status(),
                'response' => $data
            ]);

            if ($response->successful()) {
                if (isset($data['code']) && $data['code'] == '000') {
                    $customerName = $data['content']['Customer_Name'] ?? 'Unknown';
                    $amount = $variation->variation_amount;

                    Log::info('JAMB Verification Successful', [
                        'profile_id' => $request->profile_id,
                        'customer_name' => $customerName
                    ]);

                    return response()->json([
                        'success' => true, 
                        'customer_name' => $customerName,
                        'amount' => $amount
                    ]);
                } else {
                    // API returned non-success code
                    $errorMessage = $data['response_description'] ?? $data['message'] ?? 'Invalid Profile ID';
                    
                    Log::error('JAMB Verification Failed', [
                        'profile_id' => $request->profile_id,
                        'code' => $data['code'] ?? 'N/A',
                        'message' => $errorMessage,
                        'full_response' => $data
                    ]);
                    
                    return response()->json([
                        'success' => false, 
                        'message' => $errorMessage
                    ]);
                }
            } else {
                // HTTP error
                Log::error('JAMB Verification HTTP Error', [
                    'status_code' => $response->status(),
                    'response_body' => $response->body(),
                    'profile_id' => $request->profile_id
                ]);
                
                return response()->json([
                    'success' => false, 
                    'message' => 'Service temporarily unavailable. Please try again later.'
                ]);
            }

        } catch (\Exception $e) {
            Log::error('JAMB Verification Exception', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'profile_id' => $request->profile_id ?? 'N/A'
            ]);
            
            return response()->json([
                'success' => false, 
                'message' => 'An error occurred during verification. Please try again.'
            ]);
        }
    }

    /**
     * Buy JAMB PIN
     */
    public function buyJamb(Request $request)
    {
        $request->validate([
            'service'    => 'required|string',
            'profile_id' => 'required|string',
            'mobileno'   => 'required|numeric|digits:11',
            'email'      => 'nullable|email',
        ]);

        $user = Auth::user();
        $requestId = RequestIdHelper::generateRequestId();

        try {
            // Get Price
            $variation = DB::table('data_variations')->where('variation_code', $request->service)->first();
            if (!$variation) {
                Log::error('JAMB Purchase Error: Variation not found', [
                    'variation_code' => $request->service
                ]);
                return back()->with('error', 'Invalid JAMB service selected. Please refresh the page and try again.');
            }

            $fee = $variation->variation_amount;
            $description = $variation->name ?? 'JAMB PIN';

            $wallet = Wallet::where('user_id', $this->loginUserId)->first();
            if (!$wallet || $wallet->balance < $fee) {
                Log::warning('JAMB Purchase Failed: Insufficient balance', [
                    'user_id' => $this->loginUserId,
                    'required' => $fee,
                    'balance' => $wallet->balance ?? 0
                ]);
                return back()->with('error', 'Insufficient wallet balance. Please fund your wallet and try again.');
            }

            // Prepare API request payload
            $apiServiceId = 'jamb';
            $requestPayload = [
                'request_id'     => $requestId,
                'serviceID'      => $apiServiceId,
                'billersCode'    => $request->profile_id,
                'variation_code' => $request->service,
                'phone'          => $request->mobileno,
            ];

            Log::info('JAMB Purchase Request', $requestPayload);

            $response = Http::withHeaders([
                'api-key'    => env('API_KEY'),
                'secret-key' => env('SECRET_KEY'),
            ])->post(env('MAKE_PAYMENT'), $requestPayload);

            $result = $response->json();
            
            // Log the full response
            Log::info('JAMB Purchase Response', [
                'status_code' => $response->status(),
                'response' => $result
            ]);

            if ($response->successful()) {
                $successCodes = ['0', '00', '000', '200'];
                $isSuccessful = (isset($result['code']) && in_array((string)$result['code'], $successCodes)) ||
                                (isset($result['status']) && strtolower($result['status']) === 'success');

                if ($isSuccessful) {
                    $wallet->decrement('balance', $fee);

                    // Extract PIN from API response - check Pin field first (as per API documentation)
                    $purchasedCode = $result['Pin'] ?? 
                                   $result['purchased_code'] ?? 
                                   $result['content']['transactions']['Pin'] ?? null;
                    
                    if (!$purchasedCode && isset($result['cards'][0]['Pin'])) {
                        $purchasedCode = $result['cards'][0]['Pin'];
                    }
                    
                    $finalToken = $purchasedCode ?? 'Check Transaction History';

                    $payer_name = $user->first_name . ' ' . $user->last_name;
                    $transDescription = "{$description} Purchase - Profile: {$request->profile_id} - PIN: {$finalToken}";

                    // Transaction
                    Transaction::create([
                        'transaction_ref' => $requestId,
                        'user_id'         => $this->loginUserId,
                        'amount'          => $fee,
                        'description'     => $transDescription,
                        'type'            => 'debit',
                        'status'          => 'completed',
                        'metadata'        => json_encode([
                            'profile_id'     => $request->profile_id,
                            'purchased_code' => $finalToken,
                            'phone'          => $request->mobileno,
                            'service_type'   => $description,
                            'email'          => $request->email ?? null,
                        ]),
                        'performed_by' => $payer_name,
                        'approved_by'  => $this->loginUserId,
                    ]);

                    // Report
                    \App\Models\Report::create([
                        'user_id'      => $user->id,
                        'phone_number' => $request->mobileno,
                        'network'      => $request->service,
                        'ref'          => $requestId,
                        'amount'       => $fee,
                        'status'       => 'successful',
                        'type'         => 'jamb',
                        'description'  => $transDescription,
                        'old_balance'  => $wallet->balance + $fee,
                        'new_balance'  => $wallet->balance,
                    ]);

                    Log::info('JAMB Purchase Successful', [
                        'request_id' => $requestId,
                        'profile_id' => $request->profile_id,
                        'amount' => $fee
                    ]);

                    // Send email notification if email is provided
                    if ($request->email) {
                        try {
                            $emailData = [
                                'customer_name'    => $payer_name,
                                'profile_id'       => $request->profile_id,
                                'pin'              => $finalToken,
                                'amount'           => $fee,
                                'reference'        => $requestId,
                                'service_type'     => $description,
                                'transaction_date' => now()->format('d M Y, h:i A'),
                            ];

                            Mail::to($request->email)->send(new JambPurchaseNotification($emailData));
                            
                            Log::info('JAMB Purchase Email Sent', [
                                'email' => $request->email,
                                'request_id' => $requestId
                            ]);
                        } catch (\Exception $e) {
                            Log::error('JAMB Email Sending Failed', [
                                'error' => $e->getMessage(),
                                'email' => $request->email,
                                'request_id' => $requestId
                            ]);
                            // Don't fail the transaction if email fails
                        }
                    }

                    return redirect()->route('thankyou')->with([
                        'success' => 'JAMB PIN purchase successful!',
                        'ref'     => $requestId,
                        'mobile'  => $request->mobileno,
                        'amount'  => $fee,
                        'token'   => $finalToken,
                        'network' => strtoupper($description)
                    ]);
                } else {
                    // API returned failure code
                    $errorMessage = $result['response_description'] ?? $result['message'] ?? 'Purchase failed. Please try again.';
                    
                    Log::error('JAMB Purchase API Error', [
                        'request_id' => $requestId,
                        'profile_id' => $request->profile_id,
                        'code' => $result['code'] ?? 'N/A',
                        'message' => $errorMessage,
                        'full_response' => $result
                    ]);
                    
                    return back()->with('error', 'Purchase failed: ' . $errorMessage);
                }
            } else {
                // HTTP error
                Log::error('JAMB Purchase HTTP Error', [
                    'request_id' => $requestId,
                    'status_code' => $response->status(),
                    'response_body' => $response->body(),
                    'profile_id' => $request->profile_id
                ]);
                
                return back()->with('error', 'Service temporarily unavailable. Please try again later.');
            }

        } catch (\Exception $e) {
            Log::error('JAMB Purchase Exception', [
                'request_id' => $requestId ?? 'N/A',
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'profile_id' => $request->profile_id ?? 'N/A'
            ]);
            
            return back()->with('error', 'An error occurred during purchase. Please try again or contact support.');
        }
    }
}


