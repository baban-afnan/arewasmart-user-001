<?php

namespace App\Http\Controllers;

use App\Helpers\noncestrHelper;
use App\Helpers\signatureHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Auth;
use App\Models\Verification;
use App\Models\Transaction;
use App\Models\Service;
use App\Models\ServiceField;
use App\Models\Wallet;
use App\Repositories\BVN_PDF_Repository;
use Carbon\Carbon;

class BvnverificationController extends Controller
{

    /**
     * Show BVN verification page
     */
    public function index(Request $request)
    {
        $user = auth()->user();

        // Get Verification Service
        $service = Service::where('name', 'Verification')->first();
        
        // Get Prices
        $verificationPrice = 0;
        $standardSlipPrice = 0;
        $premiumSlipPrice = 0;
        $plasticSlipPrice = 0;

        if ($service) {
            $verificationField = $service->fields()->where('field_code', '600')->first();
            $standardSlipField = $service->fields()->where('field_code', '601')->first();
            $premiumSlipField = $service->fields()->where('field_code', '602')->first();
            $plasticSlipField = $service->fields()->where('field_code', '603')->first();

            $verificationPrice = $verificationField ? $verificationField->getPriceForUserType($user->role) : 0;
            $standardSlipPrice = $standardSlipField ? $standardSlipField->getPriceForUserType($user->role) : 0;
            $premiumSlipPrice = $premiumSlipField ? $premiumSlipField->getPriceForUserType($user->role) : 0;
            $plasticSlipPrice = $plasticSlipField ? $plasticSlipField->getPriceForUserType($user->role) : 0;
        }

        $wallet = Wallet::where('user_id', $user->id)->first();

        return view('verification.bvn-verification', [
            'wallet' => $wallet,
            'verificationPrice' => $verificationPrice,
            'standardSlipPrice' => $standardSlipPrice,
            'premiumSlipPrice' => $premiumSlipPrice,
            'plasticSlipPrice' => $plasticSlipPrice,
        ]);
    }

