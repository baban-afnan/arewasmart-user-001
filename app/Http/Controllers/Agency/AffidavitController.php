<?php

namespace App\Http\Controllers\Agency;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Models\AgentService;
use App\Models\Service;
use App\Models\ServiceField;
use App\Models\Transaction;
use App\Models\Wallet;
use App\Http\Controllers\Controller;

class AffidavitController extends Controller
{
    /**
     * Display the affidavit service form and submission history.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $serviceKey = 'Affidavit-app';

        // Query only this user's submissions
        $submissions = AgentService::with('transaction')
            ->where('user_id', $user->id)
            ->where('service_name', $serviceKey)
            ->when($request->filled('search'), fn($q) =>
                $q->where('reference', 'like', "%{$request->search}%"))
            ->when($request->filled('status'), fn($q) =>
                $q->where('status', $request->status))
            ->orderByRaw("
                CASE
                    WHEN status = 'pending' THEN 1
                    WHEN status = 'processing' THEN 2
                    WHEN status = 'successful' THEN 3
                    WHEN status = 'query' THEN 4
                    ELSE 99
                END
            ")->orderByDesc('submission_date')
            ->paginate(10)
            ->withQueryString();

        // Load active service and its fields
        $service = Service::where('name', $serviceKey)
            ->where('is_active', true)
            ->with(['fields' => fn($q) => $q->where('is_active', true), 'prices'])
            ->first();

        $wallet = Wallet::firstOrCreate(
            ['user_id' => $user->id],
            ['balance' => 0.00, 'status' => 'active']
        );

        $fields = $service?->fields ?? collect();
        $prices = $service?->prices ?? collect();

        return view('pages.dashboard.affidavit.index', [
            'fieldname'     => $fields,
            'services'      => Service::where('is_active', true)->get(),
            'serviceName'   => $serviceKey,
            'submissions'   => $submissions,
            'servicePrices' => $prices,
            'wallet'        => $wallet,
        ]);
    }

    /**
     * Store submission for Affidavit.
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        if (($user->status ?? 'inactive') !== 'active') {
             return redirect()->back()->with('error', "Your account is currently " . ($user->status ?? 'inactive') . ". Access denied.");
        }

        $serviceKey = 'Affidavit';

        // Validation rules
        $request->validate([
            'field_code' => 'required|exists:service_fields,id',
            'old_details' => 'required|string',
            'new_details' => 'required|string',
            'nin_slip' => 'required|file|mimes:jpg,jpeg,png,pdf|max:2048',
            'passport' => 'required|file|mimes:jpg,jpeg,png,pdf|max:2048',
        ]);

        $serviceField = ServiceField::with(['service', 'prices'])->findOrFail($request->field_code);
        $serviceName = $serviceField->service->name;
        $fieldName = $serviceField->field_name;

        // Determine correct price for user
        $servicePrice = $serviceField->prices
            ->where('user_type', $user->role)
            ->first()?->price ?? $serviceField->base_price;

        $totalAmount = $servicePrice;

        if ($servicePrice === null) {
            return back()->with([
                'status'  => 'error',
                'message' => 'Service price not configured for your account type.'
            ])->withInput();
        }

        $wallet = Wallet::where('user_id', $user->id)->firstOrFail();

        if ($wallet->status !== 'active') {
            return back()->with(['status' => 'error', 'message' => 'Your wallet is not active.'])->withInput();
        }

        if ($wallet->balance < $totalAmount) {
            return back()->with([
                'status'  => 'error',
                'message' => 'Insufficient balance. You need NGN ' .
                    number_format($totalAmount - $wallet->balance, 2) . ' more.'
            ])->withInput();
        }

        DB::beginTransaction();

        try {
            // Handle File Uploads using Laravel's Storage
            $ninSlipFile = $request->file('nin_slip');
            $passportFile = $request->file('passport');

            // Generate unique filenames
            $ninSlipName = 'nin_slip_' . time() . '_' . Str::random(10) . '.' . $ninSlipFile->getClientOriginalExtension();
            $passportName = 'passport_' . time() . '_' . Str::random(10) . '.' . $passportFile->getClientOriginalExtension();

            // Store files in storage/app/public/uploads/affidavit
            $ninSlipPath = $ninSlipFile->storeAs('uploads/affidavit/nin_slips', $ninSlipName, 'public');
            $passportPath = $passportFile->storeAs('uploads/affidavit/passports', $passportName, 'public');

            // Get full URLs
            $ninSlipUrl = Storage::disk('public')->url($ninSlipPath);
            $passportUrl = Storage::disk('public')->url($passportPath);

            $reference = 'AFF' . date('is') . strtoupper(substr(uniqid(mt_rand(), true), -5));
            $performedBy = trim($user->first_name . ' ' . $user->last_name);

            // Capture complete transaction metadata
            $fullMetadata = [
                'service_key'   => $serviceKey,
                'field_details' => [
                    'id'         => $serviceField->id,
                    'name'       => $fieldName,
                    'code'       => $serviceField->field_code,
                ],
                'user_details'  => [
                    'id'    => $user->id,
                    'name'  => $performedBy,
                    'role'  => $user->role,
                    'email' => $user->email,
                ],
                'request_data'  => [
                    'old_details' => $request->old_details,
                    'new_details' => $request->new_details,
                ],
                'pricing'       => [
                    'unit_price'  => $servicePrice,
                    'total_amount' => $totalAmount,
                ],
                'wallet_before' => $wallet->balance,
                'transaction_time' => now()->toDateTimeString(),
                'channel' => 'Affidavit Portal',
            ];

            // Create transaction
            $transaction = Transaction::create([
                'transaction_ref' => $reference,
                'user_id'         => $user->id,
                'amount'          => $totalAmount,
                'performed_by'    => $performedBy,
                'description'     => "{$serviceName} Request for {$fieldName}",
                'type'            => 'debit',
                'status'          => 'completed',
                'metadata'        => [
                    'service_name'  => $serviceName,
                    'field_name'    => $fieldName,
                    'old_details'   => $request->old_details,
                    'new_details'   => $request->new_details,
                    'price_details' => [
                        'unit_price'   => $servicePrice,
                        'total_amount' => $totalAmount,
                    ],
                    'user_details'  => [
                        'id'    => $user->id,
                        'name'  => $performedBy,
                        'role'  => $user->role,
                        'email' => $user->email,
                    ],
                    'wallet_before_transaction' => $wallet->balance,
                    'channel' => 'Affidavit Portal',
                ],
            ]);

            // Create main submission record
            AgentService::create([
                'reference'       => $reference,
                'user_id'         => $user->id,
                'service_id'      => $serviceField->service_id,
                'service_field_id' => $serviceField->id,
                'field_code'      => $serviceField->field_code,
                'service_name'    => $serviceName,
                'field_name'      => $fieldName,
                'description'     => "Old Details: " . $request->old_details . "\nNew Details: " . $request->new_details,
                'nin_slip_url'    => $ninSlipUrl, 
                'passport_url'    => $passportUrl, 
                'amount'          => $totalAmount,
                'performed_by'    => $performedBy,
                'transaction_id'  => $transaction->id,
                'submission_date' => now(),
                'status'          => 'pending',
                'service_type'    => $serviceName,
            ]);

            // Deduct wallet
            $wallet->decrement('balance', $totalAmount);

            DB::commit();

            return redirect()->route('affidavit.index')->with([
                'status'  => 'success',
                'message' => "Affidavit request submitted successfully. Your request will be processed and the affidavit will be shared with you once approved. Ref: {$reference}. Charged: ₦" . number_format($totalAmount, 2),
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            
            // Clean up uploaded files if transaction fails
            if (isset($ninSlipPath)) {
                Storage::disk('public')->delete($ninSlipPath);
            }
            if (isset($passportPath)) {
                Storage::disk('public')->delete($passportPath);
            }
            
            report($e);

            return back()->with([
                'status'  => 'error',
                'message' => 'Submission failed: ' . $e->getMessage(),
            ])->withInput();
        }
    }
}