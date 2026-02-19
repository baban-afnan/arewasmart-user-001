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

        $serviceKey = 'Affidavit-app'; // Consistency with index

        // 1. Validation
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

        // Determine price
        $servicePrice = $serviceField->prices
            ->where('user_type', $user->role)
            ->first()?->price ?? $serviceField->base_price;

        if ($servicePrice === null) {
            return back()->with('error', 'Service price not configured.')->withInput();
        }

        $wallet = Wallet::where('user_id', $user->id)->firstOrFail();
        if ($wallet->status !== 'active') {
            return back()->with('error', 'Your wallet is not active.')->withInput();
        }

        if ($wallet->balance < $servicePrice) {
            return back()->with('error', 'Insufficient balance.')->withInput();
        }

        $reference = 'AFF' . date('is') . strtoupper(substr(uniqid(mt_rand(), true), -5));
        $performedBy = trim($user->first_name . ' ' . $user->last_name);

        DB::beginTransaction();

        try {
            // 2. Charge Wallet First
            $wallet->decrement('balance', $servicePrice);

            // 3. Handle File Uploads
            $ninSlipFile = $request->file('nin_slip');
            $passportFile = $request->file('passport');

            $ninSlipName = 'nin_slip_' . time() . '_' . Str::random(10) . '.' . $ninSlipFile->getClientOriginalExtension();
            $passportName = 'passport_' . time() . '_' . Str::random(10) . '.' . $passportFile->getClientOriginalExtension();

            $ninSlipPath = $ninSlipFile->storeAs('uploads/affidavit/nin_slips', $ninSlipName, 'public');
            $passportPath = $passportFile->storeAs('uploads/affidavit/passports', $passportName, 'public');

            $ninSlipUrl = Storage::disk('public')->url($ninSlipPath);
            $passportUrl = Storage::disk('public')->url($passportPath);

            // 4. Create Transaction Record (Completed as it's an internal service)
            $transaction = Transaction::create([
                'transaction_ref' => $reference,
                'user_id'         => $user->id,
                'amount'          => $servicePrice,
                'performed_by'    => $performedBy,
                'description'     => "{$serviceName} Request for {$fieldName}",
                'type'            => 'debit',
                'status'          => 'completed',
                'metadata'        => json_encode([
                    'service_name'  => $serviceName,
                    'field_name'    => $fieldName,
                    'old_details'   => $request->old_details,
                    'new_details'   => $request->new_details,
                    'price'         => $servicePrice,
                    'user'          => ['id' => $user->id, 'name' => $performedBy],
                    'files'         => ['nin_slip' => $ninSlipUrl, 'passport' => $passportUrl],
                ]),
            ]);

            // 5. Create AgentService Submission record
            AgentService::create([
                'reference'       => $reference,
                'user_id'         => $user->id,
                'service_id'      => $serviceField->service_id,
                'service_field_id'=> $serviceField->id,
                'field_code'      => $serviceField->field_code,
                'service_name'    => 'Affidavit-app', // Consistency
                'field_name'      => $fieldName,
                'description'     => "Old: {$request->old_details}\nNew: {$request->new_details}",
                'passport_url'    => $passportUrl, 
                'amount'          => $servicePrice,
                'performed_by'    => $performedBy,
                'transaction_id'  => $transaction->id,
                'submission_date' => now(),
                'status'          => 'pending',
                'service_type'    => 'Affidavit',
            ]);

            DB::commit();

            return redirect()->route('affidavit.index')->with([
                'status'  => 'success',
                'message' => "Request submitted successfully. Ref: {$reference}",
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Affidavit Submission Error: ' . $e->getMessage());
            return back()->with('error', 'Submission failed. Please try again.')->withInput();
        }
    }
}