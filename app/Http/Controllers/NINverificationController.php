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
use App\Repositories\NIN_PDF_Repository;
use Carbon\Carbon;

class NINverificationController extends Controller
{
    /**
     * Show NIN verification page
     */
    public function index(Request $request)
    {
        $user = auth()->user();

        // 0. Preliminary Status Checks
        if ($user->status !== 'active') {
             return redirect()->back()->with('error', "Your account is currently {$user->status}. Access denied.");
        }

        // Get Verification Service
        $service = Service::where('name', 'Verification')->first();
        
        // Get Prices
        $verificationPrice = 0;
        $standardSlipPrice = 0;
        $premiumSlipPrice = 0;
        $vninSlipPrice = 0;

        if ($service) {
            $verificationField = $service->fields()->where('field_code', '610')->first();
            $standardSlipField = $service->fields()->where('field_code', '611')->first();
            $premiumSlipField = $service->fields()->where('field_code', '612')->first();
            $vninSlipField = $service->fields()->where('field_code', '616')->first();

            $verificationPrice = $verificationField ? $verificationField->getPriceForUserType($user->role) : 0;
            $standardSlipPrice = $standardSlipField ? $standardSlipField->getPriceForUserType($user->role) : 0;
            $premiumSlipPrice = $premiumSlipField ? $premiumSlipField->getPriceForUserType($user->role) : 0;
            $vninSlipPrice = $vninSlipField ? $vninSlipField->getPriceForUserType($user->role) : 0;
        }

        $wallet = Wallet::where('user_id', $user->id)->first();

        return view('verification.nin-verification', [
            'wallet' => $wallet,
            'verificationPrice' => $verificationPrice,
            'standardSlipPrice' => $standardSlipPrice,
            'premiumSlipPrice' => $premiumSlipPrice,
            'vninSlipPrice' => $vninSlipPrice,
        ]);
    }

