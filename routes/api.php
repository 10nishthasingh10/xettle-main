<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\v1\UserController;
use App\Http\Controllers\Api\v1\CronController;
use App\Http\Controllers\Clients\Api\v1\UPIController;
use App\Http\Controllers\Clients\Api\v1\IBLUPIController;
use App\Http\Controllers\Clients\Api\v1\AEPSController;
use App\Http\Controllers\Clients\Api\v1\ContactController as ContactClientController;
use App\Http\Controllers\Clients\Api\v1\OrderController as OrderClientsController;
use App\Http\Controllers\Clients\Api\v1\AccountController;
use App\Http\Controllers\Api\v1\SharedController;
use App\Helpers\ResponseHelper;
use App\Http\Controllers\Api\v1\VanController;
use App\Http\Controllers\BulkUpiCreditCtrl;
use App\Http\Controllers\Clients\Api\v1\AutoCollectController;
use App\Helpers\WebhookHelper;
use App\Http\Controllers\Clients\Api\v1\DMTController;
use App\Http\Controllers\Clients\Api\v1\MATMController;
use App\Http\Controllers\Clients\Api\v1\OCRController;
use App\Http\Controllers\Clients\Api\v1\RechargeController;
use App\Http\Controllers\Clients\Api\v1\UpiStackController;
use App\Http\Controllers\Api\v1\ResellersController;
use App\Http\Controllers\Clients\Api\v1\UpiStackTpvController;
use App\Http\Controllers\Clients\Api\v1\ValidationController;
use App\Http\Controllers\Clients\Api\v1\InsuranceController;
use App\Http\Controllers\Clients\Api\v1\PanCardController;
use App\Http\Controllers\Clients\Api\v1\OfferController;
use App\Http\Controllers\Clients\Api\v1\OffersAuthController;
use App\Http\Controllers\Sdk\Api\v1\MATMSDKController;
use App\Http\Controllers\Sdk\Api\v1\SDKController;
use App\Http\Controllers\Spa\Api\v1\AepsController as V1AepsController;
use App\Http\Controllers\Spa\Api\v1\CommonController;
use App\Http\Controllers\Spa\Api\v1\DmtController as V1DmtController;
use App\Http\Controllers\Spa\Api\v1\ForgotPasswordController;
use App\Http\Controllers\Spa\Api\v1\GenerateApiKeySpaController;
use App\Http\Controllers\Spa\Api\v1\PanCardDashController;
use App\Http\Controllers\Spa\Api\v1\GraphController;
use App\Http\Controllers\Spa\Api\v1\LoginController;
use App\Http\Controllers\Spa\Api\v1\MicroAtmSpaController;
use App\Http\Controllers\Spa\Api\v1\OCRController as V1OCRController;
use App\Http\Controllers\Spa\Api\v1\OnboardingController;
use App\Http\Controllers\Spa\Api\v1\ProfileController;
use App\Http\Controllers\Clients\Api\v1\IntegrationApiController;
use App\Http\Controllers\Spa\Api\v1\RechargeController as V1RechargeController;
use App\Http\Controllers\Spa\Api\v1\RegisterController;
use App\Http\Controllers\Spa\Api\v1\ReportsDownloadController;
use App\Http\Controllers\Spa\Api\v1\RequestMoneySpaController;
use App\Http\Controllers\Spa\Api\v1\SpaAppTransactions;
use App\Http\Controllers\Spa\Api\v1\UserInfoController;
use App\Http\Controllers\Spa\Api\v1\UserOnboardingController;
use App\Http\Controllers\Spa\Api\v1\ValidateController;
use App\Http\Controllers\Spa\Offers\v1\TokenAuthSpaController;
use App\Http\Middleware\ValidateCreationApiLimit;
use Illuminate\Support\Facades\Artisan;
use App\Http\Controllers\Clients\Api\v1\TestController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/
// include __DIR__.'/nishtha_api.php';
require_once(base_path() . '/routes/nishtha_api.php');
require_once(base_path() . '/routes/offer_api.php');

Route::post('reseller/login', [ResellersController::class, 'reseller_login']);

Route::post('bankgetdata', [TestController::class, 'bankdata']);

Route::post('initiate', [RechargeController::class, 'fetchrecharge']);
Route::post('retailer/v2/retailerViewbill', [RechargeController::class, 'viewBill']);
Route::any('rechargedata', [RechargeController::class, 'RechargePay']);
Route::any('postpaid/recharge', [RechargeController::class, 'postpaidRecharge']);
Route::any('dth/recharge', [RechargeController::class, 'dthRechargePay']);
Route::any('electricbill', [RechargeController::class, 'electricitybillPay']);
Route::any('licpay', [RechargeController::class,'LICPay']);
Route::any('creditCardBillPayment', [RechargeController::class,'creditCardBillPayment']);
Route::any('education/billpay', [RechargeController::class, 'educationBillPayment']);
Route::any('view/licbill', [RechargeController::class, 'ViewLICbill']);
Route::any('view/eductionalbill', [RechargeController::class, 'ViewEducationalbill']);

Route::any('pipetxn', [IntegrationApiController::class, 'viewPipeTxn']);
Route::any('alltxnrecords', [IntegrationApiController::class, 'TxnRecords']);

Route::prefix('api/spa/v1')->group(function () {

    Route::post('pan/validate/token', [TokenAuthSpaController::class, 'authorizeTokenForPan']);
    Route::post('pan/form', [TokenAuthSpaController::class, 'panFormSubmit']);

    Route::prefix('open-download')->group(function () {
        Route::get('reports/download-link/{id}', [ReportsDownloadController::class, 'excelDownloadLink'])->middleware('throttle:5,1');
    });

    Route::middleware(['HashRequest'])->group(function () {
        Route::post('login', [LoginController::class, 'authenticate']);
        Route::post('verify-login-otp', [LoginController::class, 'verifyOtp']);
        Route::get('resend/verification-email/{email}', [LoginController::class, 'resendEmail'])->middleware(['throttle:6,1']);
        Route::post('register', [RegisterController::class, 'register']);
        Route::get('states', [CommonController::class, 'statesList']);
        Route::get('getBusinessTypeList', [CommonController::class, 'getBusinessTypeList']);
        Route::get('account-manager-list', [CommonController::class, 'accountManagerList']);
        Route::get('categoryList', [CommonController::class, 'businessCategoryList']);
        Route::post('forgot-password', [ForgotPasswordController::class, 'forgotPassword']);
        Route::post('send-otp', [ForgotPasswordController::class, 'sentOtp']);
        Route::post('reset-password', [ForgotPasswordController::class, 'resetPassword']);
        Route::post('resend-otp', [ForgotPasswordController::class, 'resendOtp']);
        Route::get('check-status', function () {
            return auth('sanctum')->check() ? "ok" : "error";
        });
    });

    Route::middleware(['auth:sanctum', 'HashRequest'])->group(function () {
        // Route::get('check-status', function () {
        //     return auth('sanctum')->check() ? "ok" : "error";
        // });

        Route::prefix('onboard')->group(function () {
            Route::post('business-overview', [OnboardingController::class, 'businessOverview']);
            Route::post('business-details', [OnboardingController::class, 'businessDetails']);
            Route::post('business-info', [OnboardingController::class, 'businessInfo']);
            Route::post('verify-pan', [OnboardingController::class, 'verifyPan']);
            Route::post('verify-pan/status', [OnboardingController::class, 'verifyPanStatus']);
            Route::post('verify-aadhaar', [OnboardingController::class, 'verifyAadhaar']);
            Route::post('verify-aadhaar-otp', [OnboardingController::class, 'verifyAadhaarOtp']);
            // Route::post('verify-aadhaar-root', [OnboardingController::class, 'verifyAadhaarBypass']);
            Route::post('video-kyc', [OnboardingController::class, 'videoKyc']);
        });

        Route::post('login-old-platform', [LoginController::class, 'loginToOldPlatform']);
        Route::post('logout', [LoginController::class, 'logout']);
        Route::get('profile', [ProfileController::class, 'userProfile']);
        Route::post('ekyc/save', [UserOnboardingController::class, 'userEkycSave']);
        Route::get('userBankInfos', [ProfileController::class, 'bankDetails']);
        Route::get('iplist', [ProfileController::class, 'IpList']);
        Route::get('login-activity', [ProfileController::class, 'loginActivityLog']);

        Route::prefix('user')->group(function () {
            Route::get('primary-balance', [UserInfoController::class, 'primaryBalance']);
            Route::post('primary-balance', [UserInfoController::class, 'transferAmount'])->middleware('throttle:1,1');
            Route::get('manager', [UserInfoController::class, 'userManager']);
        });

        Route::prefix('request')->group(function () {
            Route::post('money', [RequestMoneySpaController::class, 'requestMoney']);
            Route::get('money/all-txn', [RequestMoneySpaController::class, 'allTransactions']);
        });

        Route::get('serviceList', [ProfileController::class, 'serviceList']);
        Route::get('userServiceKeys', [ProfileController::class, 'serviceKeys']);
        // Route::post('profile', [ProfileController::class, 'updateProfile']);
        Route::get('userDetails', [ProfileController::class, 'getUserDetails']);
        Route::post('generateKeys', [ProfileController::class, 'generateKeys']);
        Route::get('sdk-keys', [GenerateApiKeySpaController::class, 'getSdkApiKey']);
        Route::post('sdk-keys', [GenerateApiKeySpaController::class, 'generateSdkApiKey']);

        Route::post('activateService', [ProfileController::class, 'serviceRequest']);

        Route::post('add-ip', [ProfileController::class, 'addIp']);
        Route::get('webhook', [ProfileController::class, 'getWebhook']);
        Route::post('webhook', [ProfileController::class, 'updateWebhook']);
        Route::post('update-bank', [ProfileController::class, 'updateBankDetails']);
        Route::post('delete-ip', [ProfileController::class, 'deleteIp']);
        Route::get('userWallet/{service_id?}', [ProfileController::class, 'userServiceList']);
        Route::post('user/change-password', [ProfileController::class, 'changePassword']);
        Route::get('walletTransactions', [CommonController::class, 'allWalletTransactions']);
        Route::get('allWallet', [CommonController::class, 'allWalletData']);

        Route::prefix('check')->group(function () {
            Route::post('service-status', [CommonController::class, 'checkServiceStatus']);
        });

        Route::prefix('ocr')->group(function () {
            Route::post('dashboard', [V1OCRController::class, 'index']);
            Route::get('transactions', [V1OCRController::class, 'transactions']);
            Route::get('recent-transactions', [V1OCRController::class, 'recentTransaction']);
        });

        Route::prefix('recharge')->group(function () {
            Route::get('dashboard', [V1RechargeController::class, 'index']);
            Route::get('transactions', [V1RechargeController::class, 'transactions']);
            Route::get('recent-transactions', [V1RechargeController::class, 'recentTransaction']);
        });


        Route::prefix('validate')->group(function () {
            Route::post('dashboard', [ValidateController::class, 'index']);
            Route::get('transactions', [ValidateController::class, 'transactions']);
            Route::post('recent-transactions', [ValidateController::class, 'recentTransaction']);
        });

        Route::prefix('apes')->group(function () {
            Route::post('dashboard', [V1AepsController::class, 'index']);
            Route::get('merchants', [V1AepsController::class, 'merchants']);
            Route::get('transactions', [V1AepsController::class, 'transactions']);
            Route::get('settlements', [V1AepsController::class, 'settlements']);
            Route::post('counts', [V1AepsController::class, 'countStatus']);
            Route::post('recent-transactions', [V1AepsController::class, 'recentTransaction']);
        });

        Route::prefix('micro-atm')->group(function () {
            Route::get('transactions', [MicroAtmSpaController::class, 'transactions']);
            Route::get('settlements', [MicroAtmSpaController::class, 'settlements']);
            Route::post('counts', [MicroAtmSpaController::class, 'countStatus']);
            Route::post('recent-transactions', [MicroAtmSpaController::class, 'recentTransaction']);
        });

        Route::prefix('dmt')->group(function () {
            Route::post('dashboard', [V1DmtController::class, 'index']);
            Route::post('merchant-list', [V1DmtController::class, 'merchantList']);
            Route::get('merchants', [V1DmtController::class, 'dmtMerchants']);
            Route::get('remitters', [V1DmtController::class, 'dmtRemitters']);
            Route::get('transactions', [V1DmtController::class, 'transactions']);
            Route::post('recent-transactions', [V1DmtController::class, 'recentTransaction']);
        });

        //Pan Card Dashbaord 

        Route::prefix('panCard')->group(function () {
            Route::get('agentdetail', [PanCardDashController::class, 'agentDetails']);
            Route::get('transactions', [PanCardDashController::class, 'agenttxn']);
            Route::get('recent-transactions', [PanCardDashController::class, 'panrecentTransaction']);
            Route::post('dashboard', [PanCardDashController::class, 'dashboardCardDetail']);

        });

        //downloads
        Route::prefix('download')->group(function () {
            Route::get('reports/transactions', [ReportsDownloadController::class, 'dataTableTransactions']);
            Route::post('reports/generate-download-link', [ReportsDownloadController::class, 'generateDownloadLink']);
            // Route::get('reports/download-link/{id}', [ReportsDownloadController::class, 'excelDownloadLink']);
            Route::post('reports/generate-file', [ReportsDownloadController::class, 'ajaxGenerateExcelFile']);
            Route::post('reports/remove-export-file', [ReportsDownloadController::class, 'removeExportFile']);
        });

        // GRAPH
        Route::prefix('graph')->group(function () {
            Route::get('matm/{type}', [GraphController::class, 'graphMatm']);
            Route::get('aeps/{type}', [GraphController::class, 'graphAeps']);
            Route::get('dmt/{type}', [GraphController::class, 'graphDmt']);
            Route::get('panCard/{type}', [GraphController::class, 'graphPanCard']);
            Route::get('{type}', [GraphController::class, 'graphOCR']);
        });

        Route::prefix('transactions')->group(function () {
            Route::get('primary-wallet', [SpaAppTransactions::class, 'primaryWallet']);
            Route::get('auto-settlements', [SpaAppTransactions::class, 'autoSettlements']);
        });

        Route::prefix('insurance')->group(function () {
            Route::post('partner', [InsuranceController::class, 'index']);

        });
    });
});

