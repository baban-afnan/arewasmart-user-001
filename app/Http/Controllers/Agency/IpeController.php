<?php

namespace App\Http\Controllers\Agency;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use App\Models\AgentService;
use App\Models\Service;
use App\Models\ServiceField;
use App\Models\Transaction;
use App\Models\Wallet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class IpeController extends Controller
{
    public function index(Request $request)
    {
        $ipeService = Service::where('name', 'IPE')->first();

        // Fetch fields for IPE service (only active ones)
        $ipeFields = $ipeService ? $ipeService->fields()->where('is_active', 1)->get() : collect();

        $services = collect();
        $user = Auth::user();
        $role = $user->role ?? 'user';
        
        foreach ($ipeFields as $field) {
            $price = $field->prices()->where('user_type', $role)->value('price') ?? $field->base_price;
            $services->push([
                'id' => $field->id,
                'name' => $field->field_name,
                'price' => $price,
                'type' => 'ipe',
                'service_id' => $field->service_id
            ]);
        }

        $wallet = Wallet::where('user_id', Auth::id())->first();
        
        $query = AgentService::where('user_id', Auth::id())
            ->where('service_type', 'ipe');

        if ($request->has('search') && $request->search != '') {
            $searchTerm = $request->search;
            $query->where('tracking_id', 'like', "%{$searchTerm}%");
        }

        if ($request->has('status') && $request->status != '') {
            $query->where('status', $request->status);
        }

        $submissions = $query->orderByRaw("
          CASE status 
        WHEN 'pending' THEN 1 
        WHEN 'processing' THEN 2 
        WHEN 'successful' THEN 3 
        WHEN 'failed' THEN 4 
        WHEN 'resolved' THEN 5 
        WHEN 'rejected' THEN 6 
        ELSE 7 
            END
        ")->orderBy('created_at', 'desc')->paginate(10)->withQueryString();

        return view('nin.ipe', compact('services', 'wallet', 'submissions'));
    }

    public function store(Request $request)
    {
        // 1. Authenticate user (already handled by middleware, but check status)
        $user = Auth::user();
        if (($user->status ?? 'inactive') !== 'active') {
             return redirect()->back()->with('error', "Your account is currently " . ($user->status ?? 'inactive') . ". Access denied.");
        }

        // 2. Validate request
        $validated = $request->validate([
            'service_field' => 'required',
            'tracking_id' => 'required|string|min:15',
        ]);

        $fieldId = $request->service_field;
        $serviceField = ServiceField::with('service')->findOrFail($fieldId);
        
        // 3. Check service active
        if (($serviceField->is_active ?? 0) != 1 || ($serviceField->service->is_active ?? 0) != 1) {
            return back()->with('error', 'This service is currently unavailable.')->withInput();
        }

        // 4. Calculate price
        $role = $user->role ?? 'user';
        $servicePrice = $serviceField->prices()->where('user_type', $role)->value('price') ?? $serviceField->base_price;

        // 5. Lock wallet row & 6. Check wallet active & 7. Check balance
        DB::beginTransaction();
        try {
            $wallet = Wallet::where('user_id', $user->id)->lockForUpdate()->first();

            if (!$wallet || ($wallet->status ?? 'inactive') !== 'active') {
                DB::rollBack();
                return back()->with('error', 'Your wallet is not active. Please contact support.')->withInput();
            }

            if ($wallet->balance < $servicePrice) {
                DB::rollBack();
                return back()->with('error', 'Insufficient wallet balance.');
            }

            // 8. Create transaction (pending)
            $transactionRef = 'TRX-' . strtoupper(Str::random(10));
            $performedBy = $user->first_name . ' ' . $user->last_name;

            $transaction = Transaction::create([
                'transaction_ref' => $transactionRef,
                'user_id' => $user->id,
                'amount' => $servicePrice,
                'description' => "IPE Clearance for {$serviceField->field_name}",
                'type' => 'debit',
                'status' => 'pending',
                'performed_by' => $performedBy,
                'metadata' => [
                    'service' => 'IPE',
                    'service_field' => $serviceField->field_name,
                    'tracking_id' => $request->tracking_id,
                ],
            ]);

            // 9. Debit wallet
            $wallet->decrement('balance', $servicePrice);

            // 10. Create service record
            $agentService = AgentService::create([
                'reference' => 'F-' . strtoupper(Str::random(10)),
                'user_id' => $user->id,
                'service_id' => $serviceField->service_id,
                'service_field_id' => $serviceField->id,
                'field_code' => $serviceField->field_code,
                'transaction_id' => $transaction->id,
                'service_type' => 'ipe',
                'tracking_id' => $request->tracking_id,
                'amount' => $servicePrice,
                'status' => 'processing',
                'submission_date' => now(),
                'service_field_name' => $serviceField->field_name,
                'description' => $request->description ?? $serviceField->field_name,
                'performed_by' => $performedBy,
            ]);

            // 11. Commit (Ensure data is saved before API call)
            DB::commit();

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('IPE Clearance Pre-API Error: ' . $e->getMessage());
            return back()->with('error', 'System Error: Failed to initiate request. Please try again.');
        }

        // 12. API Call (Post to s8v)
        try {
            $apiKey = env('NIN_API_KEY');
            $url = 'https://www.s8v.ng/api/clearance';
            
            $payload = [
                'tracking_id' => $request->tracking_id,
                'token' => $apiKey,
            ];

            $response = Http::post($url, $payload);
            $data = $response->json();

            // 13. Handle API Response
            if (!$response->successful() || (isset($data['status']) && ($data['status'] == 'failed' || $data['status'] == 'error'))) {
                // REFUND logic
                DB::beginTransaction();
                try {
                    $wallet = Wallet::where('user_id', $user->id)->lockForUpdate()->first();
                    $wallet->increment('balance', $servicePrice);
                    
                    $errorMessage = $data['message'] ?? 'API Submission Failed';
                    
                    $transaction->update(['status' => 'failed']);
                    $agentService->update([
                        'status' => 'failed',
                        'comment' => $errorMessage
                    ]);

                    DB::commit();
                    return back()->with('error', 'API Submission Failed: ' . $errorMessage . '. Your wallet has been refunded.');
                } catch (\Exception $refundEx) {
                    DB::rollBack();
                    Log::critical('IPE Refund Failure: ' . $refundEx->getMessage(), ['user_id' => $user->id, 'amount' => $servicePrice]);
                    return back()->with('error', 'API Submission Failed and refund failed. Please contact support immediately.');
                }
            }

            $cleanResponse = $this->cleanApiResponse($data);
            $status = $this->normalizeStatus($data['status'] ?? 'processing');

            $transaction->update(['status' => 'completed']);
            $agentService->update([
                'status' => $status,
                'comment' => $cleanResponse,
            ]);

            return back()->with('success', 'Request submitted successfully. Status: ' . $status);

        } catch (\Exception $e) {
            Log::error('IPE Clearance API-Phase Error: ' . $e->getMessage());
            // Attempt refund on exception too
            DB::beginTransaction();
            try {
                $wallet = Wallet::where('user_id', $user->id)->lockForUpdate()->first();
                $wallet->increment('balance', $servicePrice);
                $transaction->update(['status' => 'failed']);
                $agentService->update(['status' => 'failed', 'comment' => 'Exception during API call: ' . $e->getMessage()]);
                DB::commit();
            } catch (\Exception $refundEx) {
                DB::rollBack();
            }
            return back()->with('error', 'Communication Error: Failed to reach API. Your request has been queued/refunded where possible.');
        }
    }


    public function checkStatus(Request $request, $id = null)
    {
        $user = Auth::user();
        if (($user->status ?? 'inactive') !== 'active') {
            if ($request->wantsJson() || $request->is('api/*')) {
                return response()->json(['success' => false, 'message' => "Your account is " . ($user->status ?? 'inactive') . ". Access denied."]);
            }
            return redirect()->back()->with('error', "Your account is currently " . ($user->status ?? 'inactive') . ". Access denied.");
        }

        try {
            if ($id) {
                $agentService = AgentService::findOrFail($id);
            } else {
                $request->validate([
                    'tracking_id' => 'required|string',
                ]);
                $agentService = AgentService::where('tracking_id', $request->tracking_id)
                    ->orderBy('created_at', 'desc')
                    ->firstOrFail();
            }

            $apiKey = env('NIN_API_KEY');
            $url = 'https://www.s8v.ng/api/clearance/status';
            $payload = [
                'tracking_id' => $agentService->tracking_id,
                'token' => $apiKey
            ];

            $response = Http::post($url, $payload);
            $apiResponse = $response->json();

            if (!$response->successful()) {
                throw new \Exception('API responded with status ' . $response->status());
            }

            $cleanResponse = $this->cleanApiResponse($apiResponse);

            $updateData = [
                'comment' => $cleanResponse,
            ];

            // Map "status" or "response" or "message" to our internal status
            $apiStatus = $apiResponse['status'] ?? $apiResponse['response'] ?? $apiResponse['message'] ?? 'pending';
            $updateData['status'] = $this->normalizeStatus($apiStatus);

            $agentService->update($updateData);

            if ($request->wantsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => true,
                    'tracking_id' => $agentService->tracking_id,
                    'status' => $agentService->status,
                    'response' => $apiResponse,
                    'clean_comment' => $cleanResponse
                ]);
            }

            return back()->with('success', 'Status checked successfully. Current status: ' . $agentService->status);

        } catch (\Exception $e) {
            Log::error('IPE Status Check Error: ' . $e->getMessage());

            if ($request->wantsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to check status: ' . $e->getMessage(),
                    'status' => 'error'
                ], 500);
            }
            return back()->with('error', 'Unable to complete the status check. Please try again.');
        }
    }

    public function webhook(Request $request)
    {
        $data = $request->all();

        Log::info('IPE Clearance Webhook Received', $data);

        $identifier = $data['tracking_id'] ?? null;

        if ($identifier) {
            $submission = AgentService::where('tracking_id', $identifier)
                ->orderBy('created_at', 'desc')
                ->first();

            if ($submission) {
                $cleanResponse = $this->cleanApiResponse($data);
                
                $updateData = [
                    'comment' => $cleanResponse,
                ];

                if (isset($data['status'])) {
                    $updateData['status'] = $this->normalizeStatus($data['status']);
                }

                $submission->update($updateData);

                Log::info('IPE Clearance Updated via Webhook', [
                    'submission_id' => $submission->id,
                    'identifier' => $identifier,
                    'new_status' => $updateData['status'] ?? 'unknown'
                ]);
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Webhook received successfully'
        ]);
    }

    private function cleanApiResponse($response): string
    {
        if (is_array($response)) {
            $jsonString = json_encode($response, JSON_PRETTY_PRINT);
        } else {
            $jsonString = (string) $response;
        }

        $cleanResponse = str_replace(['{', '}', '"', "'"], '', $jsonString);
        $cleanResponse = preg_replace('/\s+/', ' ', $cleanResponse);
        $cleanResponse = trim(strip_tags($cleanResponse));

        return $cleanResponse;
    }

    private function normalizeStatus($status): string
    {
        $s = strtolower(trim((string) $status));
        
        return match ($s) {
            'successful', 'success', 'resolved', 'in_progress', 'approved', 'completed' => 'successful',
            'processing', 'pending', 'submitted', 'new' => 'processing',
            'failed', 'rejected', 'error', 'declined', 'invalid', 'no record' => 'failed',
            default => 'pending',
        };
    }
}
