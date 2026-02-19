<?php

namespace App\Http\Controllers\Action;

use App\Helpers\RequestIdHelper;
use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Models\Wallet;
use App\Models\SmeData;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Traits\ActiveUsers;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class SmeDataController extends Controller
{
    use ActiveUsers;

    // API Configuration - matching DataController
    private function getApiBaseUrl()
    {
        return env('SME_ENDPOINT', 'https://datastationapi.com/api/data/');
    }

    private function getApiToken()
    {
        return env('AUTH_TOKEN');
    }

    /**
     * Show SME Data Purchase Page
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login')->with('error', 'Please log in to access this page.');
        }

        $wallet = Wallet::firstOrCreate(
            ['user_id' => $user->id],
            ['balance' => 0.00, 'status' => 'active']
        );

        $networks = SmeData::select('network')->distinct()->get();

        // Price lists for the advert section
        $priceList1 = DB::table('data_variations')->where('service_id', 'mtn-data')->paginate(10, ['*'], 'table1_page');
        $priceList2 = DB::table('data_variations')->where('service_id', 'airtel-data')->paginate(10, ['*'], 'table2_page');
        $priceList3 = DB::table('data_variations')->where('service_id', 'glo-data')->paginate(10, ['*'], 'table3_page');
        $priceList4 = DB::table('data_variations')->where('service_id', 'etisalat-data')->paginate(10, ['*'], 'table4_page');
        $priceList5 = DB::table('data_variations')->where('service_id', 'smile-direct')->paginate(10, ['*'], 'table5_page');
        $priceList6 = DB::table('data_variations')->where('service_id', 'spectranet')->paginate(10, ['*'], 'table6_page');

        return view('utilities.buy-sme-data', compact(
            'user', 
            'wallet', 
            'networks',
            'priceList1',
            'priceList2',
            'priceList3',
            'priceList4',
            'priceList5',
            'priceList6'
        ));
    }

    /**
     * Fetch Data Types for a Network
     */
    public function fetchDataType(Request $request)
    {
        $network = $request->id;
        $types = SmeData::where('network', $network)
            ->select('plan_type')
            ->distinct()
            ->get();
        return response()->json($types);
    }

    /**
     * Fetch Data Plans for a Network and Type
     */
    public function fetchDataPlan(Request $request)
    {
        $network = $request->id;
        $type = $request->type;
        $plans = SmeData::where('network', $network)
            ->where('plan_type', $type)
            ->where('status', 'enabled')
            ->get();
        return response()->json($plans);
    }

    /**
     * Fetch Plan Price
     */
    public function fetchSmeBundlePrice(Request $request)
    {
        $planId = $request->id;
        $plan = SmeData::where('data_id', $planId)->first();
        
        if (!$plan) {
            return response()->json("0.00");
        }

        $user = Auth::user();
        $finalPrice = $plan->calculatePriceForRole($user->role ?? 'user');

        return response()->json(number_format((float)$finalPrice, 2));
    }

    /**
     * Buy SME Data Bundle
     */
    public function buySMEdata(Request $request)
    {
        $request->validate([
            'network'  => 'required|string',
            'type'     => 'required|string',
            'plan'     => 'required|string',
            'mobileno' => 'required|numeric|digits:11'
        ]);

        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login')->with('error', 'Please log in to continue.');
        }

        // status check for user account
        if (($user->status ?? 'inactive') !== 'active') {
            return redirect()->back()->with('error', "Your account is currently " . ($user->status ?? 'inactive') . ". Access denied.");
        }

        $mobileno = $request->mobileno;
        $planId = $request->plan;
        
        $plan = SmeData::where('data_id', $planId)
            ->where('network', strtoupper($request->network))
            ->where('status', 'enabled')
            ->first();

        if (!$plan) {
            return back()->with('error', 'Invalid or disabled data plan selected.');
        }

        // Calculate Final Price (SmeData Amount + Service Field Fees)
        $payableAmount = $plan->calculatePriceForRole($user->role ?? 'user');
        $description = "{$plan->size} {$plan->plan_type} for {$mobileno} ({$plan->network})";

        // Check Wallet Balance
        $wallet = Wallet::where('user_id', $user->id)->first();
        if (!$wallet || $wallet->balance < $payableAmount) {
            return redirect()->back()->with('error', 'Insufficient wallet balance! You need ₦' . number_format($payableAmount, 2));
        }

        if (($wallet->status ?? 'inactive') !== 'active') {
            return redirect()->back()->with('error', 'Your wallet is not active. Please contact support.');
        }

        $requestId = RequestIdHelper::generateRequestId();

        // Upstream API Call (DataStation)
        $response = $this->callDataStation($requestId, $plan, $mobileno);

        if (!$response['success']) {
            return redirect()->back()->with('error', $response['message'] ?? 'Data purchase failed. Please try again later.');
        }

        // Process Transaction
        return DB::transaction(function () use ($user, $wallet, $plan, $payableAmount, $mobileno, $requestId, $response, $description) {
            // Debit Wallet
            $wallet->decrement('balance', $payableAmount);

            $transactionRef = $response['transaction_ref'] ?? $requestId;
            $apiData = $response['data'] ?? [];

            Transaction::create([
                'transaction_ref' => $transactionRef,
                'user_id'         => $user->id,
                'amount'          => $payableAmount,
                'description'     => "SME Data purchase: " . $description,
                'type'            => 'debit',
                'status'          => 'completed',
                'metadata'        => json_encode([
                    'phone'        => $mobileno,
                    'network'      => $plan->network,
                    'plan_type'    => $plan->plan_type,
                    'data_id'      => $plan->data_id,
                    'api_response' => $apiData,
                    'request_id'   => $requestId
                ]),
                'performed_by' => $user->first_name . ' ' . $user->last_name,
                'approved_by'  => $user->id,
            ]);

            return redirect()->route('thankyou')->with([
                'success'         => 'Data purchase successful!',
                'transaction_ref' => $transactionRef,
                'request_id'      => $requestId,
                'mobile'          => $mobileno,
                'network'         => $plan->network,
                'amount'          => $payableAmount,
                'paid'            => $payableAmount,
                'type'            => 'data'
            ]);
        });
    }

    /**
     * Call DataStation API for purchase
     */
    private function callDataStation($requestId, $plan, $mobileNumber)
    {
        $networkMap = [
            'MTN'      => 1,
            'AIRTEL'   => 2,
            'GLO'      => 3,
            '9MOBILE'  => 4,
            'ETISALAT' => 4,
        ];

        $networkId = $networkMap[strtoupper($plan->network)] ?? 1;

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Token ' . $this->getApiToken(),
                'Content-Type'  => 'application/json',
            ])->post($this->getApiBaseUrl(), [
                'network'       => $networkId,
                'mobile_number' => $mobileNumber,
                'plan'          => (int)$plan->data_id,
                'Ported_number' => false,
            ]);

            $data = $response->json();
            Log::info('DataStation API Response', ['response' => $data]);

            if ($response->successful() && isset($data['Status']) && strtolower($data['Status']) === 'successful') {
                return [
                    'success'         => true,
                    'data'            => $data,
                    'transaction_ref' => $data['id'] ?? $requestId
                ];
            }

            // Extract error message from DataStation standard format
            $errorMessage = 'Purchase failed at upstream provider.';
            if (isset($data['error']) && is_array($data['error']) && !empty($data['error'])) {
                $errorMessage = $data['error'][0];
            } elseif (isset($data['msg'])) {
                $errorMessage = $data['msg'];
            }

            return [
                'success' => false,
                'message' => $errorMessage,
            ];

        } catch (\Exception $e) {
            Log::error('DataStation API Connection Error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Could not connect to data provider. Please try again later.',
            ];
        }
    }
}