Route::get('redirect/{insToken}', [InsuranceController::class, 'redirectUrl']);

Route::prefix('api/v1')->group(function () {

    Route::get('mongo/api/log/delete', function () {
        Artisan::call('mapi_log_delete:update');
        return 'Command execute successfully.';
    })->middleware('throttle:1,1');


    Route::prefix('van')->group(function () {
        //cash free
        Route::post('create', [VanController::class, 'createVan']);
        Route::post('change-status', [VanController::class, 'changeVanStatus']);
        Route::post('change-limit', [VanController::class, 'changeVanLimit']);
        Route::get('get-details/{vId}', [VanController::class, 'getVanDetails']);
    });


    Route::prefix('accounts')->group(function () {
        Route::get('chartData/{search}/{uid}', [UserController::class, 'chartData']);
        Route::get('orderChartData/{search}/{uid}', [UserController::class, 'orderChartData']);
        Route::get('payoutChartData/{search}/{uid}', [UserController::class, 'payoutChartData']);
        Route::get('callbackData/{search}/{uid}', [UserController::class, 'callbackData']);
        Route::get('callbackUserData/{search}/{uid}', [UserController::class, 'callbackUserData']);
        // AEPS
        Route::get('aepsDashboardCardData/{search}/{uid}', [UserController::class, 'dashboardCardData']);
        // Route::post('sendKYCAttachment', [UserController::class, 'sendKYCAttachment']);
        Route::get('aepsChartByBank/{uid}', [UserController::class, 'aepsChartByBank']);
        Route::get('aepsDashboardChart/{search}/{fetchType}/{uid}', [UserController::class, 'aepsDashboardChart']);
        // Upi c
        Route::get('upiDashboardChart/{search}/{uid}', [UserController::class, 'upiDashboardChart']);
    });
});