    /**
     * Store new BVN verification request
     */
    public function store(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'bvn' => 'required|string|size:11|regex:/^[0-9]{11}$/',
        ]);

        // 1. Get Verification Service
        $service = Service::where('name', 'Verification')
            ->where('is_active', true)
            ->first();

        if (!$service) {
            return back()->with([
                'status' => 'error',
                'message' => 'Verification service not available.'
            ]);
        }

        // 2. Get BVN Verification ServiceField (600)
        $serviceField = $service->fields()
            ->where('field_code', '600')
            ->where('is_active', true)
            ->first();

        if (!$serviceField) {
            return back()->with([
                'status' => 'error',
                'message' => 'BVN verification service is not available.'
            ]);
        }

        // 3. Determine service price based on user role
        $servicePrice = $serviceField->getPriceForUserType($user->role);

        // 4. Check wallet
        $wallet = Wallet::where('user_id', $user->id)->firstOrFail();

        if ($wallet->status !== 'active') {
            return back()->with([
                'status' => 'error',
                'message' => 'Your wallet is not active.'
            ]);
        }

        if ($wallet->balance < $servicePrice) {
            return back()->with([
                'status' => 'error',
                'message' => 'Insufficient wallet balance. You need NGN ' . number_format($servicePrice - $wallet->balance, 2)
            ]);
        }

        try {

            $requestTime = (int) (microtime(true) * 1000);
            $noncestr = noncestrHelper::generateNonceStr();

            $data = [
                'version' => env('API_VERSION'),
                'nonceStr' => $noncestr,
                'requestTime' => $requestTime,
                'bvn' => $request->bvn,
            ];

            $signature = signatureHelper::generate_signature($data, config('keys.private2'));

            $url = env('Domain') . '/api/validator-service/open/bvn/inquire';
            $token = env('BEARER');

            $headers = [
                'Accept: application/json, text/plain, */*',
                'CountryCode: NG',
                "Signature: $signature",
                'Content-Type: application/json',
                "Authorization: Bearer $token",
            ];

            // Initialize cURL
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

            if (curl_errno($ch)) {
                $error = curl_error($ch);
                curl_close($ch);
                throw new \Exception('cURL Error: ' . $error);
            }
            curl_close($ch);

            // Log the raw response for debugging
            \Illuminate\Support\Facades\Log::info('BVN Verification Response', [
                'http_code' => $httpCode,
                'response' => $response
            ]);

            $decodedData = json_decode($response, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                return back()->with([
                    'status' => 'error',
                    'message' => 'Invalid API Response: ' . substr($response, 0, 100) // Limit length for UI
                ]);
            }

            if (isset($decodedData['respCode']) && $decodedData['respCode'] == '00000000') {
                return $this->processChargeAndReturn(
                    $wallet,
                    $servicePrice,
                    $user,
                    $serviceField,
                    $service,
                    $decodedData
                );
            } else {
                 $errorMessage = $decodedData['respDescription'] ?? 'Verification failed.';
                 
                 // Fallback for other error fields if respDescription is missing
                 if ($errorMessage === 'Verification failed.' && isset($decodedData['message'])) {
                     $errorMessage = $decodedData['message'];
                 }

                 return back()->with([
                    'status' => 'error',
                    'message' => $errorMessage
                ]);
            }
        } catch (\Exception $e) {
             return back()->with([
                'status' => 'error',
                'message' => 'System Error: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Process wallet charge, transaction creation and response
     */
    private function processChargeAndReturn($wallet, $servicePrice, $user, $serviceField, $service, $bvnData)
    {
        DB::beginTransaction();

        try {

            $transactionRef = 'Ver-' . (time() % 1000000000) . '-' . mt_rand(100, 999);
            $performedBy = $user->first_name . ' ' . $user->last_name;

            $transaction = Transaction::create([
                'transaction_ref' => $transactionRef,
                'user_id' => $user->id,
                'amount' => $servicePrice,
                'description' => "BVN Verification - {$serviceField->field_name}",
                'type' => 'debit',
                'status' => 'completed',
                'performed_by'    => $performedBy,
                'metadata' => [
                    'service' => 'verification',
                    'service_field' => $serviceField->field_name,
                    'field_code' => $serviceField->field_code,
                    'bvn' => $bvnData['data']['bvn'],
                    'user_role' => $user->role,
                    'price_details' => [
                        'base_price' => $serviceField->base_price,
                        'user_price' => $servicePrice,
                    ],
                    'source' => 'API'
                ],
            ]);

            // Deduct wallet balance
            $wallet->decrement('balance', $servicePrice);

            Verification::create([
                'user_id' => $user->id,
                'service_field_id' => $serviceField->id,
                'service_id' => $service->id,
                'transaction_id' => $transaction->id,
                'reference' => $transactionRef,
                'idno' => $bvnData['data']['bvn'],
                'firstname' => $bvnData['data']['firstName'],
                'middlename' => $bvnData['data']['middleName'],
                'surname' => $bvnData['data']['lastName'],
                'birthdate' =>  $bvnData['data']['birthday'],
                'gender' => $bvnData['data']['gender'],
                'telephoneno' => $bvnData['data']['phoneNumber'],
                'photo_path' => $bvnData['data']['photo'],
                'performed_by'    => $performedBy,
                'submission_date' => Carbon::now()
            ]);

            DB::commit();

            // Flash normalized verification data for Blade
            session()->flash('verification', $bvnData);

            return redirect()->route('bvn.verification.index')->with([
                'status' => 'success',
                'message' => "BVN Verification successful. Reference: {$transactionRef}. Charged: NGN " . number_format($servicePrice, 2),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            report($e);

            return back()->with([
                'status' => 'error',
                'message' => 'Transaction failed: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Charge for Slip Download
     */
    private function chargeForSlip($user, $fieldCode)
    {
         // 1. Get Verification Service
         $service = Service::where('name', 'Verification')
         ->where('is_active', true)
         ->first();

        if (!$service) {
            throw new \Exception('Verification service not available.');
        }

        // 2. Get ServiceField
        $serviceField = $service->fields()
            ->where('field_code', $fieldCode)
            ->where('is_active', true)
            ->first();

        if (!$serviceField) {
             throw new \Exception('Slip service not available.');
        }

        // 3. Determine service price based on user role
        $servicePrice = $serviceField->getPriceForUserType($user->role);

        // 4. Check wallet
        $wallet = Wallet::where('user_id', $user->id)->firstOrFail();

        if ($wallet->status !== 'active') {
             throw new \Exception('Your wallet is not active.');
        }

        if ($wallet->balance < $servicePrice) {
             throw new \Exception('Insufficient wallet balance.');
        }
        
        DB::beginTransaction();
        try {
             $transactionRef = 'Slip-' . (time() % 1000000000) . '-' . mt_rand(100, 999);
             $performedBy = $user->first_name . ' ' . $user->last_name;
 
             Transaction::create([
                 'transaction_ref' => $transactionRef,
                 'user_id' => $user->id,
                 'amount' => $servicePrice,
                 'description' => "Slip Download: {$serviceField->field_name}",
                 'type' => 'debit',
                 'status' => 'completed',
                 'performed_by'    => $performedBy,
                 'metadata' => [
                     'service' => 'slip_download',
                     'service_field' => $serviceField->field_name,
                     'field_code' => $serviceField->field_code,
                     'user_role' => $user->role,
                     'price_details' => [
                         'base_price' => $serviceField->base_price,
                         'user_price' => $servicePrice,
                     ],
                 ],
             ]);
 
             // Deduct wallet balance
             $wallet->decrement('balance', $servicePrice);
             
             DB::commit();
             return true;

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }


    /**
     * Download PDF slips
     */
    public function standardBVN($bvn_no)
    {
        try {
            $this->chargeForSlip(Auth::user(), '601'); // Charge for Standard Slip
            
            if (Verification::where('idno', $bvn_no)->exists()) {
                $veridiedRecord = Verification::where('idno', $bvn_no)
                    ->latest()
                    ->first();

                $view = view('freeBVN', compact('veridiedRecord'))->render();
                return response()->json(['view' => $view]);
            } else {
                return response()->json([
                    "message" => "Error",
                    "errors" => array("Not Found" => "Verification record not found !")
                ], 422);
            }
        } catch (\Exception $e) {
             return response()->json([
                "message" => "Error",
                "errors" => array("Charge Failed" => $e->getMessage())
            ], 422);
        }
    }

    public function premiumBVN($bvn_no)
    {
        try {
            $this->chargeForSlip(Auth::user(), '602'); // Charge for Premium Slip

            if (Verification::where('idno', $bvn_no)->exists()) {
                $veridiedRecord = Verification::where('idno', $bvn_no)
                    ->latest()
                    ->first();

                $view = view('PremiumBVN', compact('veridiedRecord'))->render();
                return response()->json(['view' => $view]);
            } else {
                return response()->json([
                    "message" => "Error",
                    "errors" => array("Not Found" => "Verification record not found !")
                ], 422);
            }
        } catch (\Exception $e) {
            return response()->json([
               "message" => "Error",
               "errors" => array("Charge Failed" => $e->getMessage())
           ], 422);
       }
    }

    public function plasticBVN($bvn_no)
    {
         try {
            $this->chargeForSlip(Auth::user(), '603'); // Charge for Plastic Slip
            
            $repObj = new BVN_PDF_Repository();
            return $repObj->plasticPDF($bvn_no);
         } catch (\Exception $e) {
             // For plastic PDF, we might need to return a view or redirect with error since it's a direct link
             return back()->with('error', $e->getMessage());
        }
    }
}
