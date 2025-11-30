<?php

namespace App\Http\Controllers\Action;

use App\Helpers\RequestIdHelper;
use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Models\Wallet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Traits\ActiveUsers;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class DataController extends Controller
{
    use ActiveUsers;

    protected $loginUserId;

     public function __construct()
    {
        $this->loginUserId = Auth::id();
    }

    /**
     * Show Data Services & Price Lists
     */
    public function data(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login')->with('error', 'Please log in to access this page.');
        }

        $wallet = Wallet::firstOrCreate(
            ['user_id' => $user->id],
            ['balance' => 0.00, 'status' => 'active']
        );

        try {
            // Fetch services that end with 'data' or are relevant data services
            $servicename = DB::table('data_variations')
                ->select(['service_id', 'service_name'])
                ->where('status', 'enabled')
                ->where(function($query) {
                    $query->where('service_id', 'LIKE', '%data')
                          ->orWhere('service_id', 'smile-direct')
                          ->orWhere('service_id', 'spectranet');
                })
                ->distinct()
                ->limit(6)
                ->get();

            $priceList1 = DB::table('data_variations')->where('service_id', 'mtn-data')->paginate(10, ['*'], 'table1_page');
            $priceList2 = DB::table('data_variations')->where('service_id', 'airtel-data')->paginate(10, ['*'], 'table2_page');
            $priceList3 = DB::table('data_variations')->where('service_id', 'glo-data')->paginate(10, ['*'], 'table3_page');
            $priceList4 = DB::table('data_variations')->where('service_id', 'etisalat-data')->paginate(10, ['*'], 'table4_page');
            $priceList5 = DB::table('data_variations')->where('service_id', 'smile-direct')->paginate(10, ['*'], 'table5_page');
            $priceList6 = DB::table('data_variations')->where('service_id', 'spectranet')->paginate(10, ['*'], 'table6_page');

            // Fetch recent unique phone numbers for suggestions
            $recentNumbers = \App\Models\Report::where('user_id', $user->id)
                ->whereNotNull('phone_number')
                ->latest()
                ->take(10)
                ->pluck('phone_number')
                ->unique();

            return view('utilities.buy-data', compact(
                'servicename', 'priceList1', 'priceList2', 'priceList3',
                'priceList4', 'priceList5', 'priceList6', 'wallet', 'recentNumbers'
            ));
        } catch (\Exception $e) {
            Log::error("Error loading data services: " . $e->getMessage());
            return back()->with('error', 'Unable to load data services. Please try again.');
        }
    }

    /**
     * Verify transaction PIN
     */
    public function verifyPin(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['valid' => false, 'message' => 'Unauthorized']);
        }

        $isValid = Hash::check($request->pin, $user->pin);
        return response()->json(['valid' => $isValid]);
    }

    /**
     * Sync Variations from API
     */
    public function getVariation(Request $request)
    {
        try {
            $response = Http::get(env('VARIATION_URL') . $request->type);

            if ($response->successful()) {
                $data = $response->json();
                $service_name = $data['content']['ServiceName'] ?? null;
                $service_id = $data['content']['serviceID'] ?? null;
                $convinience_fee = $data['content']['convinience_fee'] ?? 0;

                if (isset($data['content']['varations'])) {
                    foreach ($data['content']['varations'] as $variation) {
                        DB::table('data_variations')->updateOrInsert(
                            ['variation_code' => $variation['variation_code']],
                            [
                                'service_name'    => $service_name,
                                'service_id'      => $service_id,
                                'convinience_fee' => $convinience_fee,
                                'name'            => $variation['name'],
                                'variation_amount'=> $variation['variation_amount'],
                                'fixedPrice'      => $variation['fixedPrice'],
                                'status'          => 'enabled',
                                'created_at'      => Carbon::now(),
                                'updated_at'      => Carbon::now()
                            ]
                        );
                    }
                }
            } else {
            }
        } catch (\Exception $e) {
            
        }
    }
    /**
     * Buy Data Bundle
     */
    public function buydata(Request $request)
    {
        $request->validate([
            'network'  => 'required|string',
            'mobileno' => 'required|numeric|digits:11',
            'bundle'   => 'required|string'
        ]);

        $requestId = RequestIdHelper::generateRequestId();
        $user      = Auth::user();
        $wallet    = Wallet::where('user_id', $user->id)->first();

        if (!$wallet) {
            return back()->with('error', 'Wallet not found.');
        }

        // Fetch Bundle Details
        $variation = DB::table('data_variations')->where('variation_code', $request->bundle)->first();
        if (!$variation) {
             return back()->with('error', 'Invalid data bundle selected.');
        }
        
        $amount = $variation->variation_amount; // Face value / API price
        $description = $variation->name ?? 'Data Bundle';
        $networkKey = $request->network; // e.g., mtn-data

        // --- Discount Logic Start ---
        // 1. Find the Service (e.g., Data)
        $service = \App\Models\Service::where('name', 'Data')->first();
        if (!$service) {
             $service = \App\Models\Service::firstOrCreate(['name' => 'Data'], ['status' => 'active']);
        }

        // 2. Find the specific Network Field (e.g., mtn-data)
        // We search by field_name or field_code matching the network key
        $serviceField = \App\Models\ServiceField::where('service_id', $service->id)
            ->where(function($q) use ($networkKey) {
                $q->where('field_name', 'LIKE', "%{$networkKey}%")
                  ->orWhere('field_code', 'LIKE', "%{$networkKey}%");
            })->first();

        // 3. Calculate Discount
        $discountPercentage = 0;
        if ($serviceField) {
            $userType = $user->user_type ?? 'user';
            $servicePrice = \App\Models\ServicePrice::where('service_fields_id', $serviceField->id)
                ->where('user_type', $userType)
                ->first();

            if ($servicePrice) {
                $discountPercentage = $servicePrice->price; // e.g., 10 for 10%
            } else {
                $discountPercentage = $serviceField->base_price ?? 0;
            }
        }

        // Apply percentage discount
        $discountAmount = ($amount * $discountPercentage) / 100;
        $payableAmount = $amount - $discountAmount;
        // --- Discount Logic End ---

        if ($wallet->balance < $payableAmount) {
            return back()->with('error', 'Insufficient wallet balance! You need ₦' . number_format($payableAmount, 2));
        }

        try {
             // Make Payment via VTPass
            $response = Http::withHeaders([
                'api-key'    => env('API_KEY'),
                'secret-key' => env('SECRET_KEY'),
            ])->post(env('MAKE_PAYMENT'), [
                'request_id'     => $requestId,
                'serviceID'      => $request->network,
                'billersCode'    => env('BIILER_CODE'),
                'variation_code' => $request->bundle,
                'phone'          => $request->mobileno,
            ]);

            if ($response->successful()) {
                $data = $response->json();

                // Check success codes
                $successCodes = ['0','00','000','200'];
                $isSuccessful = (isset($data['code']) && in_array((string)$data['code'], $successCodes)) ||
                                (isset($data['status']) && strtolower($data['status']) === 'success');

                if ($isSuccessful) {
                    $oldBalance = $wallet->balance;
                    $wallet->decrement('balance', $payableAmount);
                    $newBalance = $wallet->balance;

                    $payer_name = $user->first_name . ' ' . $user->last_name;

                    // Create Transaction
                    Transaction::create([
                        'transaction_ref' => $requestId,
                        'user_id'         => $user->id,
                        'amount'          => $payableAmount,
                        'description'     => "Data purchase of {$description} for {$request->mobileno}",
                        'type'            => 'debit',
                        'status'          => 'completed',
                        'metadata'        => json_encode([
                            'phone'        => $request->mobileno,
                            'network'      => $networkKey,
                            'original_amt' => $amount,
                            'discount'     => $discountAmount,
                            'api_response' => $data,
                        ]),
                        'performed_by' => $payer_name,
                        'approved_by'  => $user->id,
                    ]);

                    // Create Report (Save phone number here)
                    \App\Models\Report::create([
                        'user_id'      => $user->id,
                        'phone_number' => $request->mobileno,
                        'network'      => $networkKey,
                        'ref'          => $requestId,
                        'amount'       => $amount, // Face value
                        'status'       => 'successful',
                        'type'         => 'data',
                        'description'  => "Data purchase: {$description}",
                        'old_balance'  => $oldBalance,
                        'new_balance'  => $newBalance,
                        'service_id'   => $serviceField ? $serviceField->id : null,
                    ]);

                    return redirect()->route('thankyou')->with([
                        'success' => 'Data purchase successful!',
                        'ref'     => $requestId,
                        'mobile'  => $request->mobileno,
                        'amount'  => $amount,
                        'paid'    => $payableAmount,
                        'network' => $networkKey
                    ]);
                }

                Log::error('Data API Response Error', ['response' => $data]);
                return back()->with('error', 'Data purchase failed. Please try again.');
            }

            Log::error('Data API HTTP Error', ['status' => $response->status(), 'body' => $response->body()]);
            return back()->with('error', 'Data purchase failed. Please try again.');

        } catch (\Exception $e) {
            Log::error('Data purchase exception: ' . $e->getMessage());
            return back()->with('error', 'Something went wrong. Please try again.');
        }
    }

    /**
     * Fetch Bundles by Service ID
     */
    public function fetchBundles(Request $request)
    {
        try {
            $bundles = DB::table('data_variations')
                ->select(['name', 'variation_code'])
                ->where('service_id', $request->id)
                ->where('status', 'enabled')
                ->get();

            return response()->json($bundles);
        } catch (\Exception $e) {
            Log::error('Fetch bundles error: ' . $e->getMessage());
            return response()->json([], 500);
        }
    }

    /**
     * Fetch Bundle Price
     */
    public function fetchBundlePrice(Request $request)
    {
        try {
            $price = DB::table('data_variations')
                ->where('variation_code', $request->id)
                ->value('variation_amount');

            return response()->json(number_format((float)$price, 2));
        } catch (\Exception $e) {
            Log::error('Fetch bundle price error: ' . $e->getMessage());
            return response()->json("0.00", 500);
        }
    }
}
