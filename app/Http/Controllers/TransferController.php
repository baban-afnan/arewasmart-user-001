<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Wallet;
use App\Models\Transaction;
use App\Models\ServiceField;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class TransferController extends Controller
{
    public function index()
    {
        return view('transfer.index');
    }

    public function verifyUser(Request $request)
    {
        $request->validate([
            'wallet_id' => 'required|string',
        ]);

        $wallet = Wallet::where('wallet_number', $request->wallet_id)->first();

        if ($wallet && $wallet->user) {
            $user = $wallet->user;
            $fullName = trim($user->first_name . ' ' . $user->last_name . ' ' . $user->middle_name);
            return response()->json([
                'success' => true,
                'user_name' => $fullName
            ]);
        }

        return response()->json(['success' => false, 'message' => 'User not found']);
    }

    public function processTransfer(Request $request)
    {
        $request->validate([
            'wallet_id' => 'required|exists:wallets,wallet_number',
            'amount' => 'required|numeric|min:1',
            'description' => 'nullable|string|max:255',
            'pin' => 'required|digits:5',
        ]);

        $user = Auth::user();
        $senderWallet = $user->wallet;
        $amount = $request->amount;
        
        // Verify PIN
        if (!Hash::check($request->pin, $user->pin)) {
             return back()->with('error', 'Incorrect Transaction PIN.');
        }

        // Get Receiver
        $receiverWallet = Wallet::where('wallet_number', $request->wallet_id)->first();
        if (!$receiverWallet) {
            return back()->with('error', 'Receiver wallet not found.');
        }
        
        if ($senderWallet->id === $receiverWallet->id) {
            return back()->with('error', 'You cannot transfer money to yourself.');
        }

        // Get Service Charge
        // Assuming there is a service field for P2P. 
        // If not found, we might default to 0 or throw error. 
        // User said "get the service name P2P and get service price from the servicefield"
        $serviceField = ServiceField::where('field_name', 'P2P')->first();
        $charge = 0;
        
        if ($serviceField) {
            // charge the user based on role
            $charge = $serviceField->getPriceForUserType($user->role ?? 'user'); 
        }

        $totalDeduction = $amount + $charge;

        if ($senderWallet->balance < $totalDeduction) {
            return back()->with('error', 'Insufficient balance.');
        }

        DB::beginTransaction();

        try {
            // Debit Sender
            $senderWallet->balance -= $totalDeduction;
            $senderWallet->save();

            // Credit Receiver
            $receiverWallet->balance += $amount;
            $receiverWallet->save();

            $transactionRef = 'TRX-' . strtoupper(Str::random(10));

            // Create Sender Transaction
            $senderTransaction = Transaction::create([
                'transaction_ref' => $transactionRef,
                'user_id' => $user->id,
                'amount' => $amount,
                'fee' => $charge,
                'net_amount' => $totalDeduction,
                'description' => $request->description ?? 'Transfer to ' . $receiverWallet->user->first_name,
                'type' => 'debit',
                'status' => 'completed',
                'metadata' => [
                    'service' => 'P2P',
                    'receiver_wallet' => $receiverWallet->wallet_number,
                    'receiver_name' => $receiverWallet->user->first_name . ' ' . $receiverWallet->user->last_name
                ],
                'performed_by' => $user->id,
            ]);

            // Create Receiver Transaction
            Transaction::create([
                'transaction_ref' => 'TRX-' . strtoupper(Str::random(10)), // Unique ref for receiver? Or same? Usually different or linked.
                'user_id' => $receiverWallet->user->id,
                'amount' => $amount,
                'fee' => 0,
                'net_amount' => $amount,
                'description' => 'Received from ' . $user->first_name,
                'type' => 'credit',
                'status' => 'completed',
                'metadata' => [
                    'service' => 'P2P',
                    'sender_wallet' => $senderWallet->wallet_number,
                    'sender_name' => $user->first_name . ' ' . $user->last_name
                ],
                'performed_by' => $user->id,
            ]);

            DB::commit();

            return view('thankyou2', [
                'transaction' => $senderTransaction,
                'sender' => $user,
                'receiver' => $receiverWallet->user,
                'amount' => $amount,
                'date' => now()
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Transaction failed: ' . $e->getMessage());
        }
    }
}
