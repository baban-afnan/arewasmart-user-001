<?php

namespace App\Http\Controllers\Agency;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\AgentService;
use App\Models\Service;
use App\Models\ServiceField;
use App\Models\Transaction;
use App\Models\Wallet;
use Illuminate\Support\Facades\Storage;

class TinRegistrationController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $serviceKey = 'TIN'; // Assuming the service name in DB is 'TIN'

        // Query submissions
        $submissions = AgentService::with('transaction')
            ->where('user_id', $user->id)
            ->where('service_name', $serviceKey)
            ->when($request->filled('search'), fn($q) =>
                $q->where('reference', 'like', "%{$request->search}%")
                  ->orWhere('field_name', 'like', "%{$request->search}%"))
            ->orderByRaw("
                CASE
                    WHEN status = 'pending' THEN 1
                    WHEN status = 'processing' THEN 2
                    WHEN status = 'query' THEN 3
                    WHEN status = 'successful' THEN 4
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

        return view('pages.dashboard.tin.index', [
            'fields'        => $fields,
            'service'       => $service,
            'submissions'   => $submissions,
            'servicePrices' => $prices,
            'wallet'        => $wallet,
        ]);
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        $serviceKey = 'TIN';

        // Validate Service Field ID first to determine requirements
        $request->validate([
            'service_field_id' => 'required|exists:service_fields,id',
        ]);

        $serviceField = ServiceField::with(['service', 'prices'])->findOrFail($request->service_field_id);
        $serviceName = $serviceField->service->name; // Should be 'TIN'
        $fieldName = $serviceField->field_name; // e.g., 'Individual' or 'Corporate'

        // Determine if it's Individual or Corporate based on the field name
        $isCorporate = stripos($fieldName, 'Corporate') !== false || stripos($fieldName, 'Organisation') !== false || stripos($fieldName, 'Company') !== false;

        // Define Base Rules
        $rules = [
            'email' => 'required|email',
            'phone_number' => 'required|string',
            'state' => 'required|string',
            'lga' => 'required|string',
            'city' => 'required|string',
            'house_number' => 'required|string',
            'street_name' => 'required|string',
            'nearest_bus_stop' => 'required|string',
            'country' => 'required|string',
        ];

        if ($isCorporate) {
            $rules = array_merge($rules, [
                'company_name' => 'required|string',
                'organization_type' => 'required|string',
                'registration_number' => 'required|string',
                'cac_certificate' => 'required|file|mimes:jpg,jpeg,png,pdf|max:2048',
                // Require contact person details for corporate as well
                'first_name' => 'required|string',
                'last_name' => 'required|string',
            ]);
        } else {
            // Individual Rules
            $rules = array_merge($rules, [
                'bvn' => 'required|string',
                'first_name' => 'required|string',
                'last_name' => 'required|string',
                'middle_name' => 'nullable|string',
                'nin' => 'required|string',
                'date_of_birth' => 'required|date',
                'marital_status' => 'required|string',
                'passport_upload' => 'required|file|mimes:jpg,jpeg,png,pdf|max:2048',
            ]);
        }

        $request->validate($rules);

        // Determine price
        $servicePrice = $serviceField->prices
            ->where('user_type', $user->role)
            ->first()?->price ?? $serviceField->base_price;

        $totalAmount = $servicePrice;

        if ($servicePrice === null) {
            return back()->with(['status' => 'error', 'message' => 'Service price not configured.'])->withInput();
        }

        $wallet = Wallet::where('user_id', $user->id)->firstOrFail();

        if ($wallet->status !== 'active') {
            return back()->with(['status' => 'error', 'message' => 'Your wallet is not active.'])->withInput();
        }

        if ($wallet->balance < $totalAmount) {
            return back()->with(['status' => 'error', 'message' => 'Insufficient balance.'])->withInput();
        }

        DB::beginTransaction();

        try {
            $reference = 'TIN' . date('ymd') . strtoupper(substr(uniqid(), -5));
            $performedBy = trim($user->first_name . ' ' . $user->last_name);

            $uploads = [];

            if ($isCorporate) {
                if ($request->hasFile('cac_certificate')) {
                    $uploads['cac_certificate'] = $request->file('cac_certificate')->store('uploads/tin/cac', 'public');
                }
            } else {
                if ($request->hasFile('passport_upload')) {
                    $uploads['passport'] = $request->file('passport_upload')->store('uploads/tin/passport', 'public');
                }
            }

            // Prepare Data for JSON field
            $formData = $request->except([
                '_token', 'passport_upload', 'cac_certificate'
            ]);
            $formData['uploads'] = $uploads;

            // Create Transaction
            $transaction = Transaction::create([
                'transaction_ref' => $reference,
                'user_id'         => $user->id,
                'amount'          => $totalAmount,
                'performed_by'    => $performedBy,
                'description'     => "{$serviceName} Registration - {$fieldName}",
                'type'            => 'debit',
                'status'          => 'completed',
                'metadata'        => [
                    'service' => $serviceName,
                    'field' => $fieldName,
                    'details' => $formData
                ],
            ]);

            // Create AgentService
            AgentService::create([
                'reference'       => $reference,
                'user_id'         => $user->id,
                'service_id'      => $serviceField->service_id,
                'service_field_id'=> $serviceField->id,
                'field_code'      => $serviceField->id,
                'service_name'    => $serviceName,
                'field_name'      => $fieldName,
                'amount_paid'     => $totalAmount,
                'performed_by'    => $performedBy,
                'transaction_id'  => $transaction->id,
                'submission_date' => now(),
                'status'          => 'pending',
                'service_type'    => $isCorporate ? 'Tin_corporate' : 'Tin_individual',
                'field'           => json_encode($formData),
                'first_name'      => $request->first_name,
                'last_name'       => $request->last_name,
                'business_name'   => $request->company_name ?? null,
                'email'           => $request->email,
                'phone_number'    => $request->phone_number,
                'number'          => $request->phone_number,
                'state'           => $request->state,
                'lga'             => $request->lga,
                'passport_url'    => isset($uploads['passport']) ? asset('storage/' . $uploads['passport']) : null,
            ]);

            $wallet->decrement('balance', $totalAmount);

            DB::commit();

            return redirect()->route('cac.tin')->with([
                'status' => 'success',
                'message' => 'TIN Registration submitted successfully. Reference: ' . $reference
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with(['status' => 'error', 'message' => 'Error: ' . $e->getMessage()])->withInput();
        }
    }
}
