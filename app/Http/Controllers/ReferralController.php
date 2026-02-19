<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\BonusHistory;
use App\Models\Wallet;
use App\Models\User;

class ReferralController extends Controller
{
    /**
     * Display the referral dashboard
     */
    public function index()
    {
        $user = Auth::user();
        $userId = $user->id;

        // 1. Process Pending Bonuses
        $pendingBonuses = BonusHistory::where('user_id', $userId)
            ->where('status', 'pending')
            ->get();

        foreach ($pendingBonuses as $bonus) {
            // Count successful transactions by the referred user
            $transactionCount = \App\Models\Transaction::where('user_id', $bonus->referred_user_id)
                ->where('status', 'completed')
                ->count();

            if ($transactionCount >= 5) {
                \DB::beginTransaction();
                try {
                    // Update bonus status
                    $bonus->status = 'success';
                    $bonus->save();

                    // Credit the referrer's wallet bonus
                    $wallet = Wallet::where('user_id', $userId)->first();
                    if ($wallet) {
                        $wallet->bonus = ($wallet->bonus ?? 0) + $bonus->amount;
                        $wallet->save();
                    }

                    \DB::commit();
                } catch (\Exception $e) {
                    \DB::rollBack();
                    // Log error if needed
                }
            }
        }

        // Total pending invitations (where transactions < 5)
        $pendingCount = BonusHistory::where('user_id', $userId)
            ->where('type', 'referral')
            ->where('status', 'pending')
            ->count();

        // Total referral earnings (only successful/credited ones)
        $totalEarnings = BonusHistory::where('user_id', $userId)
            ->where('type', 'referral')
            ->where('status', 'success')
            ->sum('amount');

        // Current wallet and bonus balance
        $wallet = Wallet::where('user_id', $userId)->first();
        
        // Fetch history with referred user details - ONLY PENDING
        $bonusHistory = BonusHistory::where('user_id', $userId)
            ->where('status', 'pending')
            ->with('referredUser')
            ->orderBy('id', 'desc')
            ->get();

        // For each bonus in history, attach the current transaction count of the referred user
        foreach ($bonusHistory as $history) {
            $history->referred_user_transaction_count = \App\Models\Transaction::where('user_id', $history->referred_user_id)
                ->where('status', 'completed')
                ->count();
        }

        $referralLink = config('app.url') . '/register?ref=' . $user->referral_code;

        return view('referral.index', compact(
            'user',
            'pendingCount',
            'totalEarnings',
            'wallet',
            'bonusHistory',
            'referralLink'
        ));
    }
}