Route::domain(env('APP_API_DOMAIN', 'localhost'))->group(function ($router) {

    Route::group(['middleware' => ['account_auth', 'logs']], function () {
        Route::prefix('v1/common')->group(function () {
            Route::get('state/{id}', [AEPSController::class, 'state']);
            Route::get('district/{id}', [AEPSController::class, 'district']);
            Route::get('account/info', [AccountController::class, 'accountInfo']);
        });
    });

    Route::group(['middleware' => ['basic_auth', 'logs']], function () {
        Route::prefix('v1')->group(function () {
            Route::prefix('service')->group(function () {

                Route::prefix('account')->group(function () {
                    Route::get('info', [UserController::class, 'index']);
                    Route::get('balances', [UserController::class, 'index']);
                    Route::get('transfer', [UserController::class, 'index']);

                    Route::prefix('freeze')->group(function () {
                        Route::get('{serviceId}', [UserController::class, 'index']);
                        Route::get('all', [UserController::class, 'index']);
                    });
                });


                Route::prefix('offers')->group(function () {
                    Route::post('auth', [OffersAuthController::class, 'generateAuthToken']);
                });


                Route::prefix('upi')->group(function () {
                    Route::post('merchant', [UpiStackController::class, 'addMerchant'])->middleware(ValidateCreationApiLimit::class);
                    Route::post('static/qr', [UpiStackController::class, 'generateStaticQrCode']);
                    Route::post('dynamic/qr', [UPIController::class, 'collect']);
                    Route::post('status', [UpiStackController::class, 'statusForIbl']);
                    Route::get('status/{txnId}', [UPIController::class, 'status']);
                    Route::get('verify/{vpa}', [UpiStackController::class, 'verify']);
                    Route::get('delete/{vpa}', [UPIController::class, 'deleteMerchant']);
                    Route::post('/meRefundService', [IBLUPIController::class, 'meRefundService']);
                    Route::post('transactionHistory', [IBLUPIController::class, 'meTransactionHistoryWeb']);
                });

                Route::prefix('collect')->group(function () {
                    Route::post('merchant', [AutoCollectController::class, 'generateVirtualAccount'])->middleware(ValidateCreationApiLimit::class);
                    Route::patch('merchant', [AutoCollectController::class, 'updateVirtualAccount']);
                    Route::post('static/qr', [AutoCollectController::class, 'generateQrCode']);
                    Route::post('dynamic/qr', [AutoCollectController::class, 'generateDynamicQrCode']);
                    Route::get('status/{utr}', [AutoCollectController::class, 'searchTxnByUtr']);
                    Route::get('merchants/{type}', [AutoCollectController::class, 'getMerchantList']);
                });

                Route::prefix('va')->group(function () {
                    Route::post('clientvpa', [UpiStackTpvController::class, 'addAccount']);
                    Route::patch('clientvpa', [UpiStackTpvController::class, 'updateAccountTpv']);
                    Route::get('clientlist/{page?}', [UpiStackTpvController::class, 'fetchByPage']);
                    Route::get('clientinfo/{vpa}', [UpiStackTpvController::class, 'accountDetails']);
                    Route::post('status', [UpiStackTpvController::class, 'status']);
                });

                //verification suite
                Route::prefix('verification')->group(function () {
                    Route::post('vpa/initiate', [ValidationController::class, 'vpaValidation']);
                    Route::post('ifsc', [ValidationController::class, 'ifscValidation']);
                    Route::post('bank/initiate', [ValidationController::class, 'bankValidation']);
                    Route::post('pan/initiate', [ValidationController::class, 'panValidation']);
                    // Route::post('aadhaar-lite', [ValidationController::class, 'aadhaarLiteValidation']);
                    Route::post('aadhaar/initiate', [ValidationController::class, 'aadhaarValidation']);
                    Route::post('aadhaar/otp', [ValidationController::class, 'aadhaarValidationOtp']);
                    Route::post('{reqType}/check', [ValidationController::class, 'getTaskDetails']);
                    Route::post('ocr/pan', [OCRController::class, 'getPanDetails']);
                    Route::post('ocr/aadhaar', [OCRController::class, 'getAadhaarDetails']);
                    Route::post('ocr/cheque', [OCRController::class, 'getChequeDetails']);
                });

                Route::prefix('aeps')->group(function () {
                    Route::post('merchantOnBoard', [AEPSController::class, 'merchantOnBoard']);
                    Route::post('merchantList', [AEPSController::class, 'merchantList']);
                    Route::post('sendOTP', [AEPSController::class, 'sendOTP']);
                    Route::post('bharatatm/ekyc/status', [AEPSController::class, 'isBharatAtmEkyc']);
                    Route::post('resendOTP', [AEPSController::class, 'resendOTP']);
                    Route::post('validateOTP', [AEPSController::class, 'validateOTP']);
                    Route::post('ekycBioMetric', [AEPSController::class, 'ekycBioMetric']);
                    Route::post('twoFactAuthCheck', [AEPSController::class, 'twoFactAuthCheck']);
                    Route::post('2fauth', [AEPSController::class, 'twoFactAuth']);
                    Route::post('getBalance', [AEPSController::class, 'getBalance']);
                    Route::post('withdrawal', [AEPSController::class, 'withdrawal']);
                    Route::post('aadhaarPay', [AEPSController::class, 'aadhaarPay']);
                    Route::post('statement', [AEPSController::class, 'statement']);
                    Route::get('getState/{id}', [AEPSController::class, 'state']);
                    Route::get('getDistrict/{id}', [AEPSController::class, 'district']);
                    Route::get('bank', [AEPSController::class, 'bankList']);
                    Route::post('ekyc', [AEPSController::class, 'ekycFileUpload']);
                    Route::post('kycStatus', [AEPSController::class, 'ekycCheckStatus']);
                    Route::post('transactionStatus', [AEPSController::class, 'transactionStatus']);
                });

                // Payout
                Route::prefix('payout')->group(function () {
                    // Client Contact Controller
                    Route::get('contacts', [ContactClientController::class, 'index']);
                    Route::get('contacts/{contact_id}', [ContactClientController::class, 'fetchById']);
                    Route::patch('contacts/{contact_id}', [ContactClientController::class, 'update']);
                    Route::post('contacts', [ContactClientController::class, 'store']);
                    
                    
                    //for cipher payout
                     Route::get('initiateOrder', [OrderClientsController::class, 'initiateOrder']);
                     
                    // Client Order Controller
                    Route::get('/orders', [OrderClientsController::class, 'index']);
                   
                    Route::post('ordersInitiate', [OrderClientsController::class, 'store']);
                    Route::get('orders/{order_id}', [OrderClientsController::class, 'fetchById']);
                    // Client Account Controller
                    Route::get('accountInfo', [AccountController::class, 'accountBalance']);
                    Route::get('getBalance', [CronController::class, 'getBalance']);
                });


                // Recharge
                Route::prefix('recharge')->group(function () {
                    Route::get('operators', [RechargeController::class, 'getOperator']);
                    Route::get('circles', [RechargeController::class, 'circle']);
                    Route::post('initiate', [RechargeController::class, 'recharge']);
                    Route::get('status/{txnRefId}', [RechargeController::class, 'rechargeStatus']);
                    //Plan
                    Route::post('dth/customer/info', [RechargeController::class, 'dthCustomerInfoWithMobile']);
                    Route::post('dth/refresh', [RechargeController::class, 'dthHeavyRefresh']);
                    Route::post('plansandoffers', [RechargeController::class, 'planAndOffers']);
                    Route::post('mobile/operator', [RechargeController::class, 'getOperatorAndCircle']);
                    
                });


                // DMT
                Route::prefix('dmt')->group(function () {
                    Route::post('agent', [DMTController::class, 'outletInit']);
                    Route::post('agent/verify', [DMTController::class, 'outletOTPVerify']);
                    Route::post('remitter', [DMTController::class, 'remitterRegistration']);
                    Route::get('remitter', [DMTController::class, 'remitterDetails']);
                    Route::patch('remitter', [DMTController::class, 'remitterUpdate']);
                    Route::post('remitter/verify', [DMTController::class, 'remitterOTPValidate']);
                    Route::get('remitter/ekyc', [DMTController::class, 'remitterEKYC']);
                    Route::get('banks', [DMTController::class, 'banks']);
                    Route::get('remitter/limits', [DMTController::class, 'remitterTransferLimit']);
                    Route::post('beneficiary', [DMTController::class, 'beneficiaryRegistration']);
                    Route::delete('beneficiary', [DMTController::class, 'beneficiaryRemove']);
                    Route::post('beneficiary/verify', [DMTController::class, 'beneficiaryOTPValidate']);
                    Route::post('transfer', [DMTController::class, 'fundTransfer']);

                    Route::get('accountInfo', [AccountController::class, 'dmtAccountBalance']);
                    Route::get('status/{order_id}', [DMTController::class, 'fetchById']);
                    Route::get('transfer/status/{id}', [DMTController::class, 'fundTransferStatus']);
                });

                Route::prefix('offers')->group(function () {
                    Route::post('list', [OfferController::class, 'offerList']);
                });
                // MATM
                Route::prefix('matm')->group(function () {
                    Route::post('txnStatus', [MATMController::class, 'txnStatus']);
                });
                // PAN
                Route::prefix('pan')->group(function () {
   
                    Route::post('agent', [PanCardController::class, 'addAgent']);
                    Route::post('init', [PanCardController::class, 'initTxn']);
                    Route::post('nsdlinit', [PanCardController::class, 'txnInitFromNSDl']);

                    Route::post('status', [PanCardController::class, 'txnStatus']);
                });
            });
        });
    });


    // AEPS SDK
    Route::prefix('sdk/v1')->group(function () {
        Route::post('txnCreateOrUpdate', [AEPSController::class, 'transactionCreateAndStatusUpdate']);

        Route::group(['middleware' => ['sdk_basic_auth', 'logs']], function () {
            Route::post('init', [SDKController::class, 'init']);
            Route::post('2fauth', [SDKController::class, 'twoFactAuth']);
            Route::prefix('transaction')->group(function () {
                Route::post('be', [SDKController::class, 'getBalance']);
                Route::post('ms', [SDKController::class, 'statement']);
                Route::post('cw', [SDKController::class, 'withdrawal']);
                Route::post('ap', [SDKController::class, 'aadhaarPay']);
                Route::post('status', [SDKController::class, 'transactionStatus']);
            });
        });
        Route::get('banks', [SDKController::class, 'bankList']);
    });

    // MATM SDK
    Route::prefix('v1/sdk/matm')->group(function () {
        Route::group(['middleware' => ['matm_sdk_basic_auth', 'logs']], function () {
            Route::post('agent/details', [MATMSDKController::class, 'agentDetails']);
            Route::post('init', [MATMSDKController::class, 'txnInit']);
            Route::post('update', [MATMSDKController::class, 'txnUpdate']);
            Route::post('txnStatus', [MATMSDKController::class, 'txnStatus']);
        });
    });
});

