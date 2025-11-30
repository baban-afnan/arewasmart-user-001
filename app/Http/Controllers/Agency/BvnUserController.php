<?php

namespace App\Http\Controllers\Agency;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use App\Models\ServiceField;
use App\Models\BvnUser;
use App\Models\Transaction;
use App\Models\Service;
use App\Models\Wallet;

class BvnUserController extends Controller
{
    /**
     * Display CRM BVN User submissions list
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        // Ensure wallet exists
        $wallet = Wallet::firstOrCreate(
            ['user_id' => $user->id],
            ['balance' => 0.00, 'status' => 'active']
        );

        // Base query for BVN users
        $query = BvnUser::with(['serviceField', 'transaction'])
            ->where('user_id', $user->id)
            ->whereNotNull('phone_no')
            ->where('phone_no', '<>', '');

        // Search filter
        if ($request->filled('search')) {
            $query->where('phone_no', 'like', '%' . $request->search . '%');
        }

        // Status filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Ordering by status priority
        $query->orderByRaw("
            CASE 
                WHEN status = 'pending' THEN 1
                WHEN status = 'processing' THEN 2
                ELSE 3
            END
        ")->orderByDesc('submission_date');

        $crmSubmissions = $query->paginate(10)->withQueryString();

        // Get active BVN USER service
        $phoneService = Service::where('name', 'BVN USER')
            ->where('is_active', true)
            ->first();

        $serviceFields = $phoneService
            ? ServiceField::where('service_id', $phoneService->id)
                ->where('is_active', true)
                ->get()
            : collect();

        return view('bvn.bvn-user', compact(
            'serviceFields',
            'crmSubmissions',
            'phoneService',
            'wallet'
        ));
    }

    /**
     * Store new BVN User request
     */
    public function store(Request $request)
    {
        $user = Auth::user();

        // Validate inputs
        $validated = $request->validate([
            'service_field_id' => 'required|exists:service_fields,id',
            'bvn' => 'required|string|size:11|regex:/^[0-9]{11}$/',
            'first_name' => 'required|string|max:500',
            'last_name' => 'required|string|max:500',
            'middle_name' => 'nullable|string|max:500',
            'account_no' => 'required|string|max:500',
            'bank_name' => 'required|string|max:500',
            'account_name' => 'required|string|max:500',
            'email' => 'required|string|email|max:500',
            'phone_no' => 'required|string|max:500',
            'dob' => 'required|date',
            'state' => 'required|string|max:500',
            'lga' => 'required|string|max:500',
            'address' => 'required|string|max:500',
            'agent_location' => 'required|string|max:500',
        ]);

        // Prevent duplicate email & phone
        if (BvnUser::where('email', $validated['email'])->exists()) {
            return back()->with([
                'status' => 'error',
                'message' => 'This email has already been used.'
            ]);
        }

        if (BvnUser::where('phone_no', $validated['phone_no'])->exists()) {
            return back()->with([
                'status' => 'error',
                'message' => 'This phone number has already been used.'
            ]);
        }

        // Get service field & price
        $serviceField = ServiceField::with('service')->findOrFail($validated['service_field_id']);
        $servicePrice = $serviceField->getPriceForUserType($user->role);

        if (!$servicePrice) {
            return back()->with([
                'status' => 'error',
                'message' => 'Service price not found for your user type.'
            ]);
        }

        $wallet = Wallet::where('user_id', $user->id)->firstOrFail();

        if ($wallet->balance < $servicePrice) {
            return back()->with([
                'status' => 'error',
                'message' => 'Insufficient wallet balance.'
            ]);
        }

        DB::beginTransaction();

        try {
            $transactionRef = 'A1' . date('is') . strtoupper(Str::random(5));
            $performedBy = trim("{$user->first_name} {$user->last_name}");

            // Create transaction
            $transaction = Transaction::create([
                'transaction_ref' => $transactionRef,
                'user_id' => $user->id,
                'amount' => $servicePrice,
                'description' => "BVN Agent service for {$serviceField->field_name}",
                'type' => 'debit',
                'status' => 'completed',
                'performed_by' => $performedBy,
                'metadata' => [
                    'service' => $serviceField->service->name,
                    'service_field' => $serviceField->field_name,
                    'bvn' => $validated['bvn'],
                ],
            ]);

            // Create BVN user request
            BvnUser::create([
                ...$validated,
                'reference' => $transactionRef,
                'user_id' => $user->id,
                'service_field_id' => $serviceField->id,
                'service_id' => $serviceField->service_id,
                'transaction_id' => $transaction->id,
                'submission_date' => now(),
                'status' => 'pending',
            ]);

            // Deduct wallet balance
            $wallet->decrement('balance', $servicePrice);

            DB::commit();

            return redirect()->route('bvn.index')->with([
                'status' => 'success',
                'message' => 'BVN User request submitted successfully. Reference: ' . $transactionRef
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            report($e);

            return back()->with([
                'status' => 'error',
                'message' => 'Submission failed: ' . $e->getMessage()
            ]);
        }
    }
}