    /**
     * Store new NIN verification request
     */
    public function store(Request $request)
    {
        $user = Auth::user();

        // 0. Preliminary Status Checks
        if ($user->status !== 'active') {
             return redirect()->back()->with('error', "Your account is currently {$user->status}. Access denied.");
        }

        $validated = $request->validate([
            'number_nin' => 'required|string|size:11|regex:/^[0-9]{11}$/',
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

        // 2. Get NIN Verification ServiceField (610)
        $serviceField = $service->fields()
            ->where('field_code', '610')
            ->where('is_active', true)
            ->first();

        if (!$serviceField) {
            return back()->with([
                'status' => 'error',
                'message' => 'NIN verification service is not available.'
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
                'nin' => $request->number_nin,
            ];

            $signature = signatureHelper::generate_signature($data, config('keys.private2'));

            $url = env('Domain') . '/api/validator-service/open/nin/inquire';
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

            if (curl_errno($ch)) {
                throw new \Exception('cURL Error: ' . curl_error($ch));
            }
            curl_close($ch);

            $data = json_decode($response, true);
            $respCode = $data['respCode'] ?? 'UNKNOWN';

            // Handle Response Codes
            if ($respCode === '00000000') {
                // Successful -> Charge + Create Transaction + Create Verification
                return $this->processSuccessTransaction(
                    $wallet,
                    $servicePrice,
                    $user,
                    $serviceField,
                    $service,
                    $data
                );
            } elseif ($respCode === '99120010') {
                 // NIN do not exist -> Charge + Create Transaction (No Verification)
                 return $this->processFailedButChargedTransaction(
                    $wallet,
                    $servicePrice,
                    $user,
                    $serviceField,
                    $data
                );
            } else {
                 // Other errors (99120012, 99120013, etc) -> No Charge + Create Failed Transaction
                 return $this->processFreeTransactionRecord(
                    $user,
                    $serviceField,
                    $data
                 );
            }

        } catch (\Exception $e) {
             // System/Network Error -> No Charge + Transaction Log if possible (optional, but good for tracking)
             // For now, adhering to returning back with error, but we could log a failed transaction here too if needed.
             // Given the catch block scope, we might not have serviceField context easily if it failed before fetching it.
            return back()->with([
                'status' => 'error',
                'message' => 'System Error: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Process successful transaction (Charge + Verification Record)
     */
    private function processSuccessTransaction($wallet, $servicePrice, $user, $serviceField, $service, $ninData)
    {
        DB::beginTransaction();

        try {
            $transactionRef = 'Ver-' . (time() % 1000000000) . '-' . mt_rand(100, 999);
            $performedBy = $user->first_name . ' ' . $user->last_name;

            $transaction = Transaction::create([
                'transaction_ref' => $transactionRef,
                'user_id' => $user->id,
                'amount' => $servicePrice,
                'description' => "NIN Verification - {$serviceField->field_name}",
                'type' => 'debit',
                'status' => 'completed',
                'performed_by'    => $performedBy,
                'metadata' => [
                    'service' => 'verification',
                    'service_field' => $serviceField->field_name,
                    'field_code' => $serviceField->field_code,
                    'nin' => $ninData['data']['nin'] ?? 'N/A', // Should exist on success
                    'user_role' => $user->role,
                    'price_details' => [
                        'base_price' => $serviceField->base_price,
                        'user_price' => $servicePrice,
                    ],
                    'source' => 'API',
                    'api_response' => $ninData
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
                'number_nin' => $ninData['data']['nin'],
                'firstname' => $ninData['data']['firstName'],
                'middlename' => $ninData['data']['middleName'],
                'surname' => $ninData['data']['surname'],
                'birthdate' =>  $ninData['data']['birthDate'],
                'gender' => $ninData['data']['gender'],
                'telephoneno' => $ninData['data']['telephoneNo'],
                'photo_path' => $ninData['data']['photo'],
                'performed_by'    => $performedBy,
                'submission_date' => Carbon::now()
            ]);

            DB::commit();

            // Flash normalized verification data for Blade
            session()->flash('verification', $ninData);

            return redirect()->route('nin.verification.index')->with([
                'status' => 'success',
                'message' => "NIN Verification successful. Reference: {$transactionRef}. Charged: NGN " . number_format($servicePrice, 2),
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
     * Process Failed but Charged Transaction (NIN Not Found)
     */
    private function processFailedButChargedTransaction($wallet, $servicePrice, $user, $serviceField, $data)
    {
        DB::beginTransaction();
        try {
            $transactionRef = 'Ver-Fail-' . (time() % 1000000000) . '-' . mt_rand(100, 999);
            $performedBy = $user->first_name . ' ' . $user->last_name;

            Transaction::create([
                'transaction_ref' => $transactionRef,
                'user_id' => $user->id,
                'amount' => $servicePrice,
                'description' => "NIN Verification - NIN Not Found",
                'type' => 'debit',
                'status' => 'completed', // Completed because we charged them
                'performed_by'    => $performedBy,
                'metadata' => [
                    'service' => 'verification',
                    'service_field' => $serviceField->field_name,
                    'result' => 'NIN_NOT_FOUND',
                    'api_message' => $data['respDescription'] ?? 'NIN do not exist',
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

            DB::commit();

            return back()->with([
                'status' => 'error', // Show as error to user
                'message' => "NIN do not exist. You have been charged NGN " . number_format($servicePrice, 2) . " for this search."
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with([
                'status' => 'error',
                'message' => 'Transaction Error: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Process Free Transaction Record (System Error / Param Error)
     */
    private function processFreeTransactionRecord($user, $serviceField, $data)
    {
        // No Wallet Charge, just record the attempt
        try {
            $transactionRef = 'Ver-Err-' . (time() % 1000000000) . '-' . mt_rand(100, 999);
            $performedBy = $user->first_name . ' ' . $user->last_name;

            Transaction::create([
                'transaction_ref' => $transactionRef,
                'user_id' => $user->id,
                'amount' => 0,
                'description' => "NIN Verification - Failed: " . ($data['respDescription'] ?? 'Error'),
                'type' => 'debit', // or 'info'
                'status' => 'failed',
                'performed_by'    => $performedBy,
                'metadata' => [
                    'service' => 'verification',
                    'service_field' => $serviceField->field_name,
                    'result' => 'API_ERROR',
                    'api_code' => $data['respCode'] ?? 'UNKNOWN',
                    'api_message' => $data['respDescription'] ?? 'Unknown Error',
                    'source' => 'API'
                ],
            ]);

            $message = $data['respDescription'] ?? 'Verification failed';
            
            // Clean up message if needed
            if (($data['respCode'] ?? '') == '99120012') {
                $message = 'Parameter error in the interface call.';
            } elseif (($data['respCode'] ?? '') == '99120013') {
                $message = 'Identity provided is invalid.';
            }

            return back()->with([
                'status' => 'error',
                'message' => $message 
            ]);

        } catch (\Exception $e) {
            // Even if logging fails, ensure user gets the error message
            return back()->with([
                'status' => 'error',
                'message' => $data['respDescription'] ?? 'Verification failed.'
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
     * Download NIN slips
     */
    public function standardSlip($nin_no)
    {
        try {
            $user = Auth::user();
            // 0. Preliminary Status Checks
            if ($user->status !== 'active') {
                 return back()->with('error', "Your account is currently {$user->status}. Access denied.");
            }

            $this->chargeForSlip($user, '611'); // Charge for Standard Slip
            
            $repObj = new NIN_PDF_Repository();
            return $repObj->standardPDF($nin_no);
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function premiumSlip($nin_no)
    {
        try {
            $user = Auth::user();
            // 0. Preliminary Status Checks
            if ($user->status !== 'active') {
                 return back()->with('error', "Your account is currently {$user->status}. Access denied.");
            }

            $this->chargeForSlip($user, '612'); // Charge for Premium Slip
            
            $repObj = new NIN_PDF_Repository();
            return $repObj->premiumPDF($nin_no);
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function vninSlip($nin_no)
    {
        try {
            $user = Auth::user();
            // 0. Preliminary Status Checks
            if ($user->status !== 'active') {
                 return back()->with('error', "Your account is currently {$user->status}. Access denied.");
            }

            $this->chargeForSlip($user, '616'); // Charge for VNIN Slip
            
            $repObj = new NIN_PDF_Repository();
            return $repObj->vninPDF($nin_no);
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
