<?php

namespace App\Http\Controllers\Agency;

use App\Http\Controllers\Controller;
use App\Models\AgentService;
use App\Models\Transaction;
use App\Models\Wallet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class LoanController extends Controller
{
    /**
     * Display the loan application page.
     */
    public function index()
    {
        $user = Auth::user();
        
        // Sum all completed debit transactions for this user
        $totalTransactions = Transaction::where('user_id', $user->id)
            ->where('type', 'debit')
            ->where('status', 'completed')
            ->sum('amount');

        $threshold = 100000; // 1,000,000
        $isEligible = $totalTransactions >= $threshold;

        $wallet = Wallet::where('user_id', $user->id)->first();
        
        // Fetch previous loan requests
        $submissions = AgentService::where('user_id', $user->id)
            ->where('service_type', 'loan')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('pages.dashboard.loan', compact('isEligible', 'totalTransactions', 'threshold', 'wallet', 'submissions'));
    }

    /**
     * Submit a new loan request.
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        
        // Verify eligibility again on submission
        $totalTransactions = Transaction::where('user_id', $user->id)
            ->where('type', 'debit')
            ->where('status', 'completed')
            ->sum('amount');

        if ($totalTransactions < 100000) {
            return back()->with('error', 'You are not eligible for a loan yet.');
        }

        $request->validate([
            'request_amount' => 'required|numeric|min:5000',
            'payment_plan'   => 'required|string',
        ]);

        try {
            $performedBy = $user->first_name . ' ' . $user->last_name;

            AgentService::create([
                'reference'          => 'LOAN' . strtoupper(Str::random(10)),
                'user_id'            => $user->id,
                'service_type'       => 'loan',
                'service_name'       => 'Loan Application',
                'amount'             => $request->request_amount,
                'description'        => "Payment Plan: " . $request->payment_plan,
                'status'             => 'pending',
                'submission_date'    => now(),
                'performed_by'       => $performedBy,
            ]);

            return back()->with('success', 'Your loan application has been submitted successfully and is under review.');
            
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to submit loan request. Please try again.');
        }
    }
}
