<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TransactionController extends Controller
{
    /**
     * Display a listing of the user's transactions.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login');
        }

        $query = Transaction::where('user_id', $user->id)->latest();

        // Filter by Transaction Type (credit, debit, refund)
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        // Filter by Service Type
        if ($request->filled('service_type')) {
            $service = $request->service_type;
            $query->where(function($q) use ($service) {
                $q->where('description', 'like', "%$service%")
                  ->orWhere('metadata', 'like', "%$service%");
            });
        }

        // Filter by Date Range
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $transactions = $query->paginate(10);

        return view('transactions', compact('transactions'));
    }
}