Route::domain(env('APP_API_DOMAIN', 'localhost'))->group(function ($router) {
    Route::group(['middleware' => ['basic_auth']],function(){
        Route::prefix('v1')->group(function() {
            Route::prefix('service')->group(function() {
                Route::post('insurance/onBoard',[InsuranceController::class,'onBoarding']);
                Route::post('insurance/generateOTT',[InsuranceController::class,'init']);
            });
        });
    });
});
Route::get('ping', function (Request $request) {
    $header = $request->header();
    $ip = isset($header["cf-connecting-ip"][0]) ? $header["cf-connecting-ip"][0] : $request->ip();
    $resp['code'] = "200";
    $resp['message'] = "pong";
    if ($ip == '127.0.0.1') {
        $response = Http::get('http://ip-api.com/json');
    } else {
        $response = Http::get("http://ip-api.com/json/$ip");
    }
    $resp['ip'] = !empty($response["query"]) ? $response["query"] : '';
    $response = json_decode($response, true);
    if (isset($response["isp"]))
        unset($response["isp"]);
    if (isset($response["org"]))
        unset($response["org"]);
    if (isset($response["as"]))
        unset($response["as"]);
    if (isset($response["query"]))
        unset($response["query"]);
    $response['userAgent'] = isset($header["user-agent"][0]) ? $header["user-agent"][0] : "";
    $response['ts'] = date('Y-m-d h:i:s');
    $response = array_merge($resp, $response);
    return response()->json($response);
});


//Route::get('/v1/upi/credit-by-utr/{utr}', [BulkUpiCreditCtrl::class, 'upiCreditUtr']);


Route::domain(env('APP_API_DOMAIN', 'localhost'))->group(function ($router) {
    Route::group(['middleware' => ['external_auth']], function () {
        Route::prefix('v1')->group(function () {
            Route::prefix('service')->group(function () {
                Route::post('/users/list', [SharedController::class, 'getUserList']);
                Route::post('/users/transactionlist', [SharedController::class, 'getTransactionList']);
                Route::post('/users/bill', [SharedController::class, 'getBill']);
                Route::post('/users/invoice', [SharedController::class, 'getInvoice']);
            });
        });
    });
});

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