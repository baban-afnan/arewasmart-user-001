<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\WalletController;
use App\Http\Controllers\ServicesController;
use App\Http\Controllers\Action\AirtimeController;
use App\Http\Controllers\Action\DataController;
use App\Http\Controllers\Action\EducationalController;
use App\Http\Controllers\Agency\BvnServicesController;
use App\Http\Controllers\Agency\BvnModificationController;
use App\Http\Controllers\Agency\ManualSearchController;
use App\Http\Controllers\PaymentWebhookController;
use App\Http\Controllers\Agency\BvnUserController;
use App\Http\Controllers\Agency\NinModificationController;
use App\Http\Controllers\EnrolmentReportController;
use App\Http\Controllers\SupportController;
use App\Http\Controllers\Agency\NinValidationController;
use App\Http\Controllers\NINverificationController;
use App\Http\Controllers\BvnverificationController;



Route::get('/', function () {
    return view('welcome');
});

Route::post('/palmpay/webhook', [PaymentWebhookController::class, 'handleWebhook']) ->middleware('throttle:60,1');

// Webhooks (public, validate signature in controller)
// In routes/api.php
   
Route::post('/validations-webhook', [NinValidationController::class, 'webhook'])->name('validations.webhook');


Route::middleware(['auth'])->group(function () {

    Route::get('/dashboard', [DashboardController::class, 'index'])->middleware('verified')->name('dashboard');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::post('/update-required', [ProfileController::class, 'updateRequired'])->name('profile.updateRequired');
    
    // New Profile Routes
    Route::post('/profile/photo', [ProfileController::class, 'updatePhoto'])->name('profile.photo');
    Route::post('/profile/additional', [ProfileController::class, 'updateAdditionalInfo'])->name('profile.additional');
    Route::put('/password', [ProfileController::class, 'updatePassword'])->name('password.update');
    Route::post('/profile/pin', [ProfileController::class, 'updatePin'])->name('profile.pin');


    Route::prefix('wallet')->group(function () {
            Route::get('/', [WalletController::class, 'index'])->name('wallet');
            Route::post('/create-virtual-account', [WalletController::class, 'createWallet'])->name('virtual.account.create');
            Route::post('/claim-bonus', [WalletController::class, 'claimBonus'])->name('wallet.claimBonus');
        });


    /*
        |--------------------------------------------------------------------------
        | Services
        |--------------------------------------------------------------------------
        */
        Route::prefix('services')->group(function () {
            Route::get('/bvn', [ServicesController::class, 'bvnServices'])->name('bvn.services');
            Route::get('/vip', [ServicesController::class, 'vipServices'])->name('vip.services');
            Route::get('/nin', [ServicesController::class, 'ninServices'])->name('nin.services');
            Route::get('/verification', [ServicesController::class, 'verificationServices'])->name('verification.services');
            Route::get('/support', [ServicesController::class, 'supportServices'])->name('support.services');
            Route::get('/settings', [ServicesController::class, 'settingServices'])->name('settings.services');
            Route::get('/transaction-pin', [ServicesController::class, 'transactionPin'])->name('transaction.pin');
            Route::get('/airtime', [ServicesController::class, 'airtime'])->name('airtime.index');
        });
    

    
        /*
        |--------------------------------------------------------------------------
        | Utility bill payment (Airtime & Data)
        |--------------------------------------------------------------------------
        */

        Route::get('/airtime', [AirtimeController::class, 'airtime'])->name('airtime');
        Route::post('/buy-airtime', [AirtimeController::class, 'buyAirtime'])->name('buyairtime');


        Route::get('/data', [DataController::class, 'data'])->name('buy-data');
        Route::post('/buy-data', [DataController::class, 'buydata'])->name('buydata');
        Route::get('/fetch-data-bundles', [DataController::class, 'fetchBundles'])->name('fetch.bundles');
        Route::get('/fetch-data-bundles-price', [DataController::class, 'fetchBundlePrice'])->name('fetch.bundle.price');
        Route::post('/verify-pin', [DataController::class, 'verifyPin'])->name('verify.pin');


        Route::get('/sme-data', [DataController::class, 'sme_data'])->name('sme-data');
        Route::get('/fetch-data-type', [DataController::class, 'fetchDataType']);
        Route::get('/fetch-data-plan', [DataController::class, 'fetchDataPlan']);
        Route::get('/fetch-sme-data-bundles-price', [DataController::class, 'fetchSmeBundlePrice']);
        Route::post('/buy-sme-data', [DataController::class, 'buySMEdata'])->name('buy-sme-data');

        Route::get('/education', [EducationalController::class, 'pin'])->name("education");
        Route::post('/buy-pin', [EducationalController::class, 'buypin'])->name('buypin');
        Route::get('/education/receipt/{transaction}', [EducationalController::class, 'receipt'])->name('education.receipt');
        Route::get('/get-variation', [EducationalController::class, 'getVariation'])->name('get-variation');

        Route::get('/jamb', [EducationalController::class, 'jamb'])->name('jamb');
        Route::post('/verify-jamb', [EducationalController::class, 'verifyJamb'])->name('verify.jamb');
        Route::post('/buy-jamb', [EducationalController::class, 'buyJamb'])->name('buyjamb');

        Route::get('/electricity', [App\Http\Controllers\Action\ElectricityController::class, 'index'])->name('electricity');
        Route::post('/verify-electricity', [App\Http\Controllers\Action\ElectricityController::class, 'verifyMeter'])->name('verify.electricity');
        Route::post('/buy-electricity', [App\Http\Controllers\Action\ElectricityController::class, 'purchase'])->name('buy.electricity');

        Route::get('/cable', [App\Http\Controllers\Action\CableController::class, 'index'])->name('cable');
        Route::get('/cable/variations', [App\Http\Controllers\Action\CableController::class, 'getVariations'])->name('cable.variations');
        Route::post('/cable/verify', [App\Http\Controllers\Action\CableController::class, 'verifyIuc'])->name('verify.cable');
        Route::post('/cable/buy', [App\Http\Controllers\Action\CableController::class, 'purchase'])->name('buy.cable');

        Route::get('/transactions', [App\Http\Controllers\TransactionController::class, 'index'])->name('transactions');

        /*
        |--------------------------------------------------------------------------
        | Transfer to Smart User
        |--------------------------------------------------------------------------
        */
        Route::get('/transfer', [App\Http\Controllers\TransferController::class, 'index'])->name('transfer.index');
        Route::post('/transfer/verify', [App\Http\Controllers\TransferController::class, 'verifyUser'])->name('transfer.verify');
        Route::post('/transfer/process', [App\Http\Controllers\TransferController::class, 'processTransfer'])->name('transfer.process');

        Route::view('/thankyou', 'thankyou')->name('thankyou');


        /*
        |--------------------------------------------------------------------------
        | BVN Services & CRM
        |--------------------------------------------------------------------------
        */
        Route::get('/bvn-crm', [BvnServicesController::class, 'index'])->name('bvn-crm');
        Route::post('/bvn-crm', [BvnServicesController::class, 'store'])->name('crm.store');

        Route::get('/enrolment-report', [EnrolmentReportController::class, 'index'])->name('enrolment.report');

        Route::get('/send-vnin', [BvnServicesController::class, 'index'])->name('send-vnin');
        Route::post('/send-vnin', [BvnServicesController::class, 'store'])->name('send-vnin.store');

        Route::get('/modification-fields/{serviceId}', [BvnModificationController::class, 'getServiceFields'])->name('modification.fields');
        Route::get('/modification', [BvnModificationController::class, 'index'])->name('modification');
        Route::post('/modification', [BvnModificationController::class, 'store'])->name('modification.store');

        Route::get('/phone-search', [ManualSearchController::class, 'index'])->name('phone.search.index');
        Route::post('/phone-search', [ManualSearchController::class, 'store'])->name('phone.search.store');
        Route::get('/phone-search/{id}/details', [ManualSearchController::class, 'showDetails'])->name('phone.search.details');

        /*
        |--------------------------------------------------------------------------
        | Affidavit Services
        |--------------------------------------------------------------------------
        */
        Route::get('/affidavit', [App\Http\Controllers\Agency\AffidavitController::class, 'index'])->name('affidavit.index');
        Route::post('/affidavit', [App\Http\Controllers\Agency\AffidavitController::class, 'store'])->name('affidavit.store');

        /*
        |--------------------------------------------------------------------------
        | CAC Registration Services
        |--------------------------------------------------------------------------
        */
        Route::get('/cac-reg', [App\Http\Controllers\Agency\CacRegistrationController::class, 'index'])->name('cac.index');
        Route::post('/cac-reg', [App\Http\Controllers\Agency\CacRegistrationController::class, 'store'])->name('cac.store');

        /*
        |--------------------------------------------------------------------------
        | TIN Registration Services
        |--------------------------------------------------------------------------
        */
        Route::get('/tin-reg', [App\Http\Controllers\Agency\TinRegistrationController::class, 'index'])->name('cac.tin');
        Route::post('/tin-reg/validate', [App\Http\Controllers\Agency\TinRegistrationController::class, 'validateTin'])->name('tin.validate');
        Route::post('/tin-reg/download', [App\Http\Controllers\Agency\TinRegistrationController::class, 'downloadSlip'])->name('tin.download');

        Route::prefix('bvn')->group(function () {

        // BVN User route
        Route::get('/', [BvnUserController::class, 'index'])->name('bvn.index');
        Route::post('/store', [BvnUserController::class, 'store'])->name('bvn.store');


        // nin Modification Routes
        Route::prefix('nin-modification')->group(function () {
            Route::get('/', [NinModificationController::class, 'index'])->name('nin-modification');
            Route::post('/', [NinModificationController::class, 'store'])->name('nin-modification.store');
        });

        // NIN Validation & IPE Routes
        Route::get('/nin-validation', [NinValidationController::class, 'index'])->name('nin-validation');
        Route::post('/nin-validation', [NinValidationController::class, 'store'])->name('nin-validation.store');
        Route::get('/nin-validation/check/{id}', [NinValidationController::class, 'checkStatus'])->name('nin-validation.check');
    
        // Support Routes
        Route::prefix('support')->group(function () {
            Route::get('/', [SupportController::class, 'index'])->name('support.index');
            Route::get('/create', [SupportController::class, 'create'])->name('support.create');
            Route::post('/store', [SupportController::class, 'store'])->name('support.store');
            Route::get('/{ticket}', [SupportController::class, 'show'])->name('support.show');
            Route::post('/{ticket}/reply', [SupportController::class, 'reply'])->name('support.reply');
            Route::get('/{ticket}/updates', [SupportController::class, 'fetchUpdates'])->name('support.updates');
        });


        /*
        |--------------------------------------------------------------------------
        | NIN Verification
        |--------------------------------------------------------------------------
        */
        Route::prefix('nin-verification')->group(function () {
            Route::get('/', [NINverificationController::class, 'index'])->name('nin.verification.index');
            Route::post('/', [NINverificationController::class, 'store'])->name('nin.verification.store');
            Route::post('/{id}/status', [NINverificationController::class, 'updateStatus'])->name('nin.verification.status');
            Route::get('/standardSlip/{id}', [NINverificationController::class, 'standardSlip'])->name('standardSlip');
            Route::get('/premiumSlip/{id}', [NINverificationController::class, 'premiumSlip'])->name('premiumSlip');
            Route::get('/vninSlip/{id}', [NINverificationController::class, 'vninSlip'])->name('vninSlip');
        });

        /*
        |--------------------------------------------------------------------------
        | BVN Verification
        |--------------------------------------------------------------------------
        */
        Route::prefix('bvn-verification')->group(function () {
            Route::get('/', [BvnverificationController::class, 'index'])->name('bvn.verification.index');
            Route::post('/', [BvnverificationController::class, 'store'])->name('bvn.verification.store');
        Route::get('/standardBVN/{id}', [BvnverificationController::class, 'standardBVN'])->name("standardBVN");
        Route::get('/premiumBVN/{id}', [BvnverificationController::class, 'premiumBVN'])->name("premiumBVN");
        Route::get('/plasticBVN/{id}', [BvnverificationController::class, 'plasticBVN'])->name("plasticBVN");

        });


    });


        /*
        |--------------------------------------------------------------------------
        | API Dashboard & Applications
        |--------------------------------------------------------------------------
        */
        Route::prefix('api-dashboard')->group(function () {
             Route::get('/', [\App\Http\Controllers\Api\ApiDashboardController::class, 'index'])->name('api.dashboard');
             Route::post('/apply', [\App\Http\Controllers\Api\ApiDashboardController::class, 'apply'])->name('api.apply');
        });

});
 

require __DIR__.'/auth.php';
