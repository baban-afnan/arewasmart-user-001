<?php

namespace App\Http\Controllers\Action;

use App\Helpers\RequestIdHelper;
use App\Http\Controllers\Controller;
use App\Mail\JambPurchaseNotification;
use App\Models\Report;
use App\Models\Transaction;
use App\Models\Wallet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Traits\ActiveUsers;
use Carbon\Carbon;

class EducationalController extends Controller
{
    use ActiveUsers;

    protected $loginUserId;

    public function __construct()
    {
        $this->loginUserId = Auth::id();
    }

    // ─────────────────────────────────────────────
    //  PAGE VIEWS
    // ─────────────────────────────────────────────

    /**
     * Show Educational Pin Services & Price Lists
     */
    public function pin(Request $request)
    {
        // 1. Authenticate user
        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login')->with('error', 'Please log in to access this page.');
        }

        $wallet = Wallet::firstOrCreate(
            ['user_id' => $user->id],
            ['balance' => 0.00, 'status' => 'active']
        );

        $pins = DB::table('data_variations')
            ->whereIn('service_id', ['waec', 'waec-registration'])
            ->get();

        $history = Report::where('user_id', $user->id)
            ->where('type', 'education')
            ->latest()
            ->paginate(10);

        return view('utilities.buy-educational-pin')->with(compact('pins', 'wallet', 'history'));
    }

    /**
     * Show JAMB Purchase Page
     */
    public function jamb(Request $request)
    {
        // 1. Authenticate user
        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login')->with('error', 'Please log in to access this page.');
        }

        $wallet = Wallet::firstOrCreate(
            ['user_id' => $user->id],
            ['balance' => 0.00, 'status' => 'active']
        );

        $history = Report::where('user_id', $user->id)
            ->where('type', 'jamb')
            ->latest()
            ->paginate(10);

        $variations = DB::table('data_variations')->where('service_id', 'jamb')->get();

        return view('utilities.buy-jamb', compact('wallet', 'history', 'variations'));
    }

    // ─────────────────────────────────────────────
    //  UTILITY ENDPOINTS
    // ─────────────────────────────────────────────

    /**
     * Verify Transaction PIN
     */
    public function verifyPin(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['valid' => false, 'message' => 'Unauthorized']);
        }

        return response()->json(['valid' => Hash::check($request->pin, $user->pin)]);
    }

    /**
     * Fetch & store variations from VTpass
     */
    public function getVariation(Request $request)
    {
        try {
            $url = env('VARIATION_URL') . $request->type;

            $response = Http::withHeaders([
                'api-key'    => env('API_KEY'),
                'secret-key' => env('SECRET_KEY'),
            ])->get($url);

            if ($response->successful()) {
                $data = $response->json();

                if (isset($data['content']['variations'])) {
                    $serviceName    = $data['content']['ServiceName'];
                    $serviceId      = $data['content']['serviceID'];
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

    // ─────────────────────────────────────────────
    //  BUY EDUCATIONAL PIN  (WAEC / WAEC Registration)
    // ─────────────────────────────────────────────

    /**
     * Buy Educational Pin
     *
     * Standard flow:
     *  1. Authenticate user
     *  2. Validate request
     *  3. Check service active
     *  4. Calculate price
     *  5. Lock wallet row
     *  6. Check wallet active
     *  7. Check balance
     *  8-10. (inside DB transaction) Debit wallet → Call API → On success: create transaction + report → commit
     */
    public function buypin(Request $request)
    {
        // ── 1. Authenticate user ─────────────────
        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login')->with('error', 'Please log in first.');
        }

        // ── 2. Validate request ──────────────────
        $request->validate([
            'service'  => ['required', 'string', 'in:waec-registration,waec'],
            'type'     => ['required', 'string'],
            'mobileno' => ['required', 'numeric', 'digits:11'],
        ]);

        // ── 3. Check service active (user account) ─
        if (($user->status ?? 'inactive') !== 'active') {
            return redirect()->back()->with(
                'error',
                'Your account is currently ' . ($user->status ?? 'inactive') . '. Access denied.'
            );
        }

        // ── 4. Calculate price ───────────────────
        $variation = DB::table('data_variations')
            ->where('variation_code', $request->type)
            ->first();

        if (!$variation) {
            return redirect()->back()->with('error', 'Invalid variation selected.');
        }

        $fee         = $variation->variation_amount;
        $description = $variation->name ?? $request->service;

        // ── 5 & 6. Lock wallet row + Check wallet active ─
        $wallet = Wallet::where('user_id', $user->id)->lockForUpdate()->first();

        if (!$wallet) {
            return redirect()->back()->with('error', 'Wallet not found. Please contact support.');
        }

        if (($wallet->status ?? 'inactive') !== 'active') {
            return redirect()->back()->with('error', 'Your wallet is inactive. Please contact support.');
        }

        // ── 7. Check balance ─────────────────────
        if ($wallet->balance < $fee) {
            return redirect()->back()->with('error', 'Insufficient wallet balance.');
        }

        $requestId  = RequestIdHelper::generateRequestId();
        $payerName  = trim($user->first_name . ' ' . $user->last_name);
        $oldBalance = $wallet->balance;

        // ── 8-10. DB Transaction ─────────────────
        DB::beginTransaction();

        try {
            // ── 8. Debit wallet ──────────────────
            $wallet->decrement('balance', $fee);
            $newBalance = $wallet->fresh()->balance;

            // ── 9. Call VTpass API ───────────────
            $response = Http::withHeaders([
                'api-key'    => env('API_KEY'),
                'secret-key' => env('SECRET_KEY'),
            ])->post(env('MAKE_PAYMENT'), [
                'request_id'     => $requestId,
                'serviceID'      => $request->service,
                'billersCode'    => '0123456789',
                'variation_code' => $request->type,
                'phone'          => $request->mobileno,
            ]);

            if (!$response->successful()) {
                // HTTP-level failure – rollback and abort without recording anything
                DB::rollBack();
                Log::error('Educational Pin HTTP Error', [
                    'status' => $response->status(),
                    'body'   => $response->body(),
                ]);
                return back()->with('error', 'Service temporarily unavailable. Please try again.');
            }

            $result       = $response->json();
            $successCodes = ['0', '00', '000', '200'];
            $isSuccessful = (isset($result['code']) && in_array((string) $result['code'], $successCodes))
                         || (isset($result['status']) && strtolower($result['status']) === 'success');

            if (!$isSuccessful) {
                // API returned a failure code – rollback and abort without recording anything
                DB::rollBack();
                Log::error('Educational Pin API Error', ['response' => $result]);
                $errorMsg = $result['response_description'] ?? 'Purchase failed. Please try again.';
                return back()->with('error', $errorMsg);
            }

            // ── 10. Create service records (success only) ──
            $purchasedCode = $result['purchased_code']
                ?? $result['cards'][0]['Pin']
                ?? null;

            $finalToken      = $purchasedCode ?? 'Check History';
            $transDescription = "Educational pin purchase ({$description}) - PIN: {$finalToken}";

            // Create Transaction (success)
            Transaction::create([
                'transaction_ref' => $requestId,
                'user_id'         => $user->id,
                'amount'          => $fee,
                'description'     => $transDescription,
                'type'            => 'debit',
                'status'          => 'completed',
                'performed_by'    => $payerName,
                'approved_by'     => $user->id,
                'metadata'        => json_encode([
                    'phone'          => $request->mobileno,
                    'service'        => $request->service,
                    'purchased_code' => $finalToken,
                    'api_response'   => $result,
                ]),
            ]);

            // Create Report (success)
            Report::create([
                'user_id'      => $user->id,
                'phone_number' => $request->mobileno,
                'network'      => $request->service,
                'ref'          => $requestId,
                'amount'       => $fee,
                'status'       => 'successful',
                'type'         => 'education',
                'description'  => $transDescription,
                'old_balance'  => $oldBalance,
                'new_balance'  => $newBalance,
                'service_id'   => $variation->id ?? null,
            ]);

            DB::commit();

            return redirect()->route('thankyou')->with([
                'success' => 'Educational pin purchase successful!',
                'ref'     => $requestId,
                'mobile'  => $request->mobileno,
                'amount'  => $fee,
                'token'   => $finalToken,
                'network' => strtoupper($request->service),
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Educational Pin Exception', ['error' => $e->getMessage()]);
            return back()->with('error', 'Something went wrong. Please try again.');
        }
    }

    // ─────────────────────────────────────────────
    //  JAMB
    // ─────────────────────────────────────────────

    /**
     * Verify JAMB Profile ID
     */
    public function verifyJamb(Request $request)
    {
        // 1. Authenticate user
        $user = Auth::user();
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Please log in first.']);
        }

        // 2. Validate request
        $request->validate([
            'service'    => 'required|string',
            'profile_id' => 'required|string',
        ]);

        // 3. Check account status
        if (($user->status ?? 'inactive') !== 'active') {
            return response()->json([
                'success' => false,
                'message' => 'Your account is currently ' . ($user->status ?? 'inactive') . '. Access denied.',
            ]);
        }

        try {
            $variationCode = $request->service;

            $variation = DB::table('data_variations')
                ->where('variation_code', $variationCode)
                ->first();

            if (!$variation) {
                Log::error('JAMB Verification: Variation not found', ['variation_code' => $variationCode]);
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid JAMB service selected. Please refresh and try again.',
                ]);
            }

            $requestPayload = [
                'serviceID'   => 'jamb',
                'billersCode' => $request->profile_id,
                'type'        => $variationCode,
            ];

            Log::info('JAMB Verification Request', $requestPayload);

            $response = Http::withHeaders([
                'api-key'    => env('API_KEY'),
                'secret-key' => env('SECRET_KEY'),
            ])->post(env('BASE_URL', 'https://vtpass.com/api') . '/merchant-verify', $requestPayload);

            $data = $response->json();

            Log::info('JAMB Verification Response', [
                'status_code' => $response->status(),
                'response'    => $data,
            ]);

            if ($response->successful() && isset($data['code']) && $data['code'] == '000') {
                return response()->json([
                    'success'       => true,
                    'customer_name' => $data['content']['Customer_Name'] ?? 'Unknown',
                    'amount'        => $variation->variation_amount,
                ]);
            }

            $errorMessage = $data['response_description'] ?? $data['message'] ?? 'Invalid Profile ID';
            Log::error('JAMB Verification Failed', [
                'profile_id'    => $request->profile_id,
                'code'          => $data['code'] ?? 'N/A',
                'message'       => $errorMessage,
                'full_response' => $data,
            ]);

            return response()->json(['success' => false, 'message' => $errorMessage]);

        } catch (\Exception $e) {
            Log::error('JAMB Verification Exception', [
                'error'      => $e->getMessage(),
                'profile_id' => $request->profile_id ?? 'N/A',
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred during verification. Please try again.',
            ]);
        }
    }

    /**
     * Buy JAMB PIN
     *
     * Standard flow:
     *  1. Authenticate user
     *  2. Validate request
     *  3. Check service active
     *  4. Calculate price
     *  5. Lock wallet row
     *  6. Check wallet active
     *  7. Check balance
     *  8-10. (inside DB transaction) Debit wallet → Call API → On success: create transaction + report → commit
     */
    public function buyJamb(Request $request)
    {
        // ── 1. Authenticate user ─────────────────
        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login')->with('error', 'Please log in first.');
        }

        // ── 2. Validate request ──────────────────
        $request->validate([
            'service'    => 'required|string',
            'profile_id' => 'required|string',
            'mobileno'   => 'required|numeric|digits:11',
            'email'      => 'nullable|email',
        ]);

        // ── 3. Check service active (user account) ─
        if (($user->status ?? 'inactive') !== 'active') {
            return redirect()->back()->with(
                'error',
                'Your account is currently ' . ($user->status ?? 'inactive') . '. Access denied.'
            );
        }

        // ── 4. Calculate price ───────────────────
        $variation = DB::table('data_variations')
            ->where('variation_code', $request->service)
            ->first();

        if (!$variation) {
            return redirect()->back()->with('error', 'Invalid JAMB service selected.');
        }

        $fee         = $variation->variation_amount;
        $description = $variation->name ?? 'JAMB';

        // ── 5 & 6. Lock wallet row + Check wallet active ─
        $wallet = Wallet::where('user_id', $user->id)->lockForUpdate()->first();

        if (!$wallet) {
            return redirect()->back()->with('error', 'Wallet not found. Please contact support.');
        }

        if (($wallet->status ?? 'inactive') !== 'active') {
            return redirect()->back()->with('error', 'Your wallet is inactive. Please contact support.');
        }

        // ── 7. Check balance ─────────────────────
        if ($wallet->balance < $fee) {
            return redirect()->back()->with('error', 'Insufficient wallet balance.');
        }

        $requestId  = RequestIdHelper::generateRequestId();
        $payerName  = trim($user->first_name . ' ' . $user->last_name);
        $oldBalance = $wallet->balance;

        // ── 8-10. DB Transaction ─────────────────
        DB::beginTransaction();

        try {
            // ── 8. Debit wallet ──────────────────
            $wallet->decrement('balance', $fee);
            $newBalance = $wallet->fresh()->balance;

            // ── 9. Call VTpass API ───────────────
            $response = Http::withHeaders([
                'api-key'    => env('API_KEY'),
                'secret-key' => env('SECRET_KEY'),
            ])->post(env('MAKE_PAYMENT'), [
                'request_id'     => $requestId,
                'serviceID'      => 'jamb',
                'billersCode'    => $request->profile_id,
                'variation_code' => $request->service,
                'phone'          => $request->mobileno,
            ]);

            if (!$response->successful()) {
                // HTTP-level failure – rollback and abort without recording anything
                DB::rollBack();
                Log::error('JAMB Purchase HTTP Error', [
                    'status' => $response->status(),
                    'body'   => $response->body(),
                ]);
                return back()->with('error', 'Service temporarily unavailable. Please try again.');
            }

            $result       = $response->json();
            $successCodes = ['0', '00', '000', '200'];
            $isSuccessful = (isset($result['code']) && in_array((string) $result['code'], $successCodes))
                         || (isset($result['status']) && strtolower($result['status']) === 'success');

            if (!$isSuccessful) {
                // API returned a failure code – rollback and abort without recording anything
                DB::rollBack();
                Log::error('JAMB Purchase API Error', ['response' => $result]);
                $errorMsg = $result['response_description'] ?? 'Purchase failed. Please try again.';
                return back()->with('error', $errorMsg);
            }

            // ── 10. Create service records (success only) ──
            $purchasedCode = $result['Pin']
                ?? $result['purchased_code']
                ?? $result['content']['transactions']['Pin']
                ?? $result['cards'][0]['Pin']
                ?? null;

            $finalToken      = $purchasedCode ?? 'Check History';
            $transDescription = "{$description} Purchase - Profile: {$request->profile_id} - PIN: {$finalToken}";

            // Create Transaction (success)
            Transaction::create([
                'transaction_ref' => $requestId,
                'user_id'         => $user->id,
                'amount'          => $fee,
                'description'     => $transDescription,
                'type'            => 'debit',
                'status'          => 'completed',
                'performed_by'    => $payerName,
                'approved_by'     => $user->id,
                'metadata'        => json_encode([
                    'profile_id'     => $request->profile_id,
                    'phone'          => $request->mobileno,
                    'service_type'   => $description,
                    'email'          => $request->email ?? null,
                    'purchased_code' => $finalToken,
                    'api_response'   => $result,
                ]),
            ]);

            // Create Report (success)
            Report::create([
                'user_id'      => $user->id,
                'phone_number' => $request->mobileno,
                'network'      => $request->service,
                'ref'          => $requestId,
                'amount'       => $fee,
                'status'       => 'successful',
                'type'         => 'jamb',
                'description'  => $transDescription,
                'old_balance'  => $oldBalance,
                'new_balance'  => $newBalance,
            ]);

            DB::commit();

            // Send email notification (non-blocking, after commit)
            if ($request->email) {
                try {
                    Mail::to($request->email)->send(new JambPurchaseNotification([
                        'customer_name'    => $payerName,
                        'profile_id'       => $request->profile_id,
                        'pin'              => $finalToken,
                        'amount'           => $fee,
                        'reference'        => $requestId,
                        'service_type'     => $description,
                        'transaction_date' => now()->format('d M Y, h:i A'),
                    ]));
                } catch (\Exception $e) {
                    Log::error('JAMB Email Failed: ' . $e->getMessage());
                }
            }

            return redirect()->route('thankyou')->with([
                'success' => 'JAMB PIN purchase successful!',
                'ref'     => $requestId,
                'mobile'  => $request->mobileno,
                'amount'  => $fee,
                'token'   => $finalToken,
                'network' => strtoupper($description),
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('JAMB Purchase Exception', ['error' => $e->getMessage()]);
            return back()->with('error', 'An error occurred during purchase. Please try again.');
        }
    }
}