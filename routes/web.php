<?php

use App\Helpers\CommonHelper;
use App\Http\Controllers\Admin\ActiveUserController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\UsersController;
use App\Http\Controllers\Resellers;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\UserServiceController;
use App\Http\Controllers\PayoutController;
use App\Http\Controllers\BulkPayoutController;
use App\Http\Controllers\AEPSController;
use App\Http\Controllers\RechargeController;
use App\Http\Controllers\UpiController;
use App\Http\Controllers\CommonController;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\AutoCollectApisController;
use App\Http\Controllers\Admin\AutoSettlementController as AdminAutoSettlementController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\DynamicBillingController;
use App\Http\Controllers\Admin\GlobalBillingController;
use App\Http\Controllers\Admin\DisputedTxnController;
use App\Http\Controllers\Admin\PartnersVanController;
use App\Http\Controllers\Admin\SmartCollectController;
use App\Http\Controllers\Admin\UserBankListController;
use App\Http\Controllers\Admin\AdminCommonController;
use App\Http\Controllers\Admin\ManualSettlementController;
use App\Http\Controllers\Admin\ResellerController;
use App\Http\Controllers\Admin\RechargeBackController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Api\v1\CallbackController;
use App\Http\Controllers\Api\v1\Callbacks\AEPSCallbackController;
use App\Http\Controllers\Api\v1\Callbacks\FundCallbackController;
use App\Http\Controllers\Api\v1\Callbacks\RazorPayCallbackController;
use App\Http\Controllers\Api\v1\Callbacks\IblCallbackController;
use App\Http\Controllers\Api\v1\EasebuzzVanController;
use App\Http\Controllers\Api\v1\PayoutCallbackController;
use App\Http\Controllers\Api\v1\RazPayVanController;
use App\Http\Controllers\AutoCollectController;
use App\Http\Controllers\BulkUpiCreditCtrl;
use App\Http\Controllers\Admin\IntegrationController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\VerifyEmailController;
use App\Http\Controllers\SystemEmailController;
use App\Http\Controllers\Admin\MessageController;
use App\Http\Controllers\Admin\PayoutController as AdminPayoutController;
use App\Http\Controllers\Admin\ReconcileController;
use App\Http\Controllers\Api\v1\BulkPayoutController as V1BulkPayoutController;
use App\Http\Controllers\Api\v1\FilterPayoutController;
use App\Http\Controllers\Api\v1\UserController;
use App\Http\Controllers\AutoSettlementController;
use App\Http\Controllers\GraphController;
use App\Http\Controllers\Admin\TestCommonController;
use App\Http\Controllers\Admin\RechargeController as AdminRechargeController;
use App\Http\Controllers\Admin\AdminOCRController;
use App\Http\Controllers\Admin\AdminDMTController;
use App\Http\Controllers\Admin\AdminPANController;
use App\Http\Controllers\Admin\AdminInsuranceController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Api\v1\Callbacks\BankopenCallbackController;
use App\Http\Controllers\Api\v1\Callbacks\RechargeCallbackController;
use App\Http\Controllers\Api\v1\OBVanController;
use App\Http\Controllers\UserReportController;
use App\Http\Controllers\ValidationSuiteController;
use App\Http\Controllers\VirtualAccountTpvController;
use App\Http\Controllers\Admin\OfferController;
use App\Http\Controllers\Api\v1\Callbacks\PANCallbackController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

/*Route::domain(env('APP_API_DOMAIN'))->group(function() {
    if (env('APP_ENV') == 'production') {
        Route::get('/', function() {
            return redirect()->away(env('APP_USERAPP_URL'));
        });
    }
});*/

Route::get('/clearall', function () {
    $exitCode = Artisan::call('config:clear');
    $exitCode = Artisan::call('cache:clear');
    $exitCode = Artisan::call('config:cache');
    return 'DONE';
});

// Route::get('/recharge-circles', [RechargeController::class, 'getRechargeCircles']);
// Route::get('/recharge-operators', [RechargeController::class, 'getRechargeOperators']);

Auth::routes(['verify' => true]);

Route::get('/', function () {
    return redirect('login');
});

Route::get('/home', function () {
    return redirect('user/dashboard');
});

Route::post('sendOtp', [LoginController::class, 'send_mobile_otp']);
Route::post('verifyotp',    [LoginController::class, 'verifyotpmobile']);
Route::post('resendotp/{id}',    [LoginController::class, 'resendotp']);
Route::post('signUp', [RegisterController::class, 'signUp']);

Route::prefix('reseller')->group(function () {
    Route::get('dashboard', [Resellers::class, 'index']);
    Route::get('transactions', [Resellers::class, 'allTransaction']);
    Route::get('list', [Resellers::class, 'resellerList']);
    Route::get('upicollect', [Resellers::class, 'UpiCollects']);
    Route::get('payout', [Resellers::class, 'Payout']);
    Route::post('fetch/{type}/{id?}/{returntype?}', [CommonController::class, 'fetchData']);
    Route::get('allreports/excel/download', [Resellers::class, 'excelDownload']);
    Route::get('allreports/excelDownloadLink/{id}', [Resellers::class, 'excelDownloadLink']);
    Route::post('removeExportFile/{id}', [Resellers::class, 'removeExportFile']);
    Route::post('allreports/ajaxGenerateExcelFile', [Resellers::class, 'ajaxGenerateExcelFile']);
    Route::get('allreports/getCountRecord', [Resellers::class, 'getCountRecord']);
    Route::get('dashboard/balances', [Resellers::class, 'dashboardBalances']);
    Route::get('reports/{service}', [Resellers::class, 'transactionReport']);
    Route::post('reports/{service}', [Resellers::class, 'transactionReport']);
    Route::post('fetch-reports/{service}', [Resellers::class, 'totalAmountReportsAll']);
    Route::post('dashboard/active-user/txn-details', [Resellers::class, 'getActiveTxnDetail']);
    Route::post('data/{service}/{type?}',[Resellers::class,'getRecords']);
});

Route::get('/email/verify/{id}/{hash}', [VerifyEmailController::class, '__invoke'])
    ->middleware(['signed', 'throttle:6,1'])
    ->name('verification.verify');


Route::get('email/verification-notification/{email}', [VerifyEmailController::class, 'resendEmail'])->middleware(['throttle:6,1'])->name('verification.send');
Route::get('getbusiness_sub_category/{id}', [UsersController::class, 'getBusinessSubCategory']);
Route::get('getbusiness_sub_category_by_user_id/{id}/{userId}', [UsersController::class, 'getBusinessSubCategoryByUserId']);

Route::group(['middleware' => ['auth', 'IsUser']], function () {
    Route::prefix('user')->group(function () {

        Route::get('dth-recharge', [RechargeController::class,'dthrecharge']);
        Route::get('lic-recharge', [RechargeController::class,'licrecharge']);
        Route::get('electricity-recharge', [RechargeController::class,'electricityrecharge']);
        Route::get('creditcard-recharge', [RechargeController::class,'creditcardrecharge']);
        Route::get('postpaid-recharge', [RechargeController::class,'postpaidrecharge']);
        Route::get('data-recharge', [RechargeController::class, 'datarecharge']);

        
        Route::get('/recharge-circles', [RechargeController::class, 'getRechargeCircles']);
        Route::get('/recharge-operators', [RechargeController::class, 'getRechargeOperators']);

        Route::get('/filterutr/{id}', [HomeController::class, 'filterutr']);
        Route::get('dashboard', [HomeController::class, 'index']);
        Route::get('/sendOrderDataTotEmail', [HomeController::class, 'sendOrderDataTotEmail']);
        Route::get('profile', [UsersController::class, 'myProfile']);
        Route::get('profile/{slug}', [UsersController::class, 'profileUpdate']);
        Route::get('apikeys', [UsersController::class, 'apikeys'])->name('apikeys.index');
        Route::get('iplist', [UsersController::class, 'iplist'])->name('iplist.index');
        Route::get('profile/{slug}', [UsersController::class, 'profileUpdate']);
        Route::get('myservices', [UserServiceController::class, 'index'])->name('myservices.index');
        Route::get('trans/fundTransfer', [UsersController::class, 'fundTransfer']);
        Route::get('getWebhook', [UsersController::class, 'getWebhook'])->name('getWebhook.index');
        Route::get('transaction/list', [UsersController::class, 'transactionList']);
        Route::get('transactions', [UsersController::class, 'mytransactions']);
        Route::get('van-callbacks', [UsersController::class, 'vanCallbacks']);
        Route::post('fetch/{type}/{id?}/{returntype?}', [CommonController::class, 'fetchData']);
        Route::get('allreports/excelDownloadLink/{id}', [UserReportController::class, 'excelDownloadLink']);
        Route::post('allreports/ajaxGenerateExcelFile', [UserReportController::class, 'ajaxGenerateExcelFile']);
        Route::get('allreports/getCountRecord', [UserReportController::class, 'getCountRecord']);
        Route::get('allreports/excel/download', [UserReportController::class, 'excelDownload']);
        Route::post('allreports/{service}', [UserReportController::class, 'transactionReport']);
        Route::post('removeExportFile/{id}', [UserReportController::class, 'removeExportFile']);

        Route::get('load-money-request', [UsersController::class, 'loadMoney']);
        Route::post('ajax/load-money-request', [UsersController::class, 'loadMoneyRequest']);

        Route::get('auto-settlements', [AutoSettlementController::class, 'index']);

        Route::prefix('accounts')->group(function () {
            Route::post('add-ip', [UserController::class, 'addIp']);
            Route::post('ip-delete/{id}', [UserController::class, 'ipDelete'])->whereNumber('id');
            Route::post('profile-change-password', [UserController::class, 'profileChangePassword']);
            Route::post('profile-update', [UserController::class, 'updateProfile']);
            Route::post('update-bank-details', [UserController::class, 'updateBankDetails']);
            Route::post('api-key-generate', [UserController::class, 'apikeyGenerate']);
            Route::post('sdk-api-key', [UserController::class, 'sdkApiKey']);
            Route::post('matm-sdk-api-key', [UserController::class, 'matmSdkApiKey']);
            Route::post('webhook-update', [UserController::class, 'webhookUpdate']);
            Route::post('service-activate', [UserController::class, 'serviceActivate']);
            Route::post('business-profile-update', [UserController::class, 'businessProfileUpdate']);
            Route::post('transfer-amount', [UserController::class, 'transferAmount'])->middleware('throttle:1,1');
        });

    });

    Route::prefix('payout')->group(function () {
        Route::get('/', [PayoutController::class, 'index'])->middleware('is_service_active:' . PAYOUT_SERVICE_ID);
        Route::get('contacts', [ContactController::class, 'index'])->name('contacts.index')->middleware('is_service_active:' . PAYOUT_SERVICE_ID);
        Route::get('orders', [OrderController::class, 'index'])->name('orders.index')->middleware('is_service_active:' . PAYOUT_SERVICE_ID);
        Route::post('/order/cancelled',  [OrderController::class, 'orderCancel']);
        Route::post('contacts', [ContactController::class, 'store'])->name('contacts.store')->middleware('is_service_active:' . PAYOUT_SERVICE_ID);
        Route::post('orders', [OrderController::class, 'store'])->name('orders.store')->middleware('is_service_active:' . PAYOUT_SERVICE_ID);
        Route::get('bulk', [BulkPayoutController::class, 'index'])->name('bulk.index')->middleware('is_service_active:' . PAYOUT_SERVICE_ID);
        Route::get('bulkExport/{batch_id}', [BulkPayoutController::class, 'exportBulkPayout'])->middleware('is_service_active:' . PAYOUT_SERVICE_ID);
        Route::get('bulkPayout/approveOtp/{id}', [BulkPayoutController::class, 'bulkPayoutApproveOtp'])->middleware('is_service_active:' . PAYOUT_SERVICE_ID);
        Route::post('verifyotpforbulkpayout', [BulkPayoutController::class, 'verifyOtpForBulkPayout'])->middleware('is_service_active:' . PAYOUT_SERVICE_ID);
        Route::post('batch/cancelled', [BulkPayoutController::class, 'batchCancel'])->middleware('is_service_active:' . PAYOUT_SERVICE_ID);
        Route::post('resendOtpOrderApprove/{id}', [BulkPayoutController::class, 'resendOtpOrderApprove'])->middleware('is_service_active:' . PAYOUT_SERVICE_ID);
        Route::post('fetch/{type}/{id?}/{returntype?}', [CommonController::class, 'fetchData'])->middleware('is_service_active:' . PAYOUT_SERVICE_ID);
        Route::post('inward-outwars', [FilterPayoutController::class, 'inwardOutward'])->middleware('is_service_active:' . PAYOUT_SERVICE_ID);
        Route::post('import-batch-file', [V1BulkPayoutController::class, 'bulkImport']);
        // Route::post('order-history-chart', [FilterPayoutController::class, 'orderHistoryChart'])->middleware('is_service_active:' . PAYOUT_SERVICE_ID);
    });

    Route::prefix('upi')->group(function () {
        Route::get('/', [UpiController::class, 'index'])->middleware('is_service_active:' . UPI_SERVICE_ID);
        Route::get('dashboard', [UpiController::class, 'dashboard'])->middleware('is_service_active:' . UPI_SERVICE_ID);
        Route::get('merchants', [UpiController::class, 'merchants'])->middleware('is_service_active:' . UPI_SERVICE_ID);
        Route::get('upicallbacks', [UpiController::class, 'upicallback'])->middleware('is_service_active:' . UPI_SERVICE_ID);
        Route::get('upicollects', [UpiController::class, 'upiCollect'])->middleware('is_service_active:' . UPI_SERVICE_ID);
        Route::post('fetch/{type}/{id?}/{returntype?}', [CommonController::class, 'fetchData'])->middleware('is_service_active:' . UPI_SERVICE_ID);
        Route::post('addMerchant', [UpiController::class, 'addMerchant'])->middleware('is_service_active:' . UPI_SERVICE_ID);
        Route::get('dashboard-chart/{type}', [UpiController::class, 'dashboardChart']);
        Route::post('upi-collection', [UpiCollectionController::class, 'upicollection']);
    });


    Route::prefix('va')->group(function () {
        Route::get('/', [VirtualAccountTpvController::class, 'index'])->middleware('is_service_active:' . VA_SERVICE_ID);
        Route::get('clients', [VirtualAccountTpvController::class, 'merchants'])->middleware('is_service_active:' . VA_SERVICE_ID);
        Route::get('payments', [VirtualAccountTpvController::class, 'upicallback'])->middleware('is_service_active:' . VA_SERVICE_ID);
        Route::post('fetch/{type}/{id?}/{returntype?}', [CommonController::class, 'fetchData'])->middleware('is_service_active:' . VA_SERVICE_ID);
    });

    Route::prefix('verification')->group(function () {
        Route::get('/', [ValidationSuiteController::class, 'index'])->middleware('is_service_active:' . VALIDATE_SERVICE_ID);
        Route::get('transactions', [ValidationSuiteController::class, 'upicallback'])->middleware('is_service_active:' . VALIDATE_SERVICE_ID);
        Route::post('fetch/{type}/{id?}/{returntype?}', [CommonController::class, 'fetchData'])->middleware('is_service_active:' . VALIDATE_SERVICE_ID);
    });


    Route::prefix('collect')->group(function () {
        Route::get('/', [AutoCollectController::class, 'index'])->middleware('is_service_active:' . AUTO_COLLECT_SERVICE_ID);
        Route::get('merchants', [AutoCollectController::class, 'merchants'])->middleware('is_service_active:' . AUTO_COLLECT_SERVICE_ID);
        Route::get('payments', [AutoCollectController::class, 'callbacks'])->middleware('is_service_active:' . AUTO_COLLECT_SERVICE_ID);
        Route::post('fetch/{type}/{id?}/{returntype?}', [CommonController::class, 'fetchData'])->middleware('is_service_active:' . AUTO_COLLECT_SERVICE_ID);
    });


    Route::prefix('aeps')->group(function () {
        Route::get('/', [AEPSController::class, 'index'])->middleware('is_service_active:' . AEPS_SERVICE_ID);
        Route::get('merchants', [AEPSController::class, 'aepsmerchants'])->middleware('is_service_active:' . AEPS_SERVICE_ID);
        Route::get('transactions', [AEPSController::class, 'transaction'])->middleware('is_service_active:' . AEPS_SERVICE_ID);
        Route::get('settlement', [AEPSController::class, 'settlement'])->middleware('is_service_active:' . AEPS_SERVICE_ID);
        Route::post('fetch/{type}/{id?}/{returntype?}', [CommonController::class, 'fetchData'])->middleware('is_service_active:' . AEPS_SERVICE_ID);
        Route::post('card-data', [AEPSController::class, 'dashboardCardData'])->middleware('is_service_active:' . AEPS_SERVICE_ID);
    });
    
    Route::prefix('recharge')->group(function () {
        Route::get('/', [RechargeController::class, 'index'])->middleware('is_service_active:' . RECHARGE_SERVICE_ID);
        Route::get('merchants', [RechargeController::class, 'aepsmerchants'])->middleware('is_service_active:' . RECHARGE_SERVICE_ID);
        Route::get('transactions', [RechargeController::class, 'transaction'])->middleware('is_service_active:' . RECHARGE_SERVICE_ID);
        Route::get('settlement', [RechargeController::class, 'settlement'])->middleware('is_service_active:' . RECHARGE_SERVICE_ID);
        Route::post('fetch/{type}/{id?}/{returntype?}', [CommonController::class, 'fetchData'])->middleware('is_service_active:' . RECHARGE_SERVICE_ID);
        Route::post('card-data', [RechargeController::class, 'dashboardCardData'])->middleware('is_service_active:' . RECHARGE_SERVICE_ID);
    });
    
    Route::prefix('recharge-data')->group(function () {
        Route::get('/', [RechargeController::class, 'rechargeindex']);
    });

    Route::prefix('recharge-back')->group(function () {
        Route::post('fetch/{type}/{id?}/{returntype?}', [CommonController::class, 'fetchData'])->middleware('is_service_active:' . AEPS_SERVICE_ID);
    });
    
});
Route::get('exportBulkPayoutByDate/{from}/{to}', [BulkPayoutController::class, 'exportBulkPayoutByDate']);
Route::post('getIndentifiers', [UsersController::class, 'getIndentifiers']);
Route::prefix('admin')->group(function () {
    Route::group(['middleware' => ['auth']], function () {
        // Call Commond
        Route::get('clearQueuedOrder', function () {
            Artisan::call('queued_order:update');
            return 'Queued order failed successfully.';
        })->middleware('throttle:1,4');
        Route::get('updateProcessingOrder', function (Request $request) {
            Artisan::call('processing_order:update');
            return 'Please wait few minutes.';
        })->middleware('throttle:1,5');

        Route::get('processingAutoSettlementOrderUpdate', function () {
            Artisan::call('settlement_processing_order:update');
            return 'Please wait few minutes.';
        })->middleware('throttle:1,5');
        
       
        Route::get('processingDMTOrderUpdate', function () {
            Artisan::call('dmt_transaction_status:update');
            return 'Please wait few minutes.';
        })->middleware('throttle:1,5');

        Route::prefix('accounts')->group(function () {
            Route::post('profile-change-password', [UserController::class, 'profileChangePassword']);
            Route::post('profile-update', [UserController::class, 'updateProfile']);
            Route::post('business-profile-update', [UserController::class, 'businessProfileUpdate']);
            Route::post('update-bank-details', [UserController::class, 'updateBankDetails']);
            Route::post('admin-transfer-amount', [UserController::class, 'adminTransferAmount'])->middleware('throttle:1,1');
            Route::post('sdk/status', [UserController::class, 'sdkStatus']);
            Route::post('matm/status', [UserController::class, 'matmStatus']);
            Route::post('autoSettlement/status', [UserController::class, 'autoSettlementStatus']);
            Route::post('load_money_request/status', [UserController::class, 'loadMoneyStatus']);
            Route::post('internal-transfer/status', [UserController::class, 'internalTransferStatus']);
            Route::post('claimback', [UserController::class, 'claimback'])->middleware('throttle:1,1');
            Route::post('threshold', [UserController::class, 'threshold'])->middleware('throttle:1,1');
            Route::post('sendKYCAttachment', [UserController::class, 'sendKYCAttachment']);
        });

        // End
        //ADMIN TESTING URL
        Route::post('test/{type}/{id?}/{returntype?}', [TestCommonController::class, 'fetchData']);
        Route::get('orders-list/{id}', [AdminPayoutController::class, 'order']);
        // END TEST
        
        Route::get('/dashboard', [AdminController::class, 'index']);
        Route::get('/filterutr/{id}', [AdminController::class, 'filterutr']);
        Route::get('dashboard/balances', [AdminController::class, 'dashboardBalances']);
        Route::post('data/{service}/{type?}',[DashboardController::class,'getRecords']);
        Route::post('dashboard/active-user', [ActiveUserController::class, 'getActiveTxnByUser']);
        Route::post('dashboard/latest-user', [ActiveUserController::class, 'getLatestUserSignup']);
        Route::post('dashboard/active-user/txn-details', [ActiveUserController::class, 'getActiveTxnDetail']);
        Route::get('/getBusinessInfo/{id}', [AdminController::class, 'getBusinessInfo']);
        Route::get('orders', [AdminController::class, 'ordersList']);
        Route::get('bulk', [AdminController::class, 'bulkPayoutList']);
        Route::get('contacts', [AdminController::class, 'contactList']);
        Route::post('contacts/add', [ContactController::class, 'store']);
        Route::get('getContactByUserId/{id}', [ContactController::class, 'getContactByUserId']);
        Route::post('orders/add', [OrderController::class, 'addOrder'])->middleware('throttle:1,1');;
        Route::get('transactions', [AdminController::class, 'allTransaction']);
        Route::get('serviceRequest', [AdminController::class, 'serviceActivationReq']);
        Route::get('serviceActivate/{id}', [AdminController::class, 'serviceActivate']);
        Route::get('web-service-activate/{id}', [AdminController::class, 'webServiceActivate']);
        Route::get('api-service-activate/{id}', [AdminController::class, 'apiServiceActivate']);
        Route::get('upiMerchant', [AdminController::class, 'upiMerchant']);
        Route::get('upiCallback', [AdminController::class, 'upiCallback']);
        Route::get('smart-collcet/merchants', [AdminController::class, 'scMerchants']);
        Route::get('smart-collect/callbacks', [AdminController::class, 'scCallbacks']);
        Route::get('va/clients', [AdminController::class, 'vaClients']);
        Route::get('va/callbacks', [AdminController::class, 'vaCallbacks']);
        Route::get('validation-suite/transactions', [AdminController::class, 'validationSuiteTransactions']);
        Route::get('van-callback', [AdminController::class, 'vanCallback']);
        Route::get('userprofile/{id}',[AdminController::class,'userProfiles']);
        Route::get('profile', [AdminController::class, 'profile']);
        Route::get('profileOpenByAdmin/{userId}', [AdminController::class, 'profileOpenBySupport']);
        Route::get('bulkExport/{batch_id}', [BulkPayoutController::class, 'exportBulkPayout']);
        Route::get('userprofile/status/{id}', [AdminController::class, 'userprofile']);
        Route::post('fetchReport/{id?}/{returntype?}', [CommonController::class, 'fetchReportData']);
        Route::post('fetch-reports/{service}', [AdminController::class, 'totalAmountReportsAll']);
        Route::post('fetch/{type}/{id?}/{returntype?}', [CommonController::class, 'fetchData']);
        Route::get('users', [AdminController::class, 'userList']);
        Route::get('users/video-kyc', [AdminController::class, 'userVideoKycList']);
        Route::post('users/video-kyc', [AdminController::class, 'updateVideoKycStatus']);
        Route::get('users/{type}/{userId?}', [AdminController::class, 'getList']);
        Route::get('user/{id}', [AdminController::class, 'userprofile']);
        Route::get('user/userdetails/{id}', [AdminController::class, 'userDetails']);
        Route::get('reports/{service}', [AdminController::class, 'transactionReport']);
        Route::post('reports/{service}', [AdminController::class, 'transactionReport']);
        Route::post('deductUpiFee',[AdminController::class,'upiCallbackAmount']);
        Route::post('/order/reversed',  [AdminController::class, 'orderReversed']);
        Route::post('update-lean-amount', [AdminController::class, 'updateLeanAmount']);
        Route::post('fetch-lean-amount', [AdminCommonController::class, 'fetchLeanAmount']);
        Route::post('update-reseller/{id}', [AdminController::class, 'updateReseller']);

        Route::post('upiTotalAmount',[AdminController::class,'getUpiAmount']);
        Route::post('serviceValueUpdate',[AdminController::class,'serviceValueUpdate']); 
        Route::get('update-van-info', [AutoCollectController::class, 'editVanEmailPhone']);
        Route::get('status-van-cf/{vanStatus}/{offset}/{limit}', [AutoCollectController::class, 'inactiveVanCfMerchants'])->whereNumber(['vanStatus','offset','limit']);
        Route::get('status-partner-van/{vanStatus}/{offset}/{limit}', [AutoCollectController::class, 'inactivePartnerVanMerchants'])->whereNumber(['vanStatus','offset','limit']);
        // Route::get('status-upi-sc/{userId}/{vanStatus}/{offset}/{limit}', [AutoCollectController::class, 'updateAccountStatusMerchants'])->whereNumber(['userId','vanStatus','offset','limit']);

        Route::get('auto-settlements', [AdminAutoSettlementController::class, 'index']);
        Route::post('createSettlement', [AdminAutoSettlementController::class, 'newSettlementLog']);
        Route::get('settlementStatus/{id}/{stmlrefid}', [AdminAutoSettlementController::class, 'settlementStatus']);
        Route::get('webhook-logs', [AdminController::class, 'viewWebhookLogs']);
        Route::post('webhook-logs', [AdminController::class, 'getWebhookLogs']);
        Route::get('user-api-logs', [AdminController::class, 'viewUserApiLogs']);
        Route::post('user-api-logs', [AdminController::class, 'getUserApiLogs']);

        Route::get('api-logs', [AdminController::class, 'viewApiLogs']);
        Route::post('api-logs', [AdminController::class, 'getApiLogs']);

        Route::get('aeps-transactions', [AdminController::class, 'viewAepsTransactions']);
        Route::post('aeps-transactions', [AdminController::class, 'getAepsTransactions']);
        Route::get('aeps-transactions-details/{id}', [AdminController::class, 'viewAepsTransactionDetails']);
        Route::post('aeps-transactions-details', [AdminController::class, 'getAepsTransactionsDetails']);
        
        // Reconcile Start
        Route::get('reconcile', [ReconcileController::class, 'index']);
        Route::post('reconcileReport', [ReconcileController::class, 'reconcileReport']);
        // Reconcile End
        Route::prefix('users-bank')->group(function () {
            Route::get('/', [UserBankListController::class, 'bankListView']);
            Route::get('/{id}', [UserBankListController::class, 'bankListView']);
            Route::post('add-new-banks', [UserBankListController::class, 'addNewBanks']);
            Route::post('get-bank', [UserBankListController::class, 'getUserBank']);
            Route::post('update-banks-info', [UserBankListController::class, 'updateBanksInfo']);
            Route::post('update-bank-status', [UserBankListController::class, 'updateBankStatus']);
            Route::post('update-primary-status', [UserBankListController::class, 'updatePrimaryStatus']);
            Route::post('verify-bank-account', [UserBankListController::class, 'verifyBankAccount']);
            Route::post('verify-bank-account/final', [UserBankListController::class, 'verifyBankAccountApprove']);
            Route::post('delete/{action}', [UserBankListController::class, 'deleteActions']);
            Route::post('report/{service}/{userId?}', [UserBankListController::class, 'reportsAll']);
        });

        Route::prefix('dispute-transactions')->group(function () {
            Route::get('upi-stack', [DisputedTxnController::class, 'upiStack']);
            Route::get('orders', [DisputedTxnController::class, 'smartPayout']);
            Route::post('upi-stack/submit', [DisputedTxnController::class, 'upiStackSubmit']);
            Route::post('upi-stack/final-submit', [DisputedTxnController::class, 'upiStackFinalSubmit']);

            // AEPS
            Route::get('aeps-txn', [DisputedTxnController::class, 'aepsTXN']);
            Route::post('aeps-fetch/submit', [DisputedTxnController::class, 'aepsTXNSubmit']);
            Route::post('aeps-fetch/final-submit', [DisputedTxnController::class, 'aepsFinalSubmit']);
            
            Route::get('smart-collect', [DisputedTxnController::class, 'smartCollect']);
            Route::post('smart-collect/submit', [DisputedTxnController::class, 'smartCollectSubmit']);
            Route::post('smart-collect/final-submit', [DisputedTxnController::class, 'smartCollectFinalSubmit']);

            Route::post('report/{service}', [DisputedTxnController::class, 'totalAmountReportsAll']);
        });


        Route::prefix('partners-van')->group(function () {
            Route::get('ebuz-list', [PartnersVanController::class, 'ebuzzVanList']);
            Route::post('ebuz-list/upload-kyc-doc', [PartnersVanController::class, 'uploadEbuzzVanKycDocs']);
            Route::get('edit-info', [PartnersVanController::class, 'editInfo']);
            Route::get('get-info/{bizzId}', [PartnersVanController::class, 'getBizzInfo'])->whereNumber('bizzId');
            Route::post('edit-info/submit', [PartnersVanController::class, 'editInfoSubmit']);
            Route::post('report/{service}', [PartnersVanController::class, 'reportsAll']);
        });

        Route::prefix('smart-collect-van')->group(function () {
            Route::get('edit-info', [SmartCollectController::class, 'editInfo']);
            Route::get('get-info/{bizzId}', [SmartCollectController::class, 'getBizzInfo'])->whereNumber('bizzId');
            Route::post('edit-info/submit', [SmartCollectController::class, 'editInfoSubmit']);
            Route::post('report/{service}', [SmartCollectController::class, 'reportsAll']);
        });
        // User Status Change
        Route::post('users/statusChange', [AdminController::class, 'userStatusChange']);
        Route::get('load-money-request', [AdminController::class, 'loadMoneyList']);
        Route::post('load-money-request/{action}', [AdminController::class, 'loadMoneyListUpdate']);
        Route::get('adminlist',[AdminController::class,'adminList']);
        Route::get('roles',[AdminController::class,'roles']);
        Route::post('roles/changeStatus',[AdminController::class,'changeStatusRole']);
        Route::post('roles/add',[AdminController::class,'addRole']);
        Route::get('user/userpermission/{id}',[AdminController::class,'userPermission']);
        Route::post('user/adduserpermission/{id}',[AdminController::class,'addUserPermission']);
        Route::get('roles/userList/{id}',[AdminController::class,'roleUserList']);
        Route::get('upi/credit-by-userid/{userId}', [BulkUpiCreditCtrl::class, 'index']);
        // Route::get('upi/credit-by-utr/{utr}', [BulkUpiCreditCtrl::class, 'upiCreditUtr']);

        Route::get('allreports', [ReportController::class, 'index']);
        Route::get('allreports/excelDownloadLink/{id}', [ReportController::class, 'excelDownloadLink']);
        Route::post('allreports/ajaxGenerateExcelFile', [ReportController::class, 'ajaxGenerateExcelFile']);
        Route::get('allreports/getCountRecord', [ReportController::class, 'getCountRecord']);
        Route::get('allreports/excel/download', [ReportController::class, 'excelDownload'])->name('allreports/excel/download');
        Route::post('allreports/{service}', [ReportController::class, 'transactionReport']);
        Route::post('removeExportFile/{id}', [ReportController::class, 'removeExportFile']);

        Route::prefix('global-billing')->group(function () {
            Route::get('rules', [GlobalBillingController::class, 'index']);
            Route::post('update-rules', [GlobalBillingController::class, 'editFeesAndRules']);
            Route::post('service/add', [GlobalBillingController::class, 'addNewService']);
            Route::post('service/update', [GlobalBillingController::class, 'updateService']);
            Route::post('products', [GlobalBillingController::class, 'updateProductList']);
            Route::post('product-fee', [GlobalBillingController::class, 'updateProductFeeList']);
            Route::post('products/fetch-list', [GlobalBillingController::class, 'productList']);
            Route::post('products/fetch-fee-list', [GlobalBillingController::class, 'productFeeList']);
            Route::get('status/{action}/{id}', [GlobalBillingController::class, 'statusActions'])->whereNumber('id');
            Route::post('data-table/{service}/{id?}/{returntype?}', [GlobalBillingController::class, 'datatableReports']);
        });

        Route::prefix('custom-billing')->group(function () {
            Route::get('rules', [DynamicBillingController::class, 'manageSchemesAndRules']);
            Route::post('scheme-rule/add-schemes', [DynamicBillingController::class, 'addNewSchemesAndRules']);
            Route::post('scheme-rule/edit-schemes', [DynamicBillingController::class, 'editSchemesAndRules']);
            Route::post('scheme-rule/fetch-list', [DynamicBillingController::class, 'listSchemesAndRules']);
            Route::post('assign-scheme', [DynamicBillingController::class, 'assignScheme2User']);
            Route::post('delete/{action}', [DynamicBillingController::class, 'deleteActions']);
            Route::post('status/{action}', [DynamicBillingController::class, 'statusActions']);
            Route::post('data-table/{service}/{id?}/{returntype?}', [DynamicBillingController::class, 'datatableReports']);
        });

        Route::prefix('cafee')->group(function(){
            Route::get('search-by-utr/{utr}', [AutoCollectApisController::class, 'searchByUTR']);
            Route::get('search-by-id/{refId}', [AutoCollectApisController::class, 'searchByRefId']);
            Route::get('search-by-acc/{accId}', [AutoCollectApisController::class, 'searchByAccId']);
            Route::get('recent-payments', [AutoCollectApisController::class, 'recentPayments']);
        });

        Route::prefix('messages')->group(function(){
            Route::get('list',[MessageController::class,'getList']);
            Route::post('addMessage',[MessageController::class,'addMessage']);
            Route::post('sendEmail',[MessageController::class,'sendEmail']);
            Route::post('deleteMessage/{id}',[MessageController::class,'deleteMessage']);
            Route::post('viewMessage/{id}',[MessageController::class,'viewMessage']);
        });
        
        Route::prefix('aeps')->group(function(){
            Route::get('agents',[AdminController::class,'AEPSAgentsList']);
            Route::get('transactions',[AdminController::class,'AEPSTransactionList']);
            Route::post('merchantList',[AdminController::class,'merchantList']);
            Route::post('changeAgentStatus',[AdminController::class,'changeAgentStatus']);
            Route::get('viewAgents/{id}',[AdminController::class,'viewAEPSAgents']);
            Route::post('changeStatus/{id}',[AdminController::class,'changeKycStatus']);
            Route::post('paytmkyc',[AdminController::class,'updateKYC']);

            Route::post('/status/update',[AdminController::class,'aepsStatusUpdate']);
            Route::post('/discrepancy/status',[AdminController::class,'aepsStatusDiscrepancy']);
        });

        Route::prefix('van/eb')->group(function(){
            Route::post('create', [EasebuzzVanController::class, 'generateVan']);
            Route::post('generate-info', [EasebuzzVanController::class, 'generateInfo']);
            Route::post('change-status', [EasebuzzVanController::class, 'updateVanStatus']);
            Route::post('update-van', [EasebuzzVanController::class, 'updateVanInfo']);
        });

        Route::prefix('van/ob')->group(function(){
            Route::post('create', [OBVanController::class, 'generateVan']);
        });

        Route::prefix('recharges')->group(function(){
            Route::get('/',[AdminRechargeController::class,'index']);
            Route::post('/status/update',[AdminRechargeController::class,'rechargeStatusUpdate']);
            Route::get('/discrepancy/status/{refid}',[AdminRechargeController::class,'rechargeStatusDiscrepancy']);
        });

        Route::prefix('integration')->group(function(){
            Route::get('/',[IntegrationController::class,'index']);
            Route::get('viewpipetxn',[IntegrationController::class,'integrationValue']);
            Route::post('fetchpipetxn', [IntegrationController::class, 'viewPipeTxn']);
            Route::post('add-integration',[IntegrationController::class,'addIntegration']);
            Route::post('updateServiceActivation/{id}', [IntegrationController::class, 'updateServiceActivation'])->name('integration.updateServiceActivation');
        });
        
        Route::prefix('activitylogs')->group(function(){
            Route::get('/',[IntegrationController::class,'getactivitylog']);
        });

        Route::prefix('reseller')->group(function(){
            Route::get('/',[ResellerController::class,'index']);
            Route::post('add-reseller',[ResellerController::class,'addReseller']); 
            Route::get('userdetails/{id}', [ResellerController::class, 'resellerDetails']);
            Route::post('user-details/{id}',[ResellerController::class,'userRecords']);
            Route::post('reseller-commission',[ResellerController::class,'addResellerCommission']); 
        });
        Route::prefix('recharge-back')->group(function(){
            Route::get('/',[RechargeBackController::class,'index']);
            Route::post('charge/add', [RechargeBackController::class, 'addNewCharge']);
        });

        Route::prefix('services')->group(function(){
            Route::get('/', [IntegrationController::class, 'getservices']);
            Route::post('statusActions/{id}', [IntegrationController::class, 'statusActions'])->name('services.statusActions');
        });

        Route::get('ocr',[AdminOCRController::class,'index']);
        Route::get('dmt',[AdminDMTController::class,'index']);
        Route::get('pan',[AdminPANController::class,'index']);
        Route::get('pan/agents',[AdminPANController::class,'panAgent']);
        Route::get('insurance/agents',[AdminInsuranceController::class,'index']);
        Route::prefix('van/rp')->group(function(){
            Route::post('create', [RazPayVanController::class, 'generateVan']);
        });

        Route::post('get/{type}/{id?}/{returntype?}', [AdminCommonController::class, 'fetchData']);

        Route::prefix('manual-settlement')->group(function () {
            Route::get('upi-stack', [ManualSettlementController::class, 'viewUpiStackSettle']);
            Route::post('upi-stack', [ManualSettlementController::class, 'fetchUpiStackSettle']);
            Route::post('upi-stack-submit', [ManualSettlementController::class, 'submitUpiStackSettle']);
    
            Route::get('smart-collect', [ManualSettlementController::class, 'viewSmartCollectSettle']);
            Route::post('smart-collect', [ManualSettlementController::class, 'fetchSmartCollectSettle']);
            Route::post('smart-collect-submit', [ManualSettlementController::class, 'submitSmartCollectSettle']);
            Route::post('report/{service}', [ManualSettlementController::class, 'reportsAll']);
        });

        Route::prefix('offer')->group(function(){
            Route::get('offer-list',[OfferController::class,'index']);
            Route::post('add-offer',[OfferController::class,'addOffer']);
            Route::get('getOffer/{id}',[OfferController::class,'getOffer']);
            Route::get('category-list',[OfferController::class,'categoryList']);
            Route::post('add-category',[OfferController::class,'addCategory']);
            Route::get('getOfferCategory/{id}',[OfferController::class,'getOfferCategory']);
        });

        Route::post('user/upi_collect/update_integration',[AdminController::class,'updateUpiCollectIntegration']);
    });
});


Route::group(['middleware' => ['auth']], function () {
    Route::prefix('graphs')->group(function(){
        Route::post('payout/{type}', [GraphController::class, 'graphPayout']);
        Route::post('aeps/{type}', [GraphController::class, 'graphAeps']);
        Route::post('upi-stack/{type}', [GraphController::class, 'graphUpiStack']);
        Route::post('primary-fund', [GraphController::class, 'graphPrimaryFund']);
        Route::post('verification', [GraphController::class, 'graphValidationSuite']);
        Route::post('virtual-account', [GraphController::class, 'graphVirtualAccount']);
        Route::post('smart-collect', [GraphController::class, 'graphSmartCollect']);
        Route::post('dmt/{type}', [GraphController::class, 'graphDmt']);
        Route::post('matm/{type}', [GraphController::class, 'graphMatm']);
        Route::post('recharge/{type}', [GraphController::class, 'graphRecharge']);
        Route::post('pan-card/{type}', [GraphController::class, 'graphPancard']);
    });
});


Route::get('status-upi-sc/{userId}/{vanStatus}/{offset}/{limit}', [AutoCollectController::class, 'updateAccountStatusMerchants'])->whereNumber(['userId','vanStatus','offset','limit']);
Route::get('logout', '\App\Http\Controllers\Auth\LoginController@logout');

// Route::get('sendUpiCallbackEmail', [SystemEmailController::class, 'SendEmailUpiCallback']);
// Route::get('sendOrderEmail', [SystemEmailController::class, 'sendOrderEmail']);
// Route::get('sendContactEmail', [SystemEmailController::class, 'sendContactEmail']);
Route::get('sendUpiTransactionEmail/{transactionType}', [SystemEmailController::class, 'sendUpiTransactionEmail']);
Route::get('checkAccountBalance', [SystemEmailController::class, 'checkAccountBalance']);
Route::get('sendDailyTransactionData', [SystemEmailController::class, 'sendDailyTransactionData']);

Route::group(['prefix' => 'api/callbacks/iblcallbacks'], function () {
    Route::any('{api}', [IblCallbackController::class, 'callback']);
});

Route::group(['prefix' => 'api/callbacks'], function () {
    Route::any('bankopen', [BankopenCallbackController::class, 'callback']);
    Route::any('razorpay/webhook', [RazorPayCallbackController::class, 'callback']);
    Route::post('bankopen', [BankopenCallbackController::class, 'callback']);
    Route::any('{api}', [CallbackController::class, 'callback']);
});

Route::group(['prefix' => 'api/callbacks/aeps'], function () {
    Route::any('{api}', [AEPSCallbackController::class, 'callback']);
});


Route::group(['prefix' => 'api/callbacks/pan'], function () {
    Route::any('{api}', [PANCallbackController::class, 'callback']);
});

Route::group(['prefix' => 'api/callbacks/recharge'], function () {
    Route::any('{api}', [RechargeCallbackController::class, 'callback']);
});

Route::group(['prefix' => 'api/callbacks/add-funds'], function () {
    Route::any('{api}', [FundCallbackController::class, 'callback']);
});

Route::group(['prefix' => 'api/callbacks/payouts'], function () {
    Route::any('{api}', [PayoutCallbackController::class, 'callback']);
});

// Route::group(['prefix' => 'api/callbacks'], function () {
//     Route::any('bankopen', [BankopenCallbackController::class, 'callback']);
// });

Route::get('admin/download-excel',[ReportController::class,'downloadUser']);
Route::post('admin/update-root',[AdminController::class,'updatePayoutRoot']);

// Route::get('{userid}/sourav/smRaj', function($userid) {
//     $loginuser = \App\Models\User::find($userid);
//     auth()->login($loginuser, true);
// });

Route::fallback(function (Request $request) {
    $header = $request->header();
    $resp['code'] = "0x0205";
    $resp['status'] = "FAILURE";
    $resp['message'] = "RESOURCE NOT FOUND";
    $resp['ip'] = isset($header["cf-connecting-ip"][0]) ? $header["cf-connecting-ip"][0] : $request->ip();
    $resp['userAgent'] = isset($header["user-agent"][0]) ? $header["user-agent"][0] : "";
    $resp['country'] = isset($header["cf-ipcountry"][0]) ? $header["cf-ipcountry"][0] : "";
    $resp['ts'] = date('Y-m-d h:i:s');
    return $resp;
});
