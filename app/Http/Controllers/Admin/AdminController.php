<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\AdminCommonHelper;
use App\Helpers\CashfreeAutoCollectHelper;
use App\Helpers\CommonHelper;
use App\Helpers\NumberFormat;
use App\Helpers\ResponseHelper;
use App\Helpers\UserApiLogHelper;
use App\Helpers\AepsReconsilation;
use App\Helpers\MApiLogHelper;
use App\Helpers\WebhookLogHelper;
use App\Http\Controllers\Controller;
use App\Jobs\PrimaryFundCredit;
use App\Models\PanCard;
use App\Models\PanCardTransaction;
use App\Models\Reseller;
use Validations\OrderValidation as Validations;
use App\Models\User;
use App\Models\BusinessInfo;
use App\Http\Controllers\Clients\Api\v1\AEPSController;
use App\Jobs\SendTransactionEmailJob;
use App\Models\UserService;
use App\Models\BusinessCategory;
use App\Models\State;
use App\Models\Service;
use App\Models\Transaction;
use App\Models\AccountManager;
use App\Models\BulkPayoutDetail;
use App\Models\BulkPayout;
use App\Models\Order;
use App\Models\AepsTransaction;
use App\Models\GlobalConfig;
use App\Models\Role;
use App\Models\Agent;
use App\Models\Permission;
use App\Models\UserConfig;
use App\Models\Recharge;
use App\Models\Ocr;
use App\Models\Webhook;
use App\Models\Validation;
use App\Models\DMTFundTransfer;
use Illuminate\Http\Request;
use Yajra\DataTables\Html\Builder;
use Carbon\Carbon;
use Transaction as TransactionHelper;
use Exception;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class AdminController extends Controller
{
    /**
     * Admin Home Function
     *
     * @return void
     */
    public function index()
    {
        session(['is_theme_change' => Auth::user()->is_theme_change]);

        $data['page_title'] = "Dashboard";
        $data['site_title'] = "Dashboard";
        $data['view'] = "admin.home";

        $data['userList'] = DB::table('users')
            ->select('id', 'name', 'email')
            ->where('is_admin', '0')
            ->orderBy('name', 'asc')
            ->get();

        $data['serviceList'] = DB::table('global_services')
            ->select('service_id', 'service_name')
            ->where('is_active', '1')
            ->orderBy('service_name', 'asc')
            ->get();

        // $data['top10Users'] = DB::table('users')
        //     ->select('users.name', 'users.email', 'business_infos.business_name',
        //         DB::raw('users.transaction_amount AS primary_amt'),
        //         DB::RAW('user_services.transaction_amount AS payout_amt'),
        //         DB::raw('SUM(users.transaction_amount+user_services.transaction_amount) AS total_amt')
        //     )
        //     ->leftJoin('user_services', 'users.id', '=', 'user_services.user_id')
        //     ->leftJoin('business_infos', 'users.id', '=', 'business_infos.user_id')
        //     ->where('user_services.service_id', PAYOUT_SERVICE_ID)
        //     ->where('user_services.is_active', '1')
        //     ->orderBy('total_amt', 'DESC')
        //     // ->limit(10)
        //     ->groupBy('users.id')
        //     ->get();

        // $data['users'] = User::where('is_admin', '0')->where('is_active', '1')->get();
        // $data['poolAccount'] = User::where('email', 'rbiaccount@xettle.io')->where('is_active', '1')->first();
        // $data['feeAccount'] = User::where('email', 'feeaccount@xettle.io')->where('is_active', '1')->first();
        // $data['taxAccount'] = User::where('email', 'taxaccount@xettle.io')->where('is_active', '1')->first();
        return view($data['view'])->with($data);
    }


    public function dashboardBalances()
    {
        $return['primary'] = DB::table('users')->where('is_admin', '0')->where('is_active', '1')
            ->sum('transaction_amount');
        $return['payout'] = DB::table('user_services')
            ->leftJoin('users', 'user_services.user_id', '=', 'users.id')
            ->where('users.is_admin', '0')->where('users.is_active', '1')
            ->where('user_services.service_id', PAYOUT_SERVICE_ID)->where('user_services.is_active', '1')
            ->sum('user_services.transaction_amount');
        $returnOrderQueue = DB::table('orders')
            ->select(
                DB::raw("SUM(amount+fee+tax) AS amt"),
                DB::raw("COUNT(id) AS counts")
            )
            ->where('status', 'queued')
            ->first();
        $return['orderQueue'] = $returnOrderQueue->amt;
        $return['orderQueueCount'] = $returnOrderQueue->counts;

        $returnOrderProcess = DB::table('orders')
            ->select(
                DB::raw("SUM(amount+fee+tax) AS amt"),
                DB::raw("COUNT(id) AS counts")
            )
            ->where('status', 'processing')
            ->first();
        $return['orderProcess'] = $returnOrderProcess->amt;
        $return['orderProcessCount'] = $returnOrderProcess->counts;


        $return['primaryActual'] = number_format($return['primary'], 2);
        $return['primary'] = NumberFormat::init()->change($return['primary'], 2);
        $return['payoutActual'] = number_format($return['payout'], 2);
        $return['payout'] = NumberFormat::init()->change($return['payout'], 2);
        $return['orderQueueActual'] = number_format($return['orderQueue'], 2);
        $return['orderQueue'] = NumberFormat::init()->change($return['orderQueue'], 2);
        $return['orderProcessActual'] = number_format($return['orderProcess'], 2);
        $return['orderProcess'] = NumberFormat::init()->change($return['orderProcess'], 2);

        return response()->json($return);
    }


    /**
     * get Business Account function
     *
     * @param Request $request
     * @return void
     */
    public function getBusinessInfo($account_number)
    {
        $BusinessInfo = BusinessInfo::select('name', 'account_number', 'ifsc', 'bank_name')
            ->where('account_number', $account_number)->first();
        $response['message'] = '';
        $response['data'] = [];
        $response['status'] = false;
        if (isset($BusinessInfo)) {
            $response['message'] = 'Business record found successfull';
            $response['data'] = $BusinessInfo;
            $response['status'] = true;
        } else {
            $response['message'] = 'No record found';
        }
        return $response;
    }

    public function allTransaction(Request $request)
    {
        if (Auth::user()->hasRole('super-admin') || Auth::user()->hasRole('support')) {
            $data['page_title'] = "All Transactions";
            $data['site_title'] = "All Transactions";
            $data['view'] = ADMIN . '/' . ".reports.transaction";
            $data['user'] = DB::table('users')->select('name', 'email', 'id', 'mobile')->where('is_admin', '0')->get();
            $data['transactions'] = DB::table('transactions')
                ->select('tr_identifiers')
                ->groupBy('tr_identifiers')->get();

            $data['serviceListObject'] = DB::table('global_services')
                ->select('service_name AS title', 'service_id')
                ->get();
            $id = 0;
            return view($data['view'], compact('id'))->with($data);
        } else {
            $data['url'] = url('admin/dashboard');
            return view('errors.401')->with($data);
        }
    }

    public function ordersList(Request $request)
    {
        if (Auth::user()->hasRole('super-admin') || Auth::user()->hasRole('support') || Auth::user()->hasRole('accountant')) {
            $data['page_title'] = "All Orders";
            $data['site_title'] = "All Orders";
            $data['view'] = ADMIN . '/' . ".reports.orders";
            $data['user'] = DB::table('users')->select('name', 'email', 'id', 'mobile')->where('is_admin', '0')->get();
            $data['roots'] = DB::table('integrations')->select('integration_id', 'name')->get();
            //  $data['contacts'] = DB::table('contacts')->select('contact_id', 'last_name','first_name','id','account_number', 'account_ifsc')->get();
            $qrString = \Request::getRequestUri();
            $getValueBatchId = "";
            if (isset($qrString) && str_contains($qrString, '?')) {
                $getArray = explode("?", $qrString);
                if (isset($getArray[1])) {
                    $getArray1 = explode("=", $getArray[1]);
                    if (isset($getArray1[1])) {
                        $getValueBatchId = $getArray1[1];
                    }
                }
            }
            $data['batchId'] = $getValueBatchId;
            $id = 0;
            return view($data['view'])->with($data);
        } else {
            $data['url'] = url('admin/dashboard');
            return view('errors.401')->with($data);
        }
    }



    public function bulkPayoutList(Request $request, Builder $builder)
    {
        if (Auth::user()->hasRole('super-admin')) {
            $data['page_title'] = "All Bulk Payout Listing";
            $data['site_title'] = "All Bulk Payout";
            $data['view'] = ADMIN . '/' . ".reports.bulk";
            $data['user'] = DB::table('users')->select('name', 'email', 'id', 'mobile')->where('is_admin', '0')->get();
            return view($data['view'])->with($data);
        } else {
            $data['url'] = url('admin/dashboard');
            return view('errors.401')->with($data);
        }
    }

    public function contactList(Request $request, Builder $builder)
    {
        if (Auth::user()->hasRole('super-admin') || Auth::user()->hasRole('support') || Auth::user()->hasRole('accountant')) {
            $data['page_title'] = "All Contacts Listing";
            $data['site_title'] = "All Contacts";
            $data['users'] = DB::table('users')->select('name', 'id', 'email')->get();
            $data['view'] = ADMIN . '/' . ".reports.contacts";
            return view($data['view'])->with($data);
        } else {
            $data['url'] = url('admin/dashboard');
            return view('errors.401')->with($data);
        }
    }

    public function transferAmountToUser(Request $request)
    {
        $id = decrypt($request->user_id);
        $validation = new Validations($request);
        $validator = $validation->transferAmount();
        $validator->after(function ($validator) use ($request, $id) {
            $userServices = UserService::where('user_id', $id)->where('is_active', '1')->first();
            $User = User::where('id', $id)->where('is_active', '1')->first();
            if (empty($userServices)) {
                $validator->errors()->add('service_id', 'Service account not active');
            } else {

                if ($request->transfer_amount > 0) {
                    if (floatval($User->transaction_amount) < floatval($request->transfer_amount)) {
                        $validator->errors()->add('transfer_amount', 'Insufficient Main Balance your balance is :' . $User->transaction_amount);
                    }
                } else {
                    $validator->errors()->add('transfer_amount', 'Please enter transfer amount greater then 0.');
                }
            }
        });
        if ($validator->fails()) {
            $this->message = $validator->errors();
        } else {

            /**  Add Transaction Details */
            $UserAccount = User::where('id', $id)->where('is_active', '1')->first();
            $UserService = UserService::where(['id' => $request->service_id, 'user_id' => $id])->first();

            TransactionHelper::internalTransfer($id, $UserService->service_id, $UserAccount->account_number, $UserService->service_account_number, $request->transfer_amount, $request->remarks);

            $this->status = true;
            $this->modal = true;
            $this->alert = false;
            $this->message = "Amount Transfer Successfull";
            $this->redirect = true;
            return $this->populateresponse();
        }
        return response()->json(
            $this->populate([
                'message' => $this->message,
                'status' => false,
                'data' => $this->message
            ])
        );
    }

    public function serviceActivationReq()
    {
        if (Auth::user()->hasRole('super-admin')) {
            $data['page_title'] = "Service Activation Request";
            $data['site_title'] = "Service Activation";
            $data['view'] = ADMIN . '/' . ".reports.servicerequest";

            $data['serviceList'] = DB::table('global_services')
                ->select('service_id', 'service_slug AS title', 'service_slug AS id')
                ->where('is_active', '1')
                ->orderBy('service_name', 'ASC')
                ->get();

            return view($data['view'])->with($data);
        } else {
            $data['url'] = url('admin/dashboard');
            return view('errors.401')->with($data);
        }
    }

    public function serviceActivate($id)
    {
        if (Auth::user()->is_admin == '1') {
            $userService = UserService::select('is_active')->where('id', $id)->first();
            if ($userService->is_active == '1') {
                self::serviceActivityLog($id, 0);
                $userService = UserService::where('id', $id)
                    ->update(['is_active' => '0']);
                $this->message = "User Service Deactivated Successfully";
            } else {
                UserService::where('id', $id)
                    ->whereNull('activation_date')
                    ->update(['activation_date' => date('Y-m-d H:i:s')]);
                $service_id = UserService::select('service_id')->where('id', $id)->first()->service_id;
                $PayoutService = Service::select('service_id')->where('service_id', $service_id)->where('service_slug', 'payout')->first();
                $AEPSService = Service::select('service_id')->where('service_id', $service_id)->where('service_slug', 'aeps')->first();
                $OCRService = Service::select('service_id')->where('service_id', $service_id)->where('service_slug', 'ocr')->first();
                $RechargeService = Service::select('service_id')->where('service_id', $service_id)->where('service_slug', RECHARGE_SERVICE_SLUG)->first();
                $PanService = Service::select('service_id')->where('service_id', $service_id)->where('service_slug', PAN_CARD_SERVICE_SLUG)->first();

                $validationService = Service::select('service_id')->where('service_id', $service_id)->where('service_slug', VALIDATE_SERVICE_SLUG)->first();
                $DMTService = Service::select('service_id')->where('service_id', $service_id)->where('service_slug', DMT_SERVICE_SLUG)->first();
                if (isset($PayoutService) && !empty($PayoutService->service_id)) {
                    $service_account_number = CommonHelper::newServiceWalletNumber($PayoutService->service_id);
                    $userService = UserService::select('service_account_number')->where('id', $id)->first();
                    if (empty($userService->service_account_number)) {
                        $userService = UserService::where('id', $id)->update(['is_active' => '1', 'service_account_number' => $service_account_number]);
                    } else {
                        $userService = UserService::where('id', $id)->update(['is_active' => '1']);
                    }
                } elseif (isset($AEPSService) && !empty($AEPSService->service_id)) {
                    $service_account_number = CommonHelper::newServiceWalletNumber($AEPSService->service_id);
                    $userService = UserService::select('service_account_number')->where('id', $id)->first();
                    if (empty($userService->service_account_number)) {
                        $userService = UserService::where('id', $id)->update(['is_active' => '1', 'service_account_number' => $service_account_number]);
                    } else {
                        $userService = UserService::where('id', $id)->update(['is_active' => '1']);
                    }
                } elseif (isset($DMTService) && !empty($DMTService->service_id)) {
                    $service_account_number = CommonHelper::newServiceWalletNumber($DMTService->service_id);
                    $userService = UserService::select('service_account_number')->where('id', $id)->first();
                    if (empty($userService->service_account_number)) {
                        $userService = UserService::where('id', $id)->update(['is_active' => '1', 'service_account_number' => $service_account_number]);
                    } else {
                        $userService = UserService::where('id', $id)->update(['is_active' => '1']);
                    }
                } else if (!empty($validationService->service_id)) {
                    $service_account_number = CommonHelper::newServiceWalletNumber($validationService->service_id);
                    $userService = UserService::select('service_account_number')->where('id', $id)->first();
                    if (empty($userService->service_account_number)) {
                        $userService = UserService::where('id', $id)->update(['is_active' => '1', 'service_account_number' => $service_account_number]);
                    } else {
                        $userService = UserService::where('id', $id)->update(['is_active' => '1']);
                    }
                } elseif (isset($RechargeService) && !empty($RechargeService->service_id)) {
                    $service_account_number = CommonHelper::newServiceWalletNumber($RechargeService->service_id);
                    $userService = UserService::select('service_account_number')->where('id', $id)->first();
                    if (empty($userService->service_account_number)) {
                        $userService = UserService::where('id', $id)->update(['is_active' => '1', 'service_account_number' => $service_account_number]);
                    } else {
                        $userService = UserService::where('id', $id)->update(['is_active' => '1']);
                    }
                } elseif (isset($PanService) && !empty($PanService->service_id)) {
                    $service_account_number = CommonHelper::newServiceWalletNumber($PanService->service_id);
                    $userService = UserService::select('service_account_number')->where('id', $id)->first();
                    if (empty($userService->service_account_number)) {
                        $userService = UserService::where('id', $id)->update(['is_active' => '1', 'service_account_number' => $service_account_number]);
                    } else {
                        $userService = UserService::where('id', $id)->update(['is_active' => '1']);
                    }
                } else {
                    $userService = UserService::where('id', $id)->update(['is_active' => '1']);
                }
                self::serviceActivityLog($id, 1);
                $this->message = "User Service Activated Successfully";
            }
        } else {
            $this->message = "User Service Activated Successfully";
        }
        $this->status = true;
        $this->modal = false;
        $this->alert = true;
        $this->redirect = false;
        return $this->populateresponse();
    }


    public function apiServiceActivate($id)
    {
        $resp = [];
        if (Auth::user()->is_admin == '1') {
            $userService = UserService::select('is_api_enable')
                ->where('id', $id)
                ->first();
            if ($userService->is_api_enable == '1') {
                UserService::where('id', $id)->update(['is_api_enable' => '0']);
                // $this->message = "User API Service Deactivated Successfully";
            } else {
                UserService::where('id', $id)->update(['is_api_enable' => '1']);
                // $this->message = "User API Service Activated Successfully";
            }
            $resp['status'] = 'success';
            $resp['message'] = 'Api Service updated successfully.';
        } else {
            $resp['status'] = 'failed';
            $resp['message'] = 'Un authorized user logged in.';
        }
        return $resp;
    }


    public function webServiceActivate($id)
    {
        if (Auth::user()->is_admin == '1') {
            $userService = UserService::select('is_web_enable')
                ->where('id', $id)
                ->first();
            if ($userService->is_web_enable == '1') {
                UserService::where('id', $id)->update(['is_web_enable' => '0']);
                // $this->message = "User API Service Deactivated Successfully";
            } else {
                UserService::where('id', $id)->update(['is_web_enable' => '1']);
                // $this->message = "User API Service Activated Successfully";
            }
            $resp['status'] = 'success';
            $resp['message'] = 'Web Service updated successfully.';
        } else {
            $resp['status'] = 'failed';
            $resp['message'] = 'Un authorized user logged in.';
        }
        return $resp;
    }

    public function upiMerchant()
    {
        if (Auth::user()->hasRole('super-admin') || Auth::user()->hasRole('support') || Auth::user()->hasRole('accountant')) {
            $data['page_title'] = "Upi Merchant List";
            $data['site_title'] = "Upi Merchant";
            $data['view'] = ADMIN . '/' . ".reports.merchant";

            $data['users'] = DB::table('users')
                ->where('is_admin', '0')
                ->orderBy('name', 'asc')
                ->get();

            return view($data['view'])->with($data);
        } else {
            $data['url'] = url('admin/dashboard');
            return view('errors.401')->with($data);
        }
    }

    public function upiCallback()
    {
        if (Auth::user()->hasRole('super-admin') || Auth::user()->hasRole('support') || Auth::user()->hasRole('accountant')) {
            $data['page_title'] = "Upi Callback List";
            $data['site_title'] = "Upi Callback";
            $data['view'] = ADMIN . '/' . ".reports.callback";

            $data['users'] = DB::table('users')
                ->where('is_admin', '0')
                ->orderBy('name', 'asc')
                ->get();

            return view($data['view'])->with($data);
        } else {
            $data['url'] = url('admin/dashboard');
            return view('errors.401')->with($data);
        }
    }


    /**
     * Smart Collect
     */
    public function scMerchants()
    {
        if (Auth::user()->hasRole('super-admin') || Auth::user()->hasRole('support') || Auth::user()->hasRole('accountant')) {
            $data['page_title'] = "Smart Collect Merchant List";
            $data['site_title'] = "Smart Collect Merchant";
            $data['view'] = ADMIN . '/' . ".reports.smart_collect_merchants";

            $data['users'] = DB::table('users')
                ->where('is_admin', '0')
                ->orderBy('name', 'asc')
                ->get();

            return view($data['view'])->with($data);
        } else {
            $data['url'] = url('admin/dashboard');
            return view('errors.401')->with($data);
        }
    }

    public function scCallbacks()
    {
        if (Auth::user()->hasRole('super-admin') || Auth::user()->hasRole('support') || Auth::user()->hasRole('accountant')) {
            $data['page_title'] = "Smart Collect Callback List";
            $data['site_title'] = "Smart Collect Callbacks";
            $data['view'] = ADMIN . '/' . ".reports.smart_collect_callback";

            $data['users'] = DB::table('users')
                ->where('is_admin', '0')
                ->orderBy('name', 'asc')
                ->get();

            return view($data['view'])->with($data);
        } else {
            $data['url'] = url('admin/dashboard');
            return view('errors.401')->with($data);
        }
    }


    /**
     * VAN Callback View
     */
    public function vanCallback()
    {
        if (Auth::user()->hasRole('super-admin') || Auth::user()->hasRole('support') || Auth::user()->hasRole('accountant')) {
            $data['page_title'] = "VAN Callback List";
            $data['site_title'] = "VAN Callback";
            $data['view'] = ADMIN . ".reports.van_callback";

            $data['users'] = DB::table('users')
                ->where('is_admin', '0')
                ->orderBy('name', 'asc')
                ->get();

            return view($data['view'])->with($data);
        } else {
            $data['url'] = url('admin/dashboard');
            return view('errors.401')->with($data);
        }
    }

    /**
     * Virtual Account (VA)
     */
    public function vaClients()
    {
        if (Auth::user()->hasRole('super-admin') || Auth::user()->hasRole('support') || Auth::user()->hasRole('accountant')) {
            $data['page_title'] = "Virtual Account Clients List";
            $data['site_title'] = "Virtual Account Clients";
            $data['view'] = ADMIN . '/' . ".reports.virtual_account_clients";

            $data['users'] = DB::table('users')
                ->where('is_admin', '0')
                ->orderBy('name', 'asc')
                ->get();

            return view($data['view'])->with($data);
        } else {
            $data['url'] = url('admin/dashboard');
            return view('errors.401')->with($data);
        }
    }


    /**
     * * Virtual Account (VA) Callback
     */
    public function vaCallbacks()
    {
        if (Auth::user()->hasRole('super-admin') || Auth::user()->hasRole('support') || Auth::user()->hasRole('accountant')) {
            $data['page_title'] = "Virtual Account Callback List";
            $data['site_title'] = "Virtual Account Callbacks";
            $data['view'] = ADMIN . '/' . ".reports.virtual_account_callbacks";

            $data['users'] = DB::table('users')
                ->where('is_admin', '0')
                ->orderBy('name', 'asc')
                ->get();

            return view($data['view'])->with($data);
        } else {
            $data['url'] = url('admin/dashboard');
            return view('errors.401')->with($data);
        }
    }


    /**
     * Validation Suite Transactions
     */
    public function validationSuiteTransactions()
    {
        if (Auth::user()->hasRole('super-admin') || Auth::user()->hasRole('support') || Auth::user()->hasRole('accountant')) {
            $data['page_title'] = "Validation Suite Transactions";
            $data['site_title'] = "Validation Suite Transactions";
            $data['view'] = ADMIN . '/' . ".reports.validation_suite_transactions";

            $data['users'] = DB::table('users')
                ->where('is_admin', '0')
                ->orderBy('name', 'asc')
                ->get();

            return view($data['view'])->with($data);
        } else {
            $data['url'] = url('admin/dashboard');
            return view('errors.401')->with($data);
        }
    }


    /**
     * Webhook Logs
     */
    public function viewWebhookLogs()
    {
        if (Auth::user()->hasRole('super-admin') || Auth::user()->hasRole('support') || Auth::user()->hasRole('log')) {
            $data['page_title'] = "Webhook Logs";
            $data['site_title'] = "Webhook Logs";
            $data['view'] = ADMIN . ".reports.webhook_logs";
            return view($data['view'])->with($data);
        } else {
            $data['url'] = url('admin/dashboard');
            return view('errors.401')->with($data);
        }
    }


    /**
     * User API Logs
     */
    public function viewUserApiLogs()
    {
        if (Auth::user()->hasRole('super-admin') || Auth::user()->hasRole('support') || Auth::user()->hasRole('log')) {
            $data['page_title'] = "User API Logs";
            $data['site_title'] = "User API Logs";
            $data['view'] = ADMIN . ".reports.users_api_logs";

            $data['userList'] = DB::table('users')
                ->select('id', 'name', 'email')
                ->where('is_admin', '0')
                ->get();

            $data['serviceList'] = [
                'aeps' => 'AEPS',
                'init' => 'AEPS SDK',
                'payout' => 'Payout',
                'smart_collect' => 'Smart Collect',
                'transaction' => 'AEPS Transactions',
                'upi_collect' => 'UPI Stack',
                'va' => 'Virtual Account',
                'validate' => 'Validation Suite'
            ];

            return view($data['view'])->with($data);
        } else {
            $data['url'] = url('admin/dashboard');
            return view('errors.401')->with($data);
        }
    }

    /**
     * User API Logs
     */
    public function getUserApiLogs(Request $request)
    {
        if (Auth::user()->hasRole('super-admin') || Auth::user()->hasRole('support') || Auth::user()->hasRole('log')) {
            $validator = Validator::make(
                $request->all(),
                [
                    'user_id' => 'nullable|min:1|max:99999999',
                    // 'service_type' => 'required'
                ]
            );

            if ($validator->fails()) {
                $message = json_decode(json_encode($validator->errors()), true);
                return ResponseHelper::missing('Some params are missing.', $message);
            }

            return (new UserApiLogHelper())->getRecords($request);
        } else {
            $data['url'] = url('admin/dashboard');
            return view('errors.401')->with($data);
        }
    }


    /**
     * View Aeps Transactions
     */
    public function viewAepsTransactionDetails($aadhaar)
    {
        if (Auth::user()->hasRole('super-admin') || Auth::user()->hasRole('support')) {
            $data['page_title'] = "Aeps Transactions Details";
            $data['site_title'] = "Aeps Transactions Details";
            $data['view'] = ADMIN . ".reports.aeps_transactions_details";
            $data['aadhaarNo'] = $aadhaar;
            $data['userList'] = DB::table('users')
                ->select('id', 'name', 'email')
                ->where('is_admin', '0')
                ->get();

            return view($data['view'])->with($data);
        } else {
            $data['url'] = url('admin/dashboard');
            return view('errors.401')->with($data);
        }
    }

    /**
     * User API Logs
     */
    public function getAepsTransactionsDetails(Request $request)
    {
        if (Auth::user()->hasRole('super-admin') || Auth::user()->hasRole('support')) {
            $validator = Validator::make(
                $request->all(),
                [
                    'user_id' => 'nullable|min:1|max:99999999',
                ]
            );

            if ($validator->fails()) {
                $message = json_decode(json_encode($validator->errors()), true);
                return ResponseHelper::missing('Some params are missing.', $message);
            }

            return (new AepsReconsilation())->getRecords($request, 'details');
        } else {
            $data['url'] = url('admin/dashboard');
            return view('errors.401')->with($data);
        }
    }


    /**
     * View Aeps Transactions
     */
    public function viewAepsTransactions()
    {
        if (Auth::user()->hasRole('super-admin') || Auth::user()->hasRole('support')) {
            $data['page_title'] = "Aeps Transactions";
            $data['site_title'] = "Aeps Transactions";
            $data['view'] = ADMIN . ".reports.aeps_transactions_list";

            $data['userList'] = DB::table('users')
                ->select('id', 'name', 'email')
                ->where('is_admin', '0')
                ->get();

            return view($data['view'])->with($data);
        } else {
            $data['url'] = url('admin/dashboard');
            return view('errors.401')->with($data);
        }
    }

    /**
     * User API Logs
     */
    public function getAepsTransactions(Request $request)
    {
        if (Auth::user()->hasRole('super-admin') || Auth::user()->hasRole('support')) {
            $validator = Validator::make(
                $request->all(),
                [
                    'user_id' => 'nullable|min:1|max:99999999',
                ]
            );

            if ($validator->fails()) {
                $message = json_decode(json_encode($validator->errors()), true);
                return ResponseHelper::missing('Some params are missing.', $message);
            }

            return (new AepsReconsilation())->getRecords($request, 'list');
        } else {
            $data['url'] = url('admin/dashboard');
            return view('errors.401')->with($data);
        }
    }
    /**
     * User API Logs
     */
    public function getWebhookLogs(Request $request)
    {
        if (Auth::user()->hasRole('super-admin') || Auth::user()->hasRole('support') || Auth::user()->hasRole('log')) {

            return (new WebhookLogHelper())->getRecords($request);
        } else {
            $data['url'] = url('admin/dashboard');
            return view('errors.401')->with($data);
        }
    }

    /**
     * User API Logs
     */
    public function viewApiLogs()
    {
        if (Auth::user()->hasRole('super-admin') || Auth::user()->hasRole('support') || Auth::user()->hasRole('log')) {
            $data['page_title'] = "API Logs";
            $data['site_title'] = "API Logs";
            $data['view'] = ADMIN . ".reports.api_logs";

            $data['userList'] = DB::table('users')
                ->select('id', 'name', 'email')
                ->where('is_admin', '0')
                ->get();

            $data['method'] = DB::table('apilogs')
                ->select('method')
                ->groupBy('method')
                ->get();
            $data['modal'] = DB::table('apilogs')
                ->select('modal')
                ->groupBy('modal')
                ->get();

            return view($data['view'])->with($data);
        } else {
            $data['url'] = url('admin/dashboard');
            return view('errors.401')->with($data);
        }
    }

    /**
     * User API Logs
     */
    public function getApiLogs(Request $request)
    {
        if (Auth::user()->hasRole('super-admin') || Auth::user()->hasRole('support') || Auth::user()->hasRole('log')) {
            $validator = Validator::make(
                $request->all(),
                [
                    'user_id' => 'nullable|min:1|max:99999999',
                    // 'service_type' => 'required'
                ]
            );

            if ($validator->fails()) {
                $message = json_decode(json_encode($validator->errors()), true);
                return ResponseHelper::missing('Some params are missing.', $message);
            }

            return (new MApiLogHelper())->getRecords($request);
        } else {
            $data['url'] = url('admin/dashboard');
            return view('errors.401')->with($data);
        }
    }


    public function profile()
    {
        $data['page_title'] = "My Profile";
        $data['site_title'] = "My Profile";
        $data['view'] = ADMIN . '/' . ".profile";
        return view($data['view'])->with($data);
    }
    public function userProfiles($id)
    {
        // dd($id);
        $data['page_title'] = "User Profile";
        $data['site_title'] = "User Profile";
        $data['userData'] = User::find($id);
        // dd($data['userData']);
        $data['user_id'] = $id;
        $data['view'] = ADMIN . '/' . ".user_profile";
        $data['site_title'] =  "Profile";
        $data['page_title'] =  "Profile";
        $data['page_url'] = "profile";
        $data['business_category'] = BusinessCategory::where('is_active', '1')->where('is_parent', '1')->get();
        $data['state_list'] =  State::where('is_active', '1')->get();
        $data['business_info'] = $businessInfo = BusinessInfo::where('user_id',$id)->first();
        $data['account_manager'] =  AccountManager::where('is_active', '1')->get();
        $data['resellers'] = DB::table("resellers")->where('status', '1')->select('id', 'name')->get();
        $data['user_config'] =  UserConfig::select('is_sdk_enable', 'app_id', 'app_cred_created_at', 'is_matm_enable', 'matm_app_id', 'matm_app_cred_created_at')->where(['user_id' => $id])->first();
        $data['account_manager_data'] = "";
        if (isset($businessInfo->acc_manager_id)) {
            $data['account_manager_data'] =  AccountManager::where('is_active', '1')->where('id', $businessInfo->acc_manager_id)->first();
        }
        $data['account_coordinator_data'] = "";
        if (isset($businessInfo->acc_coordinator_id)) {
            $data['account_coordinator_data'] =  AccountManager::where('is_active', '1')->where('id', $businessInfo->acc_coordinator_id)->first();
        }
        $data['services'] =  UserService::select('user_services.id', 'global_services.service_id', 'global_services.service_name')
            ->leftJoin('global_services', 'global_services.service_id', 'user_services.service_id')
            ->where('user_id', Auth::user()->id)->where('user_services.is_active', '1')->get();

        // $data['ebVan'] = DB::table('user_van_accounts')
        //     ->where('root_type', 'eb_van')
        //     ->where('user_id', Auth::user()->id)
        //     ->first();

        // $data['razVan'] = DB::table('user_van_accounts')
        //     ->where('root_type', 'raz_van')
        //     ->where('user_id', Auth::user()->id)
        //     ->first();

        $data['obVan'] = DB::table('user_van_accounts')
            ->where('root_type', OPEN_BANK_VAN)
            ->where('user_id', $id)
            ->first();


        //check van info
        // if (!empty($businessInfo->van)) {
        //     $data['van_info']['van'] = ($businessInfo->van);
        //     $data['van_info']['ifsc'] = ($businessInfo->van_ifsc);
        //     $data['van_info']['van_2'] = ($businessInfo->van_2);
        //     $data['van_info']['ifsc_2'] = ($businessInfo->van_2_ifsc);
        //     $data['van_info']['id'] = ($businessInfo->van_acc_id);
        //     $data['van_info']['status'] = ($businessInfo->van_status);
        // }

        $data['isActiveTab'] = '';

        $data['userBankInfos'] = DB::table("user_bank_infos")
            ->select('*')
            ->where('user_id', $id)
            ->orderBy('id', 'ASC')
            ->get();

        $data['webhook'] =  Webhook::where('user_id', $id)->first();
        return view(ADMIN . '/' . ".user_profile", compact('data'))->with($data);
        // if (isset($data['userData'])) {
        //     return view($data['view'])->with($data);
        // } else {
        //     $data['url'] = url('admin/dashboard');
        //     return view('errors.401')->with($data);
        // }
    }
    public function userprofile($id)
    {
        if (Auth::user()->hasRole('super-admin') && is_numeric($id)) {
            $data['page_title'] = "User Profile";
            $data['site_title'] = "User Profile";
            $data['view'] = ADMIN . '/' . ".reports.user_profile";
            $data['obVanAccount'] = DB::table('user_van_accounts')
                ->where('root_type', OPEN_BANK_VAN)
                ->where('user_id', $id)
                ->first();

            $data['userConfig'] = DB::table('user_config')
                ->where('user_id', $id)
                ->first();

            $data['userBankInfos'] = DB::table("user_bank_infos")
                ->select('*')
                ->where('user_id', $id)
                ->orderBy('id', 'ASC')
                ->get();

            $data['userService'] = UserService::leftJoin('global_services', 'global_services.service_id', 'user_services.service_id')
                ->select('*', 'user_services.created_at', 'user_services.is_active as service_active_check', 'user_services.id as servicePkId')
                ->where('user_services.user_id', $id)->get()->toArray();
            $data['userBusinessProfile'] = BusinessInfo::select('*')->where('user_id', $id)->first();
            $data['business_category'] = BusinessCategory::where('is_active', '1')->where('is_parent', '1')->get();
            $data['state_list'] = State::where('is_active', '1')->get();
            $data['userData'] = User::find($id);
            $data['resellers'] = DB::table('resellers')->select('id','name')->where('status', '1')->get();
            
            $data['user_id'] = $id;
            $data['account_manager'] = $data['account_managerNew'] = AccountManager::where('is_active', '1')->get()->toArray();
            $data['account_manager_data'] = "";
            if (isset($businessInfo->acc_manager_id)) {
                $data['account_manager_data'] = AccountManager::where('is_active', '1')->where('id', $businessInfo->acc_manager_id)->first();
            }
            $data['account_coordinator_data'] = "";
            if (isset($businessInfo->acc_coordinator_id)) {
                $data['account_coordinator_data'] = AccountManager::where('is_active', '1')->where('id', $businessInfo->acc_coordinator_id)->first();
            }
            $query = 'SELECT  sum(amount) as totAmount FROM (SELECT DISTINCT customer_ref_id, amount, created_at FROM upi_callbacks where user_id=' . $id . ' group by customer_ref_id) t ';
            $callbackAmount = DB::select($query);
            $data['upiCallbackData'] = $callbackAmount[0];
            $data['payoutSelectedValue'] = '';
            $data['smartCollectSelectedValue'] = '';
            $data['upiSelectedValue'] = '';
            $data['aepsSelectedValue'] = '';
            if (isset($data['userService'])) {
                $payoutArrayValue = [];
                $payoutWebArrayValue = [];
                $payoutApiArrayValue = [];
                $smartCollectArrayValue = [];
                $smartCollectWebArrayValue = [];
                $smartCollectApiArrayValue = [];
                $aepsArrayValue = [];
                $aepsWebArrayValue = [];
                $aepsApiArrayValue = [];
                $upiArrayValue = [];
                $upiWebArrayValue = [];
                $upiApiArrayValue = [];
                foreach ($data['userService'] as $UserService) {
                    // Payout selected value
                    if ($UserService['service_id'] == PAYOUT_SERVICE_ID) {
                        if (isset($UserService['web_value']) && !empty($UserService['web_value'])) {
                            $array = explode(",", $UserService['web_value']);
                            $payoutWebArrayValue = explode(",", ("web-" . implode(",web-", $array)));
                        }
                        if (isset($UserService['api_value']) && !empty($UserService['api_value'])) {
                            $array = explode(",", $UserService['api_value']);
                            $payoutApiArrayValue = explode(",", ("api-" . implode(",api-", $array)));
                        }

                        if (count($payoutWebArrayValue) && count($payoutApiArrayValue)) {
                            $payoutArrayValue = array_merge($payoutWebArrayValue, $payoutApiArrayValue, array('web'), array('api'));
                        } else if (count($payoutWebArrayValue)) {
                            $payoutArrayValue = array_merge($payoutWebArrayValue, array('web'));
                        } else if (count($payoutApiArrayValue)) {
                            $payoutArrayValue = array_merge($payoutApiArrayValue, array('api'));
                        }
                        if (isset($payoutArrayValue) && count($payoutArrayValue))
                            ; {
                            $data['payoutSelectedValue'] = "'" . implode("','", $payoutArrayValue) . "'";
                        }
                    }

                    // Smart Collect selected value
                    if ($UserService['service_id'] == AUTO_COLLECT_SERVICE_ID) {
                        if (isset($UserService['web_value']) && !empty($UserService['web_value'])) {
                            $array = explode(",", $UserService['web_value']);
                            $smartCollectWebArrayValue = explode(",", ("web-" . implode(",web-", $array)));
                        }
                        if (isset($UserService['api_value']) && !empty($UserService['api_value'])) {
                            $array = explode(",", $UserService['api_value']);
                            $smartCollectApiArrayValue = explode(",", ("api-" . implode(",api-", $array)));
                        }

                        if (count($smartCollectWebArrayValue) && count($smartCollectApiArrayValue)) {
                            $smartCollectArrayValue = array_merge($smartCollectWebArrayValue, $smartCollectApiArrayValue, array('web'), array('api'));
                        } else if (count($smartCollectWebArrayValue)) {
                            $smartCollectArrayValue = array_merge($smartCollectWebArrayValue, array('web'));
                        } else if (count($smartCollectApiArrayValue)) {
                            $smartCollectArrayValue = array_merge($smartCollectApiArrayValue, array('api'));
                        }
                        if (isset($smartCollectArrayValue) && count($smartCollectArrayValue))
                            ; {
                            $data['smartCollectSelectedValue'] = "'" . implode("','", $smartCollectArrayValue) . "'";
                        }
                    }

                    // AEPS selected value
                    if ($UserService['service_id'] == AEPS_SERVICE_ID) {
                        if (isset($UserService['web_value']) && !empty($UserService['web_value'])) {
                            $aepsICICIWebArrayValue = [];
                            $aepsSBMWebArrayValue = [];
                            $aepsPaytmWebArrayValue = [];
                            $aepsAirtelWebArrayValue = [];
                            $array = json_decode($UserService['web_value'], true);
                            if (isset($array['icici'])) {
                                $iciciArary = explode(",", $array['icici']);
                                $aepsICICIWebArrayValue = explode(",", ("web-icici-" . implode(",web-icici-", $iciciArary)));
                                $aepsICICIWebArrayValue = array_merge($aepsICICIWebArrayValue, array('web-icici'));
                            }

                            if (isset($array['sbm'])) {
                                $sbmArary = explode(",", $array['sbm']);
                                $aepsSBMWebArrayValue = explode(",", ("web-sbm-" . implode(",web-sbm-", $sbmArary)));
                                $aepsSBMWebArrayValue = array_merge($aepsSBMWebArrayValue, array('web-sbm'));
                            }

                            if (isset($array['paytm'])) {
                                $paytmArary = explode(",", $array['paytm']);
                                $aepsPaytmWebArrayValue = explode(",", ("web-paytm-" . implode(",web-paytm-", $paytmArary)));
                                $aepsPaytmWebArrayValue = array_merge($aepsPaytmWebArrayValue, array('web-paytm'));
                            }

                            if (isset($array['airtel'])) {
                                $airtelArary = explode(",", $array['airtel']);
                                $aepsAirtelWebArrayValue = explode(",", ("web-airtel-" . implode(",web-airtel-", $airtelArary)));
                                $aepsAirtelWebArrayValue = array_merge($aepsAirtelWebArrayValue, array('web-airtel'));
                            }
                            $aepsWebArrayValue = array_merge($aepsICICIWebArrayValue, $aepsSBMWebArrayValue, $aepsPaytmWebArrayValue, $aepsAirtelWebArrayValue);
                        }
                        if (isset($UserService['api_value']) && !empty($UserService['api_value'])) {
                            $aepsICICIApiArrayValue = [];
                            $aepsSBMApiArrayValue = [];
                            $aepsPaytmApiArrayValue = [];
                            $aepsAirtelApiArrayValue = [];
                            $array = json_decode($UserService['api_value'], true);
                            if (isset($array['icici'])) {
                                $iciciArary = explode(",", $array['icici']);
                                $aepsICICIApiArrayValue = explode(",", ("api-icici-" . implode(",api-icici-", $iciciArary)));
                                $aepsICICIApiArrayValue = array_merge($aepsICICIApiArrayValue, array('api-icici'));
                            }

                            if (isset($array['sbm'])) {
                                $sbmArary = explode(",", $array['sbm']);
                                $aepsSBMApiArrayValue = explode(",", ("api-sbm-" . implode(",api-sbm-", $sbmArary)));
                                $aepsSBMApiArrayValue = array_merge($aepsSBMApiArrayValue, array('api-sbm'));
                            }

                            if (isset($array['paytm'])) {
                                $paytmArary = explode(",", $array['paytm']);
                                $aepsPaytmApiArrayValue = explode(",", ("api-paytm-" . implode(",api-paytm-", $paytmArary)));
                                $aepsPaytmApiArrayValue = array_merge($aepsPaytmApiArrayValue, array('api-paytm'));
                            }

                            if (isset($array['airtel'])) {
                                $airtelArary = explode(",", $array['airtel']);
                                $aepsAirtelApiArrayValue = explode(",", ("api-airtel-" . implode(",api-airtel-", $airtelArary)));
                                $aepsAirtelApiArrayValue = array_merge($aepsAirtelApiArrayValue, array('api-airtel'));
                            }
                            $aepsApiArrayValue = array_merge($aepsICICIApiArrayValue, $aepsSBMApiArrayValue, $aepsPaytmApiArrayValue, $aepsAirtelApiArrayValue);
                        }
                        $aepsArrayValue = array_merge($aepsWebArrayValue, $aepsApiArrayValue);
                        if (isset($aepsArrayValue) && count($aepsArrayValue))
                            ; {
                            $data['aepsSelectedValue'] = "'" . implode("','", $aepsArrayValue) . "'";
                        }
                    }
                    // UPI selected value
                    if ($UserService['service_id'] == UPI_SERVICE_ID) {
                        if (isset($UserService['web_value']) && !empty($UserService['web_value'])) {
                            $array = explode(",", $UserService['web_value']);
                            $upiWebArrayValue = explode(",", ("web-" . implode(",web-", $array)));
                        }
                        if (isset($UserService['api_value']) && !empty($UserService['api_value'])) {
                            $array = explode(",", $UserService['api_value']);
                            $upiApiArrayValue = explode(",", ("api-" . implode(",api-", $array)));
                        }

                        if (count($upiWebArrayValue) && count($upiApiArrayValue)) {
                            $upiArrayValue = array_merge($upiWebArrayValue, $upiApiArrayValue, array('web'), array('api'));
                        } else if (count($upiWebArrayValue)) {
                            $upiArrayValue = array_merge($upiWebArrayValue, array('web'));
                        } else if (count($upiApiArrayValue)) {
                            $upiArrayValue = array_merge($upiApiArrayValue, array('api'));
                        }
                        if (isset($upiArrayValue) && count($upiArrayValue))
                            ; {
                            $data['upiSelectedValue'] = "'" . implode("','", $upiArrayValue) . "'";
                        }
                    }
                }
            }
            $data['upiCollectData'] = DB::table('upi_collects')->select(DB::raw('sum(amount) as totAmount'))->where('status', 'success')->where('user_id', $id)->first();
            if (isset($data['userData'])) {
                return view($data['view'])->with($data);
            } else {
                $data['url'] = url('admin/dashboard');
                return view('errors.401')->with($data);
            }
        } else {
            $data['url'] = url('admin/dashboard');
            return view('errors.401')->with($data);
        }
    }

    /**
     * profileOpenBySupport
     *
     * @return void
     */
    public function profileOpenBySupport($userId)
    {
        if (Auth::user()->hasRole('support')) {

            $data['site_title'] = "Profile";
            $data['page_title'] = "Profile";
            $data['page_url'] = "profile";
            $data['business_category'] = BusinessCategory::where('is_active', '1')
                ->where('is_parent', '1')->get();
            $data['state_list'] = State::where('is_active', '1')->get();
            $data['business_info'] = $businessInfo = BusinessInfo::where('user_id', $userId)
                ->where('is_kyc_updated', '1')->first();
            if (isset($businessInfo)) {
                $data['isActiveTab'] = 'active';
                $data['account_manager'] = AccountManager::where('is_active', '1')->get();
                $data['userData'] = User::where('id', $userId)->first();
                $data['user_config'] = UserConfig::select('is_sdk_enable', 'app_id', 'app_cred_created_at', 'api_integration_id', 'web_integration_id')->where(['user_id' => $userId])->first();
                $data['account_manager_data'] = "";
                if (isset($businessInfo->acc_manager_id)) {
                    $data['account_manager_data'] = AccountManager::where('is_active', '1')->where('id', $businessInfo->acc_manager_id)->first();
                }
                $data['account_coordinator_data'] = "";
                if (isset($businessInfo->acc_coordinator_id)) {
                    $data['account_coordinator_data'] = AccountManager::where('is_active', '1')->where('id', $businessInfo->acc_coordinator_id)->first();
                }
                $data['services'] = UserService::select('user_services.id', 'global_services.service_id', 'global_services.service_name')
                    ->leftJoin('global_services', 'global_services.service_id', 'user_services.service_id')
                    ->where('user_id', $userId)->where('user_services.is_active', '1')
                    ->get();
                $data['payout_routes'] = DB::table('integrations')->select('name', 'integration_id')->where('is_active', '1')->get();
                return view(ADMIN . '/' . 'profileOpenBySupport', compact('data'))->with($data);
            } else {
                $data['url'] = url('admin/dashboard');
                return view('errors.401')->with($data);
            }
        }

        $data['url'] = url('admin/dashboard');
        return view('errors.401')->with($data);
    }
    /**
     * Undocumented function
     *
     * @param [type] $orderRefId
     * @param [type] $orderId
     * @return void
     */
    public function orderReversed(Request $request)
    {
        if (Auth::user()->hasRole('super-admin')) {
            $validation = new Validations($request);
            $validator = $validation->orderReversed();
            $validator->after(function ($validator) use ($request) {
                $Order = Order::where(['user_id' => $request->userId, 'order_ref_id' => $request->orderRefId])->first();
                if (empty($Order)) {
                    $validator->errors()->add('message', 'Order is not valid');
                } else {
                    if ($Order->status != 'processed') {
                        $validator->errors()->add('message', 'Order is not valid for reversed');
                    }
                }
            });

            if ($validator->fails()) {
                $this->message = $validator->errors();
            } else {

                $Order = DB::table('orders')
                    ->where('order_ref_id', $request->orderRefId)
                    ->select('user_id', 'batch_id')
                    ->first();
                $errorDesc = $request->remarks;
                $statusCode = '';
                $utr = '';
                $txn = CommonHelper::getRandomString('txn', false);
                $getServicePkId = DB::table('user_services')->select('id')->where('user_id', $Order->user_id)->where('service_id', PAYOUT_SERVICE_ID)->first();
                DB::select("CALL OrderStatusUpdate('" . $request->orderRefId . "', $Order->user_id, $getServicePkId->id, 'reversed', '" . $txn . "', '" . $errorDesc . "', '" . $statusCode . "','" . $utr . "', @json)");
                $results = DB::select('select @json as json');
                $response = json_decode($results[0]->json, true);
                if ($response['status'] == '1') {
                    BulkPayoutDetail::payStatusUpdate($Order->batch_id, 'reversed', $request->orderRefId, 'Order Reversed', '');
                    BulkPayout::updateStatusByBatch($Order->batch_id, array());
                    TransactionHelper::sendCallback($Order->user_id, $request->orderRefId, 'reversed');
                    $this->message = "Order Reversed Successfully";
                } else {
                    $this->message = $response['message'];
                    $this->message_object = true;
                }

                $this->status = true;
                $this->modal = true;
                $this->alert = true;
                $this->redirect = true;
                return $this->populateresponse();
            }
            return response()->json(
                $this->populate([
                    'message' => $this->message,
                    'status' => $this::FAILED_STATUS,
                    'data' => $this->message
                ])
            );
        } else {
            $this->message = array("message" => "You don't have access to perform this action.");
            $this->message_object = true;
            $this->status = $this::FAILED_STATUS;
            return $this->populateresponse();
        }
    }


    public function userList()
    {
        if (Auth::user()->hasRole('super-admin') || Auth::user()->hasRole('support') || Auth::user()->hasRole('accountant')) {
            $data['page_title'] = "User List";
            $data['site_title'] = "Users";
            $data['view'] = ADMIN . ".reports.users";

            $data['Pipe'] = DB::table('integrations')->select(DB::raw('id, name as pipeName'))->get();
            $data['serviceLists'] = DB::table('global_services')
                ->select('service_name AS title', 'service_slug AS id')
                ->where('is_active', '1')
                ->orderBy('service_name', 'ASC')
                ->get();

            $data['serviceList'] = json_encode($data['serviceLists']);
            $data['serviceListObject'] = $data['serviceLists'];
            return view($data['view'])->with($data);
        } else {
            $data['url'] = url('admin/dashboard');
            return view('errors.401')->with($data);
        }
    }


    /**
     * User Video KYC List
     */
    public function userVideoKycList()
    {
        if (Auth::user()->hasRole('super-admin') || Auth::user()->hasRole('support')) {
            $data['page_title'] = "User List";
            $data['site_title'] = "Users";
            $data['view'] = ADMIN . ".reports.user_video_kyc";

            $data['users'] = DB::table('users')
                ->select('id', 'name', 'email')
                ->whereNotNull('email_verified_at')
                ->where('is_admin', '0')
                ->orderBy('name', 'asc')
                ->get();

            return view($data['view'])->with($data);
        } else {
            $data['url'] = url('admin/dashboard');
            return view('errors.401')->with($data);
        }
    }


    /**
     * Update Video KYC Approve and Reject
     */
    public function updateVideoKycStatus(Request $request)
    {
        if (Auth::user()->hasRole('super-admin') || Auth::user()->hasRole('support')) {

            $validator = Validator::make(
                $request->all(),
                [
                    'value' => "required|in:app,rej",
                    'vid' => "required|numeric|min:1|max:9999999",
                    'remarks' => "nullable|max:250" //required_if:value,rej
                ],
                [
                    'remarks.required_if' => "The remarks field is required"
                ]
            );


            if ($validator->fails()) {
                $message = json_decode(json_encode($validator->errors()), true);
                return ResponseHelper::failed('Some params are missing.', $message);
            }

            $adminId = Auth::user()->id;

            $id = trim($request->vid);
            $status = trim($request->value);
            $remarks = trim($request->remarks);

            $kycVideo = DB::table('user_video_kyc')
                ->select('id', 'user_id')
                ->where('id', $id)
                // ->where('status', '0')
                ->first();

            if (empty($kycVideo)) {
                return ResponseHelper::failed('Invalid Video KYC ID.');
            }

            switch ($status) {
                case 'app':
                    DB::table('user_video_kyc')
                        ->where('id', $id)
                        ->update([
                            'status' => '1',
                            'remarks' => $remarks,
                            'status_changed_at' => date('Y-m-d H:i:s'),
                            'status_changed_by' => $adminId,
                            'updated_at' => date('Y-m-d H:i:s'),
                        ]);

                    DB::table('business_infos')
                        ->where('user_id', $kycVideo->user_id)
                        ->update([
                            'is_active' => '1',
                            // 'is_kyc_updated' => '1',
                            'updated_at' => date('Y-m-d H:i:s'),
                        ]);


                    DB::table('users')
                        ->where('id', $kycVideo->user_id)
                        ->update(['signup_status' => SIGNUP_STATUS_COMPLETE]);

                    return ResponseHelper::success('Video KYC Updated Successfully.');
                    break;
                case 'rej':
                    DB::table('user_video_kyc')
                        ->where('id', $id)
                        ->update([
                            'status' => '2',
                            'remarks' => $remarks,
                            'status_changed_at' => date('Y-m-d H:i:s'),
                            'status_changed_by' => $adminId,
                            'updated_at' => date('Y-m-d H:i:s'),
                        ]);

                    DB::table('business_infos')
                        ->where('user_id', $kycVideo->user_id)
                        ->update([
                            'is_active' => '0',
                            // 'is_kyc_updated' => '1',
                            'updated_at' => date('Y-m-d H:i:s'),
                        ]);

                    DB::table('users')
                        ->where('id', $kycVideo->user_id)
                        ->update(['signup_status' => SIGNUP_STATUS_VIDEO_UPLOADED]);

                    return ResponseHelper::success('Video KYC Updated Successfully.');
                    break;
            }

            return ResponseHelper::failed('Video KYC Updation Fauled.');

        } else {
            return abort(401);
        }
    }


    public function userDetails($id)
    {
        if (Auth::user()->hasRole('super-admin')) {
            $data['page_title'] = 'User Transaction';
            $data['site_title'] = "Users";
            $data['user'] = array('id' => $id);
            $data['view'] = ADMIN . ".reports.userDetails";
            return view($data['view'])->with($data);
        } else {
            $data['url'] = url('admin/dashboard');
            return view('errors.401')->with($data);
        }
    }

    public function transactionReport(Request $request)
    {
        if (Auth::user()->hasRole('super-admin') || Auth::user()->hasRole('accountant')) {
            $service = $request->service;
            switch ($service) {
                case 'payout':
                    $data['page_title'] = "Payout Report";
                    $data['site_title'] = "Users";
                    $data['view'] = ADMIN . ".reports.payout_report";
                    $data['userData'] = DB::table('users')->select(DB::raw('id,concat(name," (",email,")") as userName'))->where('is_admin', '0')->get();


                    $fromDate = $toDate = date('Y-m-d');

                    //query for total success order
                    $sqlQuery = DB::table('orders')
                        ->select(
                            DB::raw("FORMAT(sum(orders.amount+orders.fee+orders.tax),2) AS totalAmount"),
                            DB::raw("COUNT(orders.id) AS totalCount")
                        )
                        ->where('orders.status', 'processed');


                    //query for total failed counts and amount
                    $sqlQueryForFailed = DB::table('orders')
                        ->select(
                            DB::raw("FORMAT(sum(orders.amount+orders.fee+orders.tax),2) AS totalAmount"),
                            DB::raw("COUNT(orders.id) AS totalCount")
                        )
                        ->where('orders.status', 'failed');


                    //query for total reversed orders
                    $sqlQueryForReversed = DB::table('orders')
                        ->select(
                            DB::raw("FORMAT(sum(orders.amount+orders.fee+orders.tax),2) AS totalAmount"),
                            DB::raw("COUNT(orders.id) AS totalCount")
                        )
                        ->where('orders.status', 'reversed');



                    if ($request->isMethod('post')) {
                        if (!empty($request->from)) {
                            $fromDate = $request->from;
                        }

                        if (!empty($request->to)) {
                            $toDate = $request->to;
                        }

                        $sqlQuery->whereDate('orders.created_at', '>=', $fromDate)
                            ->whereDate('orders.created_at', '<=', $toDate);

                        $sqlQueryForFailed->whereDate('orders.created_at', '>=', $fromDate)
                            ->whereDate('orders.created_at', '<=', $toDate);

                        $sqlQueryForReversed->whereDate('orders.created_at', '>=', $fromDate)
                            ->whereDate('orders.created_at', '<=', $toDate);

                        if (!empty($request->user_id)) {
                            $sqlQuery->where('orders.user_id', $request->user_id);
                            $sqlQueryForFailed->where('orders.user_id', $request->user_id);
                            $sqlQueryForReversed->where('orders.user_id', $request->user_id);
                        }

                        $result['success'] = $sqlQuery->first();
                        $result['failed'] = $sqlQueryForFailed->first();
                        $result['reversed'] = $sqlQueryForReversed->first();

                        return response()->json($result);
                    }


                    $sqlQuery->whereDate('orders.created_at', '>=', $fromDate)
                        ->whereDate('orders.created_at', '<=', $toDate);

                    $sqlQueryForFailed->whereDate('orders.created_at', '>=', $fromDate)
                        ->whereDate('orders.created_at', '<=', $toDate);

                    $sqlQueryForReversed->whereDate('orders.created_at', '>=', $fromDate)
                        ->whereDate('orders.created_at', '<=', $toDate);


                    $data['totalAmount']['success'] = $sqlQuery->first();
                    $data['totalAmount']['failed'] = $sqlQueryForFailed->first();
                    $data['totalAmount']['reversed'] = $sqlQueryForReversed->first();
                    $data['dateFrom'] = $fromDate;
                    $data['dateTo'] = $toDate;

                    break;
                case 'upi':
                    $data['page_title'] = "UPI Report";
                    $data['site_title'] = "Users";
                    $data['view'] = ADMIN . ".reports.upiTransactionReport";
                    $data['userData'] = User::select(DB::raw('id,concat(name," (",email,")") as userName'))->where('is_admin', '0')->get();


                    $fromDate = $toDate = date('Y-m-d');

                    //query for total Static QR
                    $sqlQuery = DB::table('upi_collects')
                        ->select(
                            DB::raw("FORMAT(sum(amount),2) AS totalAmount"),
                            DB::raw("COUNT(id) AS totalCount")
                        )->where('status','success');


                    


                    if ($request->isMethod('post')) {
                        if (!empty($request->from)) {
                            $fromDate = $request->from;
                        }
                        

                        if (!empty($request->to)) {
                            $toDate = $request->to;
                        }

                        $sqlQuery->whereDate('created_at', '>=', $fromDate)
                            ->whereDate('created_at', '<=', $toDate);

                        


                        if (!empty($request->user_id)) {
                            $sqlQuery->where('user_id', $request->user_id);
                           
                        $result['upi_qr'] = $sqlQuery->first();
                        

                        return response()->json($result);
                        }
                    }

                    $sqlQuery->whereDate('created_at', '>=', $fromDate)
                        ->whereDate('created_at', '<=', $toDate);

                    
                    $data['totalAmount']['upi_qr'] = $sqlQuery->first();

                    //dd($data['totalAmount']['upi_qr']);
                    $data['dateFrom'] = $fromDate;
                    $data['dateTo'] = $toDate;
                    break;
                case 'van':
                    $data['page_title'] = "VAN Report";
                    $data['site_title'] = "Users";
                    $data['view'] = ADMIN . ".reports.vanTransactionReport";
                    $data['userData'] = User::select(DB::raw('id,concat(name," (",email,")") as userName'))->where('is_admin', '0')->get();


                    $fromDate = $toDate = date('Y-m-d');

                    $sqlQuery = DB::table('fund_receive_callbacks')
                        ->select(
                            DB::raw("sum(amount) AS totalAmount"),
                            DB::raw("COUNT(id) AS totalCount")
                        );

                    $sqlQueryUpi = DB::table('cf_merchants_fund_callbacks')
                        ->select(
                            DB::raw("sum(amount) AS totalAmount"),
                            DB::raw("COUNT(id) AS totalCount")
                        )->where('is_vpa', '1');

                    $sqlQueryVan = DB::table('cf_merchants_fund_callbacks')
                        ->select(
                            DB::raw("sum(amount) AS totalAmount"),
                            DB::raw("COUNT(id) AS totalCount")
                        )->where('is_vpa', '0');

                    if ($request->isMethod('post')) {
                        if (!empty($request->from)) {
                            $fromDate = $request->from;
                        }

                        if (!empty($request->to)) {
                            $toDate = $request->to;
                        }

                        $sqlQuery->whereDate('created_at', '>=', $fromDate)
                            ->whereDate('created_at', '<=', $toDate);

                        $sqlQueryUpi->whereDate('created_at', '>=', $fromDate)
                            ->whereDate('created_at', '<=', $toDate);

                        $sqlQueryVan->whereDate('created_at', '>=', $fromDate)
                            ->whereDate('created_at', '<=', $toDate);



                        if (!empty($request->user_id)) {
                            $sqlQuery->where('user_id', $request->user_id);
                            $sqlQueryUpi->where('user_id', $request->user_id);
                            $sqlQueryVan->where('user_id', $request->user_id);
                        }

                        $result = [$sqlQuery->first(), $sqlQueryUpi->first(), $sqlQueryVan->first()];

                        return response()->json($result);
                    }


                    $sqlQuery->whereDate('created_at', '>=', $fromDate)
                        ->whereDate('created_at', '<=', $toDate);

                    $sqlQueryUpi->whereDate('created_at', '>=', $fromDate)
                        ->whereDate('created_at', '<=', $toDate);

                    $sqlQueryVan->whereDate('created_at', '>=', $fromDate)
                        ->whereDate('created_at', '<=', $toDate);



                    $data['totalAmount'] = [$sqlQuery->first(), $sqlQueryUpi->first(), $sqlQueryVan->first()];
                    $data['dateFrom'] = $fromDate;
                    $data['dateTo'] = $toDate;
                    break;
                case 'aeps':
                    $data['page_title'] = "AEPS Transaction List";
                    $data['site_title'] = "Users";

                    $data['view'] = ADMIN . ".reports.aepsTransactionReport";
                    $data['userData'] = User::select(DB::raw('id,concat(name," (",email,")") as userName'))->where('is_admin', '0')->get();
                    $query = AepsTransaction::select(DB::raw('sum(transaction_amount) as totalAmount,count(id) as totalCount'))->where('status', 'success');
                    $totalAmountGroupBy = AepsTransaction::select(DB::raw('sum(transaction_amount) as totalAmount,count(id) as totalCount, route_type'))
                        ->where(['transaction_type' => 'cw', 'status' => 'success'])
                        ->groupBy('route_type');
                    $totalCommissionAmountGroupBy = AepsTransaction::select(DB::raw('sum(commission) as totalCommissionAmount,count(id) as totalCommissionCount'))
                        ->whereIn('transaction_type', ['ms', 'cw'])
                        ->where('status', 'success');
                    $totalTrnTypeCount = AepsTransaction::select(DB::raw('count(id) as trnCount, transaction_type'))
                        ->whereIn('transaction_type', ['be', 'ms', 'cw'])
                        ->where('status', 'success')
                        ->groupBy('transaction_type');
                    $merchantCode = AepsTransaction::select(DB::raw('count(DISTINCT  merchant_code) as merchantCount'))->where('status', 'success');
                    if ($request->isMethod('post')) {

                        if ((isset($request->from) && !empty($request->from)) && (isset($request->to) && !empty($request->to))) {
                            if ($request->from == $request->to) {
                                $merchantCode->whereDate('created_at', '=', Carbon::createFromFormat('Y-m-d', $request->from)->format('Y-m-d'));
                                $query->whereDate('created_at', '=', Carbon::createFromFormat('Y-m-d', $request->from)->format('Y-m-d'));
                                $totalAmountGroupBy->whereDate('created_at', '=', Carbon::createFromFormat('Y-m-d', $request->from)->format('Y-m-d'));
                                $totalCommissionAmountGroupBy->whereDate('created_at', '=', Carbon::createFromFormat('Y-m-d', $request->from)->format('Y-m-d'));
                                $totalTrnTypeCount->whereDate('created_at', '=', Carbon::createFromFormat('Y-m-d', $request->from)->format('Y-m-d'));

                            } else {

                                $merchantCode->whereBetween('created_at', [
                                    Carbon::createFromFormat('Y-m-d', $request->from)
                                        ->format('Y-m-d'), Carbon::createFromFormat('Y-m-d', $request->to)->addDay(1)->format('Y-m-d')
                                ]);
                                $totalAmountGroupBy->whereBetween('created_at', [
                                    Carbon::createFromFormat('Y-m-d', $request->from)
                                        ->format('Y-m-d'), Carbon::createFromFormat('Y-m-d', $request->to)->addDay(1)->format('Y-m-d')
                                ]);
                                $totalCommissionAmountGroupBy->whereBetween('created_at', [
                                    Carbon::createFromFormat('Y-m-d', $request->from)
                                        ->format('Y-m-d'), Carbon::createFromFormat('Y-m-d', $request->to)->addDay(1)->format('Y-m-d')
                                ]);
                                $totalTrnTypeCount->whereBetween('created_at', [
                                    Carbon::createFromFormat('Y-m-d', $request->from)
                                        ->format('Y-m-d'), Carbon::createFromFormat('Y-m-d', $request->to)->addDay(1)->format('Y-m-d')
                                ]);
                                $query->whereBetween('created_at', [
                                    Carbon::createFromFormat('Y-m-d', $request->from)
                                        ->format('Y-m-d'), Carbon::createFromFormat('Y-m-d', $request->to)->addDay(1)->format('Y-m-d')
                                ]);
                            }
                        }
                        if (isset($request->user_id) && !empty($request->user_id)) {
                            $query->where('user_id', $request->user_id);
                            $merchantCode->where('user_id', $request->user_id);
                            $totalAmountGroupBy->where('user_id', $request->user_id);
                            $totalCommissionAmountGroupBy->where('user_id', $request->user_id);
                            $totalTrnTypeCount->where('user_id', $request->user_id);
                        }

                        $result = array($query->first(), $merchantCode->first(), $totalAmountGroupBy->get(), $totalCommissionAmountGroupBy->first(), $totalTrnTypeCount->get());
                        echo json_encode($result);
                        exit;
                    } else {
                        $request->from = date('Y-m-d');
                        $request->to = date('Y-m-d');
                        if ((isset($request->from) && !empty($request->from)) && (isset($request->to) && !empty($request->to))) {
                            if ($request->from == $request->to) {
                                $merchantCode->whereDate('created_at', '=', Carbon::createFromFormat('Y-m-d', $request->from)->format('Y-m-d'));
                                $query->whereDate('created_at', '=', Carbon::createFromFormat('Y-m-d', $request->from)->format('Y-m-d'));
                                $totalAmountGroupBy->whereDate('created_at', '=', Carbon::createFromFormat('Y-m-d', $request->from)->format('Y-m-d'));
                                $totalCommissionAmountGroupBy->whereDate('created_at', '=', Carbon::createFromFormat('Y-m-d', $request->from)->format('Y-m-d'));
                                $totalTrnTypeCount->whereDate('created_at', '=', Carbon::createFromFormat('Y-m-d', $request->from)->format('Y-m-d'));

                            } else {
                                $merchantCode->whereBetween('created_at', [
                                    Carbon::createFromFormat('Y-m-d', $request->from)
                                        ->format('Y-m-d'), Carbon::createFromFormat('Y-m-d', $request->to)->addDay(1)->format('Y-m-d')
                                ]);
                                $query->whereBetween('created_at', [
                                    Carbon::createFromFormat('Y-m-d', $request->from)
                                        ->format('Y-m-d'), Carbon::createFromFormat('Y-m-d', $request->to)->addDay(1)->format('Y-m-d')
                                ]);
                                $totalAmountGroupBy->whereBetween('created_at', [
                                    Carbon::createFromFormat('Y-m-d', $request->from)
                                        ->format('Y-m-d'), Carbon::createFromFormat('Y-m-d', $request->to)->addDay(1)->format('Y-m-d')
                                ]);
                                $totalCommissionAmountGroupBy->whereBetween('created_at', [
                                    Carbon::createFromFormat('Y-m-d', $request->from)
                                        ->format('Y-m-d'), Carbon::createFromFormat('Y-m-d', $request->to)->addDay(1)->format('Y-m-d')
                                ]);
                                $totalTrnTypeCount->whereBetween('created_at', [
                                    Carbon::createFromFormat('Y-m-d', $request->from)
                                        ->format('Y-m-d'), Carbon::createFromFormat('Y-m-d', $request->to)->addDay(1)->format('Y-m-d')
                                ]);

                            }
                        }
                    }
                    // dd($query->first(), $merchantCode->first(), $totalAmountGroupBy->get());>
                    $data['totalAmount'] = array($query->first(), $merchantCode->first(), $totalAmountGroupBy->get(), $totalCommissionAmountGroupBy->first(), $totalTrnTypeCount->get());
                    break;

                case 'daybook':
                    $data['page_title'] = "AEPS Transaction List";
                    $data['site_title'] = "Users";
                    $data['view'] = ADMIN . ".reports.dayBookReport";
                    $data['userData'] = User::select(DB::raw('id,concat(name," (",email,")") as userName'))->where('is_admin', '0')->get();
                    break;
                case 'reconsilation':
                    $data['page_title'] = "Report";
                    $data['site_title'] = "Users";
                    $data['view'] = ADMIN . ".reports.reconsilationReport";
                    $data['userData'] = User::select(DB::raw('id,concat(name," (",email,")") as userName'))->where('is_admin', '0')->get();
                    break;
                case 'recharge':
                    $data['page_title'] = "Recharge Transaction List";
                    $data['site_title'] = "Users";

                    $data['view'] = ADMIN . ".reports.rechargeTransactionReport";
                    $data['userData'] = User::select(DB::raw('id,concat(name," (",email,")") as userName'))->where('is_admin', '0')->get();
                    $query = Recharge::select(DB::raw('sum(amount) as totalAmount,count(id) as totalCount'))->whereIn('status', ['processed', 'processing']);
                    $totalCommissionAmountGroupBy = Recharge::select(DB::raw('sum(commission) as totalCommissionAmount,count(id) as totalCommissionCount'))
                        //->whereIn('transaction_type', ['ms','cw'])
                        ->whereIn('status', ['processed', 'pending']);

                    if ($request->isMethod('post')) {

                        if ($request->from == $request->to) {

                            $query->whereDate('created_at', '=', Carbon::createFromFormat('Y-m-d', $request->from)->format('Y-m-d'));
                            $totalCommissionAmountGroupBy->whereDate('created_at', '=', Carbon::createFromFormat('Y-m-d', $request->from)->format('Y-m-d'));
                        } else {
                            $query->whereBetween('created_at', [
                                Carbon::createFromFormat('Y-m-d', $request->from)
                                    ->format('Y-m-d'), Carbon::createFromFormat('Y-m-d', $request->to)->addDay(1)->format('Y-m-d')
                            ]);
                            $totalCommissionAmountGroupBy->whereBetween('created_at', [
                                Carbon::createFromFormat('Y-m-d', $request->from)
                                    ->format('Y-m-d'), Carbon::createFromFormat('Y-m-d', $request->to)->addDay(1)->format('Y-m-d')
                            ]);
                        }

                        if (isset($request->user_id) && !empty($request->user_id)) {
                            $query->where('user_id', $request->user_id);
                            //$merchantCode->where('user_id', $request->user_id);
                            //$totalAmountGroupBy->where('user_id', $request->user_id);
                            $totalCommissionAmountGroupBy->where('user_id', $request->user_id);
                            //$totalTrnTypeCount->where('user_id', $request->user_id);
                        }

                        $result = array($query->first(), $totalCommissionAmountGroupBy->first());
                        echo json_encode($result);
                        exit;
                    } else {
                        $request->from = date('Y-m-d');
                        $request->to = date('Y-m-d');
                        if ((isset($request->from) && !empty($request->from)) && (isset($request->to) && !empty($request->to))) {
                            if ($request->from == $request->to) {

                                $query->whereDate('created_at', '=', Carbon::createFromFormat('Y-m-d', $request->from)->format('Y-m-d'));
                                $totalCommissionAmountGroupBy->whereDate('created_at', '=', Carbon::createFromFormat('Y-m-d', $request->from)->format('Y-m-d'));
                            } else {
                                $query->whereBetween('created_at', [
                                    Carbon::createFromFormat('Y-m-d', $request->from)
                                        ->format('Y-m-d'), Carbon::createFromFormat('Y-m-d', $request->to)->addDay(1)->format('Y-m-d')
                                ]);
                                $totalCommissionAmountGroupBy->whereBetween('created_at', [
                                    Carbon::createFromFormat('Y-m-d', $request->from)
                                        ->format('Y-m-d'), Carbon::createFromFormat('Y-m-d', $request->to)->addDay(1)->format('Y-m-d')
                                ]);
                            }
                        }
                    }

                    $data['totalAmount'] = array($query->first(), $totalCommissionAmountGroupBy->first());
                    break;
                case 'validation':
                    $data['page_title'] = "OCR Transaction List";
                    $data['site_title'] = "Users";

                    $data['view'] = ADMIN . ".reports.ocrTransactionReport";
                    $data['userData'] = User::select(DB::raw('id,concat(name," (",email,")") as userName'))->where('is_admin', '0')->get();
                    $query = Validation::select(DB::raw('sum(fee) as totalAmount,count(id) as totalCount'))->whereIn('status', ['success', 'pending']);
                    $totalCommissionAmountGroupBy = Validation::select(DB::raw('sum(tax) as totalCommissionAmount,count(id) as totalCommissionCount'))
                        //->whereIn('transaction_type', ['ms','cw'])
                        ->whereIn('status', ['success', 'pending']);

                    if ($request->isMethod('post')) {

                        if ($request->from == $request->to) {

                            $query->whereDate('created_at', '=', Carbon::createFromFormat('Y-m-d', $request->from)->format('Y-m-d'));
                            $totalCommissionAmountGroupBy->whereDate('created_at', '=', Carbon::createFromFormat('Y-m-d', $request->from)->format('Y-m-d'));
                        } else {
                            $query->whereBetween('created_at', [
                                Carbon::createFromFormat('Y-m-d', $request->from)
                                    ->format('Y-m-d'), Carbon::createFromFormat('Y-m-d', $request->to)->addDay(1)->format('Y-m-d')
                            ]);
                            $totalCommissionAmountGroupBy->whereBetween('created_at', [
                                Carbon::createFromFormat('Y-m-d', $request->from)
                                    ->format('Y-m-d'), Carbon::createFromFormat('Y-m-d', $request->to)->addDay(1)->format('Y-m-d')
                            ]);
                        }

                        if (isset($request->user_id) && !empty($request->user_id)) {
                            $query->where('user_id', $request->user_id);
                            //$merchantCode->where('user_id', $request->user_id);
                            //$totalAmountGroupBy->where('user_id', $request->user_id);
                            $totalCommissionAmountGroupBy->where('user_id', $request->user_id);
                            //$totalTrnTypeCount->where('user_id', $request->user_id);
                        }

                        $result = array($query->first(), $totalCommissionAmountGroupBy->first());
                        echo json_encode($result);
                        exit;
                    } else {
                        $request->from = date('Y-m-d');
                        $request->to = date('Y-m-d');
                        if ((isset($request->from) && !empty($request->from)) && (isset($request->to) && !empty($request->to))) {
                            if ($request->from == $request->to) {

                                $query->whereDate('created_at', '=', Carbon::createFromFormat('Y-m-d', $request->from)->format('Y-m-d'));
                                $totalCommissionAmountGroupBy->whereDate('created_at', '=', Carbon::createFromFormat('Y-m-d', $request->from)->format('Y-m-d'));
                            } else {
                                $query->whereBetween('created_at', [
                                    Carbon::createFromFormat('Y-m-d', $request->from)
                                        ->format('Y-m-d'), Carbon::createFromFormat('Y-m-d', $request->to)->addDay(1)->format('Y-m-d')
                                ]);
                                $totalCommissionAmountGroupBy->whereBetween('created_at', [
                                    Carbon::createFromFormat('Y-m-d', $request->from)
                                        ->format('Y-m-d'), Carbon::createFromFormat('Y-m-d', $request->to)->addDay(1)->format('Y-m-d')
                                ]);
                            }
                        }
                    }

                    $data['totalAmount'] = array($query->first(), $totalCommissionAmountGroupBy->first());
                    break;
                case 'dmt':
                    $data['page_title'] = "DMT Transaction List";
                    $data['site_title'] = "Users";

                    $data['view'] = ADMIN . ".reports.dmtTransactionReport";
                    $data['userData'] = User::select(DB::raw('id,concat(name," (",email,")") as userName'))->where('is_admin', '0')->get();
                    $query = DMTFundTransfer::select(DB::raw('sum(amount) as totalAmount,count(id) as totalCount'))->whereIn('status', ['processed', 'processing']);
                    $totalCommissionAmountGroupBy = DMTFundTransfer::select(DB::raw('sum(fee+tax) as totalCommissionAmount,count(id) as totalCommissionCount'))
                        //->whereIn('transaction_type', ['ms','cw'])
                        ->whereIn('status', ['processed', 'processing']);

                    if ($request->isMethod('post')) {

                        if ($request->from == $request->to) {

                            $query->whereDate('created_at', '=', Carbon::createFromFormat('Y-m-d', $request->from)->format('Y-m-d'));
                            $totalCommissionAmountGroupBy->whereDate('created_at', '=', Carbon::createFromFormat('Y-m-d', $request->from)->format('Y-m-d'));
                        } else {
                            $query->whereBetween('created_at', [
                                Carbon::createFromFormat('Y-m-d', $request->from)
                                    ->format('Y-m-d'), Carbon::createFromFormat('Y-m-d', $request->to)->addDay(1)->format('Y-m-d')
                            ]);
                            $totalCommissionAmountGroupBy->whereBetween('created_at', [
                                Carbon::createFromFormat('Y-m-d', $request->from)
                                    ->format('Y-m-d'), Carbon::createFromFormat('Y-m-d', $request->to)->addDay(1)->format('Y-m-d')
                            ]);
                        }

                        if (isset($request->user_id) && !empty($request->user_id)) {
                            $query->where('user_id', $request->user_id);
                            //$merchantCode->where('user_id', $request->user_id);
                            //$totalAmountGroupBy->where('user_id', $request->user_id);
                            $totalCommissionAmountGroupBy->where('user_id', $request->user_id);
                            //$totalTrnTypeCount->where('user_id', $request->user_id);
                        }

                        $result = array($query->first(), $totalCommissionAmountGroupBy->first());
                        echo json_encode($result);
                        exit;
                    } else {
                        $request->from = date('Y-m-d');
                        $request->to = date('Y-m-d');
                        if ((isset($request->from) && !empty($request->from)) && (isset($request->to) && !empty($request->to))) {
                            if ($request->from == $request->to) {

                                $query->whereDate('created_at', '=', Carbon::createFromFormat('Y-m-d', $request->from)->format('Y-m-d'));
                                $totalCommissionAmountGroupBy->whereDate('created_at', '=', Carbon::createFromFormat('Y-m-d', $request->from)->format('Y-m-d'));
                            } else {
                                $query->whereBetween('created_at', [
                                    Carbon::createFromFormat('Y-m-d', $request->from)
                                        ->format('Y-m-d'), Carbon::createFromFormat('Y-m-d', $request->to)->addDay(1)->format('Y-m-d')
                                ]);
                                $totalCommissionAmountGroupBy->whereBetween('created_at', [
                                    Carbon::createFromFormat('Y-m-d', $request->from)
                                        ->format('Y-m-d'), Carbon::createFromFormat('Y-m-d', $request->to)->addDay(1)->format('Y-m-d')
                                ]);
                            }
                        }
                    }

                    $data['totalAmount'] = array($query->first(), $totalCommissionAmountGroupBy->first());
                    break;

                case 'panCard':

                    $data['page_title'] = "PAN Transaction List";
                    $data['site_title'] = "Users";

                    $data['view'] = ADMIN . ".reports.panTransactionReport";
                    $data['userData'] = User::select(DB::raw('id,concat(name," (",email,")") as userName'))->where('is_admin', '0')->get();
                    $query = PanCardTransaction::select(DB::raw('sum(fee) as totalAmount,count(id) as totalCount'))->whereIn('status', ['success', 'pending']);
                    $totalCommissionAmountGroupBy = PanCard::select(DB::raw('count(id) as totCount'))->whereIn('status', ['1']);

                    if ($request->isMethod('post')) {

                        if ($request->from == $request->to) {

                            $query->whereDate('created_at', '=', Carbon::createFromFormat('Y-m-d', $request->from)->format('Y-m-d'));
                            $totalCommissionAmountGroupBy->whereDate('created_at', '=', Carbon::createFromFormat('Y-m-d', $request->from)->format('Y-m-d'));
                        } else {
                            $query->whereBetween('created_at', [
                                Carbon::createFromFormat('Y-m-d', $request->from)
                                    ->format('Y-m-d'), Carbon::createFromFormat('Y-m-d', $request->to)->addDay(1)->format('Y-m-d')
                            ]);
                            $totalCommissionAmountGroupBy->whereBetween('created_at', [
                                Carbon::createFromFormat('Y-m-d', $request->from)
                                    ->format('Y-m-d'), Carbon::createFromFormat('Y-m-d', $request->to)->addDay(1)->format('Y-m-d')
                            ]);
                        }

                        if (isset($request->user_id) && !empty($request->user_id)) {
                            $query->where('user_id', $request->user_id);
                            //$merchantCode->where('user_id', $request->user_id);
                            //$totalAmountGroupBy->where('user_id', $request->user_id);
                            $totalCommissionAmountGroupBy->where('user_id', $request->user_id);
                            //$totalTrnTypeCount->where('user_id', $request->user_id);
                        }

                        $result = array($query->first(), $totalCommissionAmountGroupBy->first());
                        echo json_encode($result);
                        exit;
                    } else {
                        $request->from = date('Y-m-d');
                        $request->to = date('Y-m-d');
                        if ((isset($request->from) && !empty($request->from)) && (isset($request->to) && !empty($request->to))) {
                            if ($request->from == $request->to) {

                                $query->whereDate('created_at', '=', Carbon::createFromFormat('Y-m-d', $request->from)->format('Y-m-d'));
                                $totalCommissionAmountGroupBy->whereDate('created_at', '=', Carbon::createFromFormat('Y-m-d', $request->from)->format('Y-m-d'));
                            } else {
                                $query->whereBetween('created_at', [
                                    Carbon::createFromFormat('Y-m-d', $request->from)
                                        ->format('Y-m-d'), Carbon::createFromFormat('Y-m-d', $request->to)->addDay(1)->format('Y-m-d')
                                ]);
                                $totalCommissionAmountGroupBy->whereBetween('created_at', [
                                    Carbon::createFromFormat('Y-m-d', $request->from)
                                        ->format('Y-m-d'), Carbon::createFromFormat('Y-m-d', $request->to)->addDay(1)->format('Y-m-d')
                                ]);
                            }
                        }
                    }

                    $data['totalAmount'] = array($query->first(), $totalCommissionAmountGroupBy->first());
                    break;
                default:
                    return abort(404);
                    break;
            }

            return view($data['view'])->with($data);
        } else {
            $data['url'] = url('admin/dashboard');
            return view('errors.401')->with($data);
        }
    }


    /**
     * update lean amount for user primary account
     */
    public function updateLeanAmount(Request $request)
    {
        try {
            $validator = Validator::make(
                $request->all(),
                [
                    'user_id' => "required",
                    'lean_amt' => "required|numeric",
                ]
            );

            if ($validator->fails()) {
                $message = json_encode($validator->errors()->all());
                return ResponseHelper::missing($message);
            }


            $userId = decrypt($request->user_id);
            $leanAmount = floatval($request->lean_amt);


            if (empty($leanAmount)) {
                return ResponseHelper::failed("Invalid amount. Rs." . $leanAmount);
            }


            //fetching user details
            $userInfo = DB::table('users')
                ->select('id', 'transaction_amount')
                ->where('id', $userId)
                ->first();


            if (empty($userInfo)) {
                return ResponseHelper::failed("Invalid user ID.");
            }


            //make desigion based on the amount
            if ($leanAmount < 0) {
                $txnType = 'cr';
                $narration = 'Lean amount released.';
            } else {
                $txnType = 'dr';
                $narration = 'Lean amount marked.';
            }

            //check lean mark total amount and released lean mark amount
            if ($txnType == 'cr') {

                $drBal = 0;
                $crBal = 0;

                $checkBal = DB::table('lean_mark_transactions')
                    ->select(
                        DB::raw("`user_id`, `txn_type`, sum(`amount`) as amt")
                    )
                    ->where('user_id', $userId)
                    ->groupBy('txn_type')
                    ->get();


                if ($checkBal->isEmpty()) {
                    return ResponseHelper::failed("No Lean Balance found.");
                }

                foreach ($checkBal as $row) {
                    if ($row->txn_type == "dr") {
                        $drBal = round($row->amt, 2);
                    } else if ($row->txn_type == "cr") {
                        $crBal = round($row->amt, 2);
                        $crBal = abs($crBal);
                    }
                }

                $remainLeanBalance = round($drBal - $crBal, 2);

                if (abs($leanAmount) > ($remainLeanBalance)) {
                    return ResponseHelper::failed("Lean Amount Balance is {$remainLeanBalance} only. Releasing for " . abs($leanAmount) . " Rs.");
                }
            }


            $closingBalance = $userInfo->transaction_amount - $leanAmount;


            //prepare array to inster into DB
            $txnData = [
                'user_id' => $userId,
                'txn_type' => $txnType,
                'amount' => $leanAmount,
                'opening_balance' => 0,
                'closing_balance' => 0,
                'status' => '0',
                'narration' => $narration,
                'udf1' => "admin:" . Auth::user()->id,
                'created_at' => date("Y-m-d H:i:s"),
            ];


            $txnData['id'] = DB::table('lean_mark_transactions')->insertGetId($txnData);


            //assign job for manage balance
            PrimaryFundCredit::dispatch((object) $txnData, 'lean_mark_balance_by_admin')->onQueue('primary_fund_queue');


            return ResponseHelper::success('Primary balance will be after lean mark Rs: ' . $closingBalance, ['status' => "Updated"]);
        } catch (Exception $e) {
            return ResponseHelper::swwrong("Error: " . $e->getMessage());
        }
    }


    /**
     * update service web value and api value
     */
    public function serviceValueUpdate(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'user_id' => "required"
            ]
        );

        if ($validator->fails()) {
            $message = json_encode($validator->errors()->all());
            return ResponseHelper::missing($message);
        }

        $userId = decrypt($request->user_id);
        $data = $request->all();
        // PAYOUT SERVICE UPDATE

        if (isset($data['payout_service'])) {
            $payoutService = $data['payout_service'];
            $payoutService = str_replace(" ", "", $payoutService);
            $contains = Str::contains($payoutService, 'api-');
            $webContains = Str::contains($payoutService, 'web-');
            if ($contains || $webContains) {
                $payoutServiceArray = explode(',', $payoutService);
                $input1 = 'api-';
                $apiResult = array_filter($payoutServiceArray, function ($item) use ($input1) {
                    if (stripos($item, $input1) !== false) {
                        return true;
                    }
                    return false;
                });

                $input2 = 'web-';
                $webResult = array_filter($payoutServiceArray, function ($item) use ($input2) {
                    if (stripos($item, $input2) !== false) {
                        return true;
                    }
                    return false;
                });
                if (count($apiResult)) {
                    $apiResult = explode(",", str_replace("api-", "", implode(",", $apiResult)));
                }
                if (count($webResult)) {
                    $webResult = explode(",", str_replace("web-", "", implode(",", $webResult)));
                }
                $setValue['web_value'] = implode(",", $webResult);
                $setValue['api_value'] = implode(",", $apiResult);
                DB::table('user_services')
                    ->where(['user_id' => $userId, 'service_id' => PAYOUT_SERVICE_ID])
                    ->update($setValue);
            }
        } else {
            $setValue['web_value'] = '';
            $setValue['api_value'] = '';
            DB::table('user_services')
                ->where(['user_id' => $userId, 'service_id' => PAYOUT_SERVICE_ID])
                ->update($setValue);
        }

        // SMART COLLECT SERVICE UPDATE

        if (isset($data['smart_collect'])) {
            $smartCollectService = $data['smart_collect'];
            $smartCollectService = str_replace(" ", "", $smartCollectService);
            $contains = Str::contains($smartCollectService, 'api-');
            $webContains = Str::contains($smartCollectService, 'web-');
            if ($contains || $webContains) {
                $smartCollectService = explode(',', $smartCollectService);
                $input1 = 'api-';
                $apiResult = array_filter($smartCollectService, function ($item) use ($input1) {
                    if (stripos($item, $input1) !== false) {
                        return true;
                    }
                    return false;
                });

                $input2 = 'web-';
                $webResult = array_filter($smartCollectService, function ($item) use ($input2) {
                    if (stripos($item, $input2) !== false) {
                        return true;
                    }
                    return false;
                });
                if (count($apiResult)) {
                    $apiResult = explode(",", str_replace("api-", "", implode(",", $apiResult)));
                }
                if (count($webResult)) {
                    $webResult = explode(",", str_replace("web-", "", implode(",", $webResult)));
                }
                $setValue['web_value'] = implode(",", $webResult);
                $setValue['api_value'] = implode(",", $apiResult);
                DB::table('user_services')
                    ->where(['user_id' => $userId, 'service_id' => AUTO_COLLECT_SERVICE_ID])
                    ->update($setValue);
            }
        } else {
            $setValue['web_value'] = '';
            $setValue['api_value'] = '';
            DB::table('user_services')
                ->where(['user_id' => $userId, 'service_id' => AUTO_COLLECT_SERVICE_ID])
                ->update($setValue);
        }
        // AEPS SERVICE UPDATE

        if (isset($data['aeps_service']) || isset($data['aeps_service_api'])) {
            $aepsService = isset($data['aeps_service']) ? $data['aeps_service'] : "";
            $aepsServiceApi = isset($data['aeps_service_api']) ? $data['aeps_service_api'] : "";
            if (isset($aepsService) && !empty($aepsService) && isset($aepsServiceApi) && !empty($aepsServiceApi)) {
                $aepsService = $aepsService . ',' . $aepsServiceApi;
            } elseif (isset($aepsServiceApi) && !empty($aepsServiceApi)) {
                $aepsService = $aepsServiceApi;
            }

            $aepsService = str_replace(" ", "", $aepsService);
            // web value example wi = web icici, ws = web sbm
            $wi = Str::contains($aepsService, 'web-icici-');
            $ws = Str::contains($aepsService, 'web-sbm-');
            $wa = Str::contains($aepsService, 'web-airtel-');
            $wp = Str::contains($aepsService, 'web-paytm-');
            if ($wi || $ws || $wa || $wp) {
                $iciciCheck = 'web-icici-';
                $aepsServiceArray = explode(',', $aepsService);
                $iciciResult = array_filter($aepsServiceArray, function ($item) use ($iciciCheck) {
                    if (stripos($item, $iciciCheck) !== false) {
                        return true;
                    }
                    return false;
                });

                $sbmCheck = 'web-sbm-';
                $sbmResult = array_filter($aepsServiceArray, function ($item) use ($sbmCheck) {
                    if (stripos($item, $sbmCheck) !== false) {
                        return true;
                    }
                    return false;
                });

                $airtelCheck = 'web-airtel-';
                $airtelResult = array_filter($aepsServiceArray, function ($item) use ($airtelCheck) {
                    if (stripos($item, $airtelCheck) !== false) {
                        return true;
                    }
                    return false;
                });

                $paytmCheck = 'web-paytm-';
                $paytmResult = array_filter($aepsServiceArray, function ($item) use ($paytmCheck) {
                    if (stripos($item, $paytmCheck) !== false) {
                        return true;
                    }
                    return false;
                });

                if (count($iciciResult)) {
                    $iciciResult = explode(",", str_replace($iciciCheck, "", implode(",", $iciciResult)));
                    $webAepsSetValue['icici'] = implode(",", $iciciResult);
                }
                if (count($sbmResult)) {
                    $sbmResult = explode(",", str_replace($sbmCheck, "", implode(",", $sbmResult)));
                    $webAepsSetValue['sbm'] = implode(",", $sbmResult);
                }
                if (count($airtelResult)) {
                    $airtelResult = explode(",", str_replace($airtelCheck, "", implode(",", $airtelResult)));
                    $webAepsSetValue['airtel'] = implode(",", $airtelResult);
                }
                if (count($paytmResult)) {
                    $paytmResult = explode(",", str_replace($paytmCheck, "", implode(",", $paytmResult)));
                    $webAepsSetValue['paytm'] = implode(",", $paytmResult);
                }
                if (isset($webAepsSetValue)) {
                    $apesValues['web_value'] = json_encode($webAepsSetValue);
                } else {
                    $apesValues['web_value'] = NULL;
                }
            } else {
                $apesValues['web_value'] = NULL;
            }

            // Api Value Set
            $ai = Str::contains($aepsService, 'api-icici-');
            $as = Str::contains($aepsService, 'api-sbm-');
            $aa = Str::contains($aepsService, 'api-airtel-');
            $ap = Str::contains($aepsService, 'api-paytm-');
            if ($ai || $as || $aa || $ap) {
                $iciciCheck = 'api-icici-';
                $aepsServiceArray = explode(',', $aepsService);
                $iciciResult = array_filter($aepsServiceArray, function ($item) use ($iciciCheck) {
                    if (stripos($item, $iciciCheck) !== false) {
                        return true;
                    }
                    return false;
                });

                $sbmCheck = 'api-sbm-';
                $sbmResult = array_filter($aepsServiceArray, function ($item) use ($sbmCheck) {
                    if (stripos($item, $sbmCheck) !== false) {
                        return true;
                    }
                    return false;
                });

                $airtelCheck = 'api-airtel-';
                $airtelResult = array_filter($aepsServiceArray, function ($item) use ($airtelCheck) {
                    if (stripos($item, $airtelCheck) !== false) {
                        return true;
                    }
                    return false;
                });

                $paytmCheck = 'api-paytm-';
                $paytmResult = array_filter($aepsServiceArray, function ($item) use ($paytmCheck) {
                    if (stripos($item, $paytmCheck) !== false) {
                        return true;
                    }
                    return false;
                });

                if (count($iciciResult)) {
                    $iciciResult = explode(",", str_replace($iciciCheck, "", implode(",", $iciciResult)));
                    $aepsSetValue['icici'] = implode(",", $iciciResult);
                }
                if (count($sbmResult)) {
                    $sbmResult = explode(",", str_replace($sbmCheck, "", implode(",", $sbmResult)));
                    $aepsSetValue['sbm'] = implode(",", $sbmResult);
                }
                if (count($airtelResult)) {
                    $airtelResult = explode(",", str_replace($airtelCheck, "", implode(",", $airtelResult)));
                    $aepsSetValue['airtel'] = implode(",", $airtelResult);
                }
                if (count($paytmResult)) {
                    $paytmResult = explode(",", str_replace($paytmCheck, "", implode(",", $paytmResult)));
                    $aepsSetValue['paytm'] = implode(",", $paytmResult);
                }
                if (isset($aepsSetValue)) {
                    $apesValues['api_value'] = json_encode($aepsSetValue);
                } else {
                    $apesValues['api_value'] = NULL;
                }
            } else {
                $apesValues['api_value'] = NULL;
            }
            if (isset($apesValues)) {
                DB::table('user_services')
                    ->where(['user_id' => $userId, 'service_id' => AEPS_SERVICE_ID])
                    ->update($apesValues);
            }
        } else {
            $setValue['web_value'] = '';
            $setValue['api_value'] = '';
            DB::table('user_services')
                ->where(['user_id' => $userId, 'service_id' => AEPS_SERVICE_ID])
                ->update($setValue);
        }
        // UPI SERVICE UPDATE
        if (isset($data['upi_service'])) {
            $upiService = $data['upi_service'];
            $upiService = str_replace(" ", "", $upiService);
            $contains = Str::contains($upiService, 'api-');
            $webContains = Str::contains($upiService, 'web-');
            if ($contains || $webContains) {
                $upiServiceArray = explode(',', $upiService);
                $input1 = 'api-';
                $upiApiResult = array_filter($upiServiceArray, function ($item) use ($input1) {
                    if (stripos($item, $input1) !== false) {
                        return true;
                    }
                    return false;
                });

                $input2 = 'web-';
                $upiWebResult = array_filter($upiServiceArray, function ($item) use ($input2) {
                    if (stripos($item, $input2) !== false) {
                        return true;
                    }
                    return false;
                });
                if (count($upiApiResult)) {
                    $upiApiResult = explode(",", str_replace("api-", "", implode(",", $upiApiResult)));
                }
                if (count($upiWebResult)) {
                    $upiWebResult = explode(",", str_replace("web-", "", implode(",", $upiWebResult)));
                }
                $upiSetValue['web_value'] = implode(",", $upiWebResult);
                $upiSetValue['api_value'] = implode(",", $upiApiResult);
                DB::table('user_services')
                    ->where(['user_id' => $userId, 'service_id' => UPI_SERVICE_ID])
                    ->update($upiSetValue);
            }
        } else {
            $setValue['web_value'] = '';
            $setValue['api_value'] = '';
            DB::table('user_services')
                ->where(['user_id' => $userId, 'service_id' => UPI_SERVICE_ID])
                ->update($setValue);
        }
        if ($userId) {
            return ['status' => true, 'message' => 'All services value updated successfully. '];
        } else {
            return ['status' => false, 'message' => 'Some thing went wrong .'];
        }
    }



    /**
     * Payout Report For All Users and Total amount filtered by date range
     */
    public function totalAmountReportsAll(Request $request, $service, $returnType = 'all')
    {
        $request['return'] = 'all';
        $request->orderIdArray = [];
        $request->serviceIdArray = [];
        $request->userIdArray = [];
        $request['returnType'] = $returnType;
        // $parentData = session('parentData');
        $request['where'] = 0;


        $toDate = $fromDate = date('Y-m-d');

        if (!empty($request->from)) {
            $fromDate = $request->from;
        }

        if (!empty($request->to)) {
            $toDate = $request->to;
        }

        switch ($service) {

            case 'payout':
                $sqlQuery = DB::table('orders')
                    ->select('orders.user_id', DB::raw("sum(orders.amount) AS tot_amount, sum(orders.fee) AS tot_fee, sum(orders.tax) AS tot_tax, count(orders.id) AS tot_txn"), 'users.name', 'users.email')
                    ->leftJoin('users', 'orders.user_id', 'users.id')
                    ->where('orders.status', 'processed')
                    ->whereDate('orders.created_at', '>=', $fromDate)
                    ->whereDate('orders.created_at', '<=', $toDate);

                if (!empty($request->user_id)) {
                    $sqlQuery = $sqlQuery->where('orders.user_id', $request->user_id);
                }

                $result = $sqlQuery->groupBy('orders.user_id');

                break;

            case 'upi':
               /* $sqlQuery = DB::table('upi_collects')
                    ->select('upi_collects.user_id', DB::raw("sum(upi_collects.amount) AS tot_amount, count(upi_collects.id) AS tot_txn, upi_collects.type AS type"), 'users.name', 'users.email')
                    ->leftJoin('users', 'upi_collects.user_id', 'users.id')
                    ->where('upi_collects.type', 'PAYMENT_RECV')
                    ->whereDate('upi_collects.created_at', '>=', $fromDate)
                    ->whereDate('upi_collects.created_at', '<=', $toDate);
                // ->skip($request->start)->take($request->length);

                if (!empty($request->user_id)) {
                    $sqlQuery->where('upi_collects.user_id', $request->user_id);
                }

                $result = $sqlQuery->groupBy('upi_collects.user_id');*/


                //UPI Collect
                $sqlUpiCollect = DB::table('upi_collects')
                    ->select('upi_collects.user_id', DB::raw("FORMAT(sum(upi_collects.amount),2) AS tot_amount, count(upi_collects.id) AS tot_txn, upi_collects.type AS type"), 'users.name', 'users.email')
                    ->leftJoin('users', 'upi_collects.user_id', 'users.id')
                    ->where('upi_collects.status', 'success')
                    ->whereDate('upi_collects.created_at', '>=', $fromDate)
                    ->whereDate('upi_collects.created_at', '<=', $toDate);

                if (!empty($request->user_id)) {
                    $sqlUpiCollect->where('upi_collects.user_id', $request->user_id);
                }

                $result = $sqlUpiCollect->groupBy('upi_collects.user_id');

                //$result = $result->merge($result2);

                break;

            case 'van':
                $sqlQuery = DB::table('fund_receive_callbacks')
                    ->select('fund_receive_callbacks.user_id', DB::raw("sum(fund_receive_callbacks.amount) AS tot_amount, count(fund_receive_callbacks.id) AS tot_txn"), 'users.name', 'users.email')
                    ->leftJoin('users', 'fund_receive_callbacks.user_id', 'users.id')
                    ->whereDate('fund_receive_callbacks.created_at', '>=', $fromDate)
                    ->whereDate('fund_receive_callbacks.created_at', '<=', $toDate);

                if (!empty($request->user_id)) {
                    $sqlQuery = $sqlQuery->where('fund_receive_callbacks.user_id', $request->user_id);
                }

                $result = $sqlQuery->groupBy('fund_receive_callbacks.user_id');

                break;

            case 'van-api':
                $sqlQuery = DB::table('cf_merchants_fund_callbacks')
                    ->select('cf_merchants_fund_callbacks.user_id', DB::raw("sum(cf_merchants_fund_callbacks.amount) AS tot_amount, count(cf_merchants_fund_callbacks.id) AS tot_txn"), 'users.name', 'users.email')
                    ->leftJoin('users', 'cf_merchants_fund_callbacks.user_id', 'users.id')
                    ->whereDate('cf_merchants_fund_callbacks.created_at', '>=', $fromDate)
                    ->whereDate('cf_merchants_fund_callbacks.created_at', '<=', $toDate);

                if (!empty($request->user_id)) {
                    $sqlQuery = $sqlQuery->where('cf_merchants_fund_callbacks.user_id', $request->user_id);
                }

                $result = $sqlQuery->groupBy('cf_merchants_fund_callbacks.user_id');

                break;

            case 'dashboard-users-amount':
                $result = DB::table('users')
                    ->select(
                        'users.name',
                        'users.email',
                        'business_infos.business_name',
                        DB::RAW('users.transaction_amount AS tot_amount'),
                        // DB::RAW('user_services.transaction_amount AS payout_amt'),
                        // DB::raw('users.transaction_amount AS tot_amount'),
                        DB::raw("(SELECT user_services.transaction_amount FROM user_services WHERE user_services.user_id = users.id AND user_services.service_id = '" . PAYOUT_SERVICE_ID . "' LIMIT 1) as payout_amt"),
                        DB::raw("(SELECT user_services.transaction_amount FROM user_services WHERE user_services.user_id = users.id AND user_services.service_id = '" . VALIDATE_SERVICE_ID . "' LIMIT 1) as validate_amt"),
                        DB::raw("(SELECT user_services.transaction_amount FROM user_services WHERE user_services.user_id = users.id AND user_services.service_id = '" . DMT_SERVICE_ID . "' LIMIT 1) as dmt_amt"),
                        DB::raw("(SELECT user_services.transaction_amount FROM user_services WHERE user_services.user_id = users.id AND user_services.service_id = '" . RECHARGE_SERVICE_ID . "' LIMIT 1) as recharge_amt")
                    )
                    // ->leftJoin('user_services', 'users.id', '=', 'user_services.user_id')
                    ->leftJoin('business_infos', 'users.id', '=', 'business_infos.user_id')
                    ->where('users.is_active', '1')
                    ->where('users.is_admin', '0');
                // ->where('users.transaction_amount', '>', 0);
                // ->where('user_services.service_id', PAYOUT_SERVICE_ID)
                // ->where('user_services.service_id', VALIDATE_SERVICE_ID)
                // ->where('user_services.service_id', DMT_SERVICE_ID)
                // ->where('user_services.service_id', RECHARGE_SERVICE_ID)
                // ->where('user_services.is_active', '1')
                // ->where(function($sql){
                //     return $sql->where('users.transaction_amount', '>', 0)
                //         ->orWhere('user_services.transaction_amount', '>', 0);
                // });
                // ->groupBy('users.id');
                break;

            default:
                return abort(404);
                break;
        }


        if (!empty($request->order[0]['column'])) {
            $filterColumn = $request->columns[$request->order[0]['column']]['data'];
            $orderBy = $request->order[0]['dir'];
            $result->orderBy($filterColumn, $orderBy);
        } else {
            $result->orderBy('tot_amount', 'DESC');
        }


        $sqlQueryCount = $result;
        $sqlQueryCount = $sqlQueryCount->get();

        if ($request['length'] != -1) {
            $result->skip($request->start)->take($request->length);
        }
        $result = $result->get();


        if ($request->return == "all" || $returnType == "all") {
            $json_data = array(
                "draw" => intval($request['draw']),
                "recordsTotal" => intval(count($sqlQueryCount)),
                "recordsFiltered" => intval(count($sqlQueryCount)),
                "data" => $result,
                "from_date" => $fromDate,
                "to_date" => $toDate,
                "start" => $request->start,
                "length" => $request->length,
            );
            echo json_encode($json_data);
        } else {
            return response()->json($result);
        }
    }

    public function upiCallbackAmount(Request $request)
    {
        $from_date = $request->fee_from_date;
        $to_date = $request->fee_to_date;
        $userId = $request->user_id;
        $fee = (($request->fee_amount));
        $upi_amount = (($request->upi_amount));
        $fee_percentage = $request->fee_percentage;
        $txnId = CommonHelper::getRandomString('txn', false);
        //$last_day_this_month  = date('Y-m-t',strtotime($date.'-01'));
        $adminUserId = Auth::user()->id;

        $getUserAcc = User::select('id', 'account_number', 'transaction_amount')
            ->where('id', $userId)->first();
        DB::enableQueryLog();
        //$invoiceData = DB::table('invoice')->where('user_id',$userId)->whereBetween('start_date',[$from_date,$to_date])->orWhereBetween('end_date',[$from_date,$to_date])->count();
        //dd(DB::getQueryLog());
        $tr_amount = 0;

        $fee = $fee;
        $tax = round($fee * 18 / 100, 2, PHP_ROUND_HALF_EVEN);
        $tr_total_amount = ($fee >= 0) ? ($fee + $tax) : ($fee + $tax);
        $PW_balance = $getUserAcc->transaction_amount - $tr_total_amount;
        $getUserAcc->transaction_amount = $PW_balance;
        $getUserAcc->save();
        // if($invoiceData==0)
        // {

        $upiTxn = [
            'txn_id' => $txnId,
            'txn_ref_id' => 'INV_' . $txnId,
            'account_number' => $getUserAcc->account_number,
            'user_id' => $getUserAcc->id,
            'order_id' => $txnId,
            'tr_total_amount' => '-' . $tr_total_amount,
            'tr_amount' => 0,
            'tr_fee' => $fee,
            'tr_tax' => $tax,
            'closing_balance' => $PW_balance,
            'tr_date' => date('Y-m-d H:i:s'),
            'tr_type' => 'dr',
            'tr_identifiers' => 'upi_fee_inv',
            'tr_narration' => $tr_total_amount . ' UPI fee charged for ' . date('d M ', strtotime($from_date)) . ' to ' . date('d M Y ', strtotime($to_date)),
            'tr_reference' => '',
            'udf1' => '',
            'udf2' => '',
            'udf3' => '',
            'udf4' => '',
            'created_at' => date('Y-m-d H:i:s'),
        ];

        //insert transactions
        DB::table('transactions')
            ->insert($upiTxn);
        $upiInvoice = [
            'invoice_id' => 'INV_' . $txnId,
            'user_id' => $userId,
            'service_id' => 'srv_1626344088',
            'fee_amount' => $fee + $tax,
            'fee_able_amount' => $upi_amount,
            'record_date' => '',
            'start_date' => $from_date,
            'udf1' => 'percentage@' . $fee_percentage,
            'udf2' => "admin:" . $adminUserId,
            'end_date' => $to_date,
            'created_at' => date('Y-m-d H:i:s')
        ];
        DB::table('invoice')
            ->insert($upiInvoice);

        $this->message = "UPI Fee Collected Successfully.";
        $this->status = true;
        $this->modal = true;
        $this->alert = true;
        $this->redirect = true;
        return $this->populateresponse();
        // }
        // else
        // {
        //     $this->message  = array('message' => 'Fee already exist for this month.');
        //             $this->message_object = true;
        //             $this->status   = true;


        // $this->redirect = true;
        // return $this->populateresponse();

        // }

    }

    public function getUpiAmount(Request $request)
    {
        $query = 'SELECT  sum(amount) as totAmount FROM (SELECT DISTINCT customer_ref_id, amount, created_at FROM upi_callbacks where user_id=' . $request->user_id . ' and date_format(created_at,"%Y-%m-%d") between "' . Carbon::createFromFormat('Y-m-d', $request->from)->format('Y-m-d') . '" and "' . Carbon::createFromFormat('Y-m-d', $request->to)->format('Y-m-d') . '" group by customer_ref_id) t ';
        //$data = DB::table('upi_callbacks')->select(DB::raw('sum(amount)'))->where(DB::raw('date_format(upi_callbacks.created_at,"%Y-%m")'),Carbon::createFromFormat('Y-m-d',$request->from)->format('Y-m'))->where('user_id',$request->user_id)
        $data = DB::select($query);
        $collectData = DB::table('upi_collects')->select(DB::raw('sum(amount) as totAmount'))->where('user_id', $request->user_id)->where('status', 'success')->where(DB::raw('date_format(created_at,"%Y-%m-%d") between ' . Carbon::createFromFormat('Y-m-d', $request->from)->format('Y-m-d') . ' and ' . Carbon::createFromFormat('Y-m-d', $request->from)->format('Y-m-d')))->first();


        $amount = $data[0]->totAmount + $collectData->totAmount;
        $fee = ($amount * $request->fee) / 100;

        return json_encode(array('amount' => number_format($amount, 2, '.', ''), 'fee' => number_format($fee, 2, '.', ''), 'fee_percentage' => $request->fee, 'status' => true));


    }


    /**
     * Load Money List
     */
    public function loadMoneyList()
    {
        if (Auth::user()->hasRole('super-admin') || Auth::user()->hasRole('finance')) {
            $data['page_title'] = "Load Money";
            $data['site_title'] = "Load Money Request";
            $data['view'] = ADMIN . ".load_money";

            $id = 0;
            return view($data['view'], compact('id'))->with($data);
        } else {
            $data['url'] = url('admin/dashboard');
            return view('errors.401')->with($data);
        }
    }

    /**
     * Load Money Update
     */
    public function loadMoneyListUpdate(Request $request, $action)
    {
        
        if (Auth::user()->hasRole('super-admin') || Auth::user()->hasRole('finance')) {
            switch ($action) {
                case 'cancelled':
                    $validator = Validator::make(
                        $request->all(),
                        [
                            'remarks' => "required",
                            'request_id' => "required|numeric",
                        ]
                    );


                    if ($validator->fails()) {
                        $message = json_decode(json_encode($validator->errors()), true);
                        return ResponseHelper::missing('Some params are missing.', $message);
                    }

                    $requestId = $request->request_id;
                    $remarks = $request->remarks;
                    $adminId = Auth::user()->id;

                    $update = DB::table('load_money_request')->where('id', $requestId)
                        ->update([
                            'status' => 'rejected',
                            'admin_id' => $adminId,
                            'remarks' => $remarks,
                            'updated_at' => date('Y-m-d H:i:s'),
                        ]);

                    if ($update) {
                        $this->status = true;
                        $this->modal = true;
                        $this->alert = true;
                        $this->message = "Request Cancelled Successfully";
                        $this->title = "Load Money";
                        $this->redirect = true;
                        return $this->populateresponse();
                    }

                    break;

                case 'approve':
                    $validator = Validator::make(
                        $request->all(),
                        [
                            'remarks' => "nullable",
                            'request_id' => "required|numeric",
                        ]
                    );


                    if ($validator->fails()) {
                        $message = json_decode(json_encode($validator->errors()), true);
                        return ResponseHelper::missing('Some params are missing.', $message);
                    }

                    $requestId = $request->request_id;
                    $remarks = $request->remarks;
                    $adminId = Auth::user()->id;

                    $reqData = DB::table('load_money_request')->where('id', $requestId)->first();

                    $reqData->remarks = $remarks;
                    $reqData->admin_id = $adminId;

                    try {
                        PrimaryFundCredit::dispatch($reqData, 'load_money_fee')->onQueue('primary_fund_queue');

                        $this->status = true;
                        $this->modal = true;
                        $this->alert = true;
                        $this->message = "Request Approved Successfully";
                        $this->title = "Load Money";
                        $this->redirect = true;
                        return $this->populateresponse();
                    } catch (Exception $e) {
                        return $e->getMessage();
                    }
                    break;

                default:
                    return abort(404);
                    break;
            }
        } else {
            return abort(404);
        }
    }


    /**
     * userStatusChange
     *
     * @param  mixed $request
     * @return void
     */
    public function userStatusChange(Request $request)
    {
        if (Auth::user()->hasRole('super-admin')) {

            $validator = Validator::make(
                $request->all(),
                [
                    'remarks' => "required",
                    'user_id' => "required",
                    'logged_id' => "required",
                    'status_id' => "required",
                ]
            );

            if ($validator->fails()) {
                $message = json_decode(json_encode($validator->errors()), true);
                return ResponseHelper::missing('Some params are missing.', $message);
            }

            $requestId = $request->user_id;
            $loggedId = decrypt($request->logged_id);
            $statusId = decrypt($request->status_id);
            $remarks = $request->remarks;
            $adminId = Auth::user()->id;
            $update = false;
            $message = "";

            if ($adminId == $loggedId) {

                $update = DB::table('users')->where('id', $requestId)
                    ->update([
                        'is_active' => "$statusId",
                        'updated_at' => date('Y-m-d H:i:s'),
                    ]);
                if ($update) {
                    $type = 'user_status';
                    if ($statusId == 1) {
                        $type = 'user_active';
                    } elseif ($statusId == 0) {
                        $type = 'user_initiate';
                    } elseif ($statusId == 2) {
                        $type = 'user_inactive';
                        self::sendUserStatusEmail($requestId, $statusId, $remarks);
                    } elseif ($statusId == 3) {
                        $type = 'user_suspended';
                        self::sendUserStatusEmail($requestId, $statusId, $remarks);
                    } elseif ($statusId == 4) {
                        $type = 'user_blocked';
                        self::sendUserStatusEmail($requestId, $statusId, $remarks);
                    }
                    \ActivityLog::addToLog($type, $requestId, $remarks, $adminId);
                    $this->status = true;
                    $this->message = "User status changed successfully.";
                    $this->modalId = "userStatusChange";
                    $this->modalClose = true;
                    $this->alert = true;
                    $this->title = "User Status";
                    return $this->populateresponse();
                } else {
                    $message = "Unauthenticate user logged in";
                    $this->status = true;
                    $this->message = array("message" => $message);
                    $this->message_object = true;
                    return $this->populateresponse();
                }
            } else {
                $message = "Unauthenticate user logged in";
                $this->status = false;
                $this->message = array("message" => $message);
                $this->message_object = true;
                return $this->populateresponse();
            }

        } else {
            return abort(404);
        }
    }

    public function roles()
    {
        if (Auth::user()->hasRole('super-admin')) {
            $data['page_title'] = "Roles";
            $data['site_title'] = "Roles";
            $data['view'] = ADMIN . ".roles";

            $id = 0;
            return view($data['view'], compact('id'))->with($data);
        } else {
            $data['url'] = url('admin/dashboard');
            return view('errors.401')->with($data);
        }
    }

    public function roleUserList($id)
    {
        if (Auth::user()->hasRole('super-admin')) {
            $data['page_title'] = "Roles";
            $data['site_title'] = "Roles";
            return view('admin/role_users', compact('id'))->with($data);
        } else {
            $data['url'] = url('admin/dashboard');
            return view('errors.401')->with($data);
        }
    }
    public function changeStatusRole(Request $request)
    {
        $id = $request->id;
        $action = $request->action;

        $roles = Role::where('id', $id)->first();
        $roles->status = $action;
        $roles->save();
        $this->status = true;
        $this->modal = true;
        $this->alert = true;
        $this->message = "Status changed Successfully";
        $this->title = "Role";
        $this->redirect = false;
        return $this->populateresponse();
    }

    public function addRole(Request $request)
    {
        $role_name = $request->role_name;
        $role_data = Role::where('name', $role_name)->where('status', '!=', 'delete')->count();
        if ($role_data == 0) {
            try {
                $role = new Role();
                $role->name = $role_name;
                $role->slug = Str::slug($role_name);
                $role->status = 'active';
                $role->created_at = now();
                $role->save();

                $this->status = true;
                $this->modal = true;
                $this->message = "Role added Successfully";
                $this->redirect = true;
                return $this->populateresponse();
            } catch (Exception $e) {
                return $e->getMessage();
            }
        } else {
            $this->status = $this::FAILED_STATUS;
            $this->message = array("message" => "Role already added.");
            $this->message_object = true;
            return $this->populateresponse();

        }

    }

    public function userPermission($id)
    {
        $data['page_title'] = "Roles";
        $data['site_title'] = "Roles";
        $data['view'] = ADMIN . ".user_permission";
        $up = [];
        $data['roles'] = Role::where('status', 'active')->get();
        $data['permissions'] = Permission::get();
        $data['user_role'] = DB::table('users_roles')->where('user_id', $id)->first();
        $user_permission = DB::table('users_permissions')->select('permission_id')->where('user_id', $id)->get()->toArray();
        foreach ($user_permission as $val) {
            $up[] = $val->permission_id;
        }
        $data['user_permission'] = $up;
        return view($data['view'], compact('id'))->with($data);
    }

    public function addUserPermission(Request $request, $id)
    {
        if ($id) {
            $role_id = $request->role_id;
            $permission_id = !empty($request->permission_id) ? $request->permission_id : array();
            $user_role = DB::table('users_roles')->where('user_id', $id)->count();
            if ($user_role > 0) {

                $role = DB::table('users_roles')->where('user_id', $id)->delete();
            }
            $role = DB::table('users_roles')->insert([
                'role_id' => $role_id,
                'user_id' => $id
            ]);
            $user_permission = DB::table('users_permissions')->where('user_id', $id)->count();
            if ($user_permission > 0) {
                DB::table('users_permissions')->where('user_id', $id)->delete();
            }
            foreach ($permission_id as $per) {
                DB::table('users_permissions')->insert([
                    'user_id' => $id,
                    'permission_id' => $per
                ]);
            }
            $this->status = true;
            $this->modal = true;
            $this->message = "User permission added Successfully";
            $this->redirect = true;
            return $this->populateresponse();
        }
    }

    public function adminList()
    {
        if (Auth::user()->hasRole('super-admin')) {
            $data['page_title'] = "Admin User";
            $data['site_title'] = "Admin User";
            $data['view'] = ADMIN . ".admin_list";

            $id = 0;
            return view($data['view'], compact('id'))->with($data);
        } else {
            $data['url'] = url('admin/dashboard');
            return view('errors.401')->with($data);
        }
    }

    public function filterutr($utr)
    {
        if (Auth::user()->hasRole('super-admin') || Auth::user()->hasRole('support') || Auth::user()->hasRole('accountant')) {
            $orders = DB::table('orders')
                ->select('order_ref_id', 'client_ref_id', 'user_id', 'currency', 'amount', 'bank_reference')
                ->where('bank_reference', 'LIKE', "%$utr%")
                ->limit(10)
                ->get();

            $upiCallbacks = DB::table('upi_callbacks')
                ->select('payee_vpa', 'npci_txn_id', 'root_type', 'user_id', 'original_order_id', 'amount', 'merchant_txn_ref_id', 'customer_ref_id', 'payer_vpa', 'payer_acc_name', 'payer_mobile', 'payer_mobile', 'payer_ifsc')
                ->where('customer_ref_id', 'LIKE', "%$utr%")
                ->limit(10)
                ->get();

            $upiFundCallbacks = DB::table('cf_merchants_fund_callbacks')
                ->select('ref_no', 'utr', 'user_id', 'v_account_id', 'amount', 'v_account_number', 'remitter_account', 'remitter_ifsc', 'remitter_name', 'remitter_vpa')
                ->where('utr', 'LIKE', "%$utr%")
                ->limit(10)
                ->get();
            $upiFundReceiveCallback = DB::table('fund_receive_callbacks')
                ->select('utr', 'v_account_number', 'user_id', 'v_account_id', 'amount', 'remitter_account', 'remitter_ifsc', 'remitter_name', 'remitter_vpa')
                ->where('utr', 'LIKE', "%$utr%")
                ->limit(10)
                ->get();

            $aepsData = DB::table('aeps_transactions')
                ->select('rrn as utr', 'merchant_code', 'user_id', 'client_ref_id', 'transaction_amount', 'bankiin')
                ->where('rrn', 'LIKE', "%$utr%")
                ->limit(10)
                ->get();
            $payoutService = DB::table('global_services')
                ->where('service_id', PAYOUT_SERVICE_ID)->first();
            $payoutUrl = asset('') . '/media/logos/' . $payoutService->url;
            $ordersData = '';
            foreach ($orders as $value) {
                $ordersData .= '<a href="' . url('admin/orders') . '?bank_ref=' . $value->bank_reference . '"  style="text-decoration:none;"><div class="search-suggestions-group"><div class="ssg-header"><img src="' . $payoutUrl . '" height="40" width="40"/>
                <div class="ssg-name" style="margin-left:20px;"> ' . $value->bank_reference . ' </div><div class="ssg-info"></div></div><div class="ssg-content"><div class="ssg-items ssg-items-list"></div></div></div></a>';
            }

            $upiService = DB::table('global_services')
                ->where('service_id', UPI_SERVICE_ID)->first();
            $upiUrl = asset('') . '/media/logos/' . $upiService->url;

            foreach ($upiCallbacks as $value) {
                if ($value->root_type == 'ibl_tpv') {
                    $ordersData .= '<a href="' . url('admin/va/callbacks') . '?bank_ref=' . $value->customer_ref_id . '" style="text-decoration:none;"><div class="search-suggestions-group"><div class="ssg-header"><img src="' . $upiUrl . '" height="40" width="40"/>
                    <div class="ssg-name" style="margin-left:20px;"> ' . $value->customer_ref_id . ' </div><div class="ssg-info"></div></div><div class="ssg-content"><div class="ssg-items ssg-items-list"></div></div></div></a>';
                } else {
                    $ordersData .= '<a href="' . url('admin/upiCallback') . '?bank_ref=' . $value->customer_ref_id . '" style="text-decoration:none;"><div class="search-suggestions-group"><div class="ssg-header"><img src="' . $upiUrl . '" height="40" width="40"/>
                    <div class="ssg-name" style="margin-left:20px;"> ' . $value->customer_ref_id . ' </div><div class="ssg-info"></div></div><div class="ssg-content"><div class="ssg-items ssg-items-list"></div></div></div></a>';
                }
            }

            $upiFundService = DB::table('global_services')
                ->where('service_id', 'srv_1639475949')->first();
            $upiFundUrl = asset('') . '/media/logos/' . $upiFundService->url;

            foreach ($upiFundCallbacks as $value) {
                $ordersData .= '<a href="' . url('admin/smart-collect/callbacks') . '?bank_ref=' . $value->utr . '"  style="text-decoration:none;"><div class="search-suggestions-group"><div class="ssg-header"><img src="' . $upiFundUrl . '" height="40" width="40"/>
                <div class="ssg-name" style="margin-left:20px;"> ' . $value->utr . ' </div><div class="ssg-info"></div></div><div class="ssg-content"><div class="ssg-items ssg-items-list"></div></div></div></a>';
            }
            // Partner van
            $upiFundReceiveService = DB::table('global_services')
                ->where('service_id', 'srv_1635429299')->first();
            $upiFundReceiveUrl = asset('') . '/media/logos/' . $upiFundReceiveService->url;

            foreach ($upiFundReceiveCallback as $value) {
                $ordersData .= '<a href="' . url('admin/van-callback') . '?bank_ref=' . $value->utr . '"  style="text-decoration:none;"><div class="search-suggestions-group"><div class="ssg-header"><img src="' . $upiFundReceiveUrl . '" height="40" width="40"/>
                <div class="ssg-name" style="margin-left:20px;"> ' . $value->utr . ' </div><div class="ssg-info"></div></div><div class="ssg-content"><div class="ssg-items ssg-items-list"></div></div></div></a>';
            }
            // AEPS Transactions
            $aepsService = DB::table('global_services')
                ->where('service_id', AEPS_SERVICE_ID)->first();
            $aepsUrl = asset('') . '/media/logos/' . $aepsService->url;

            foreach ($aepsData as $value) {
                $ordersData .= '<a href="' . url('admin/aeps/transactions') . '?bank_ref=' . $value->utr . '"  style="text-decoration:none;"><div class="search-suggestions-group"><div class="ssg-header"><img src="' . $aepsUrl . '" height="40" width="40"/>
                <div class="ssg-name" style="margin-left:20px;"> ' . $value->utr . ' </div><div class="ssg-info"></div></div><div class="ssg-content"><div class="ssg-items ssg-items-list"></div></div></div></a>';
            }
            $data['message'] = 'Records found successfully.';
            $data['status'] = true;
            if (!empty($ordersData)) {
                $data['data'] = $ordersData;
            } else {
                $data['data'] = '<div class="search-suggestions-group"><div class="ssg-header">
                <div class="ssg-name" style="margin-left:20px;color:red;"> No records found. </div><div class="ssg-info"></div></div><div class="ssg-content"><div class="ssg-items ssg-items-list"></div></div></div>';
            }

            return $data;
        } else {
            $data['data'] = '<div class="search-suggestions-group"><div class="ssg-header">
            <div class="ssg-name" style="margin-left:20px;color:red;"> No records found. </div><div class="ssg-info"></div></div><div class="ssg-content"><div class="ssg-items ssg-items-list"></div></div></div>';
            $data['message'] = 'No records found.';
            $data['status'] = false;
            return $data;
        }
    }


    public function merchantList(Request $req)
    {

        $query = DB::table('aeps_transactions');
        $query->leftJoin(
            'agents',
            'agents.merchant_code',
            'aeps_transactions.merchant_code'
        );
        $query->select(
            'first_name',
            'middle_name',
            'last_name',
            'mobile',
            'aeps_transactions.merchant_code',
            DB::raw('SUM(aeps_transactions.transaction_amount) as totalAmount, COUNT(aeps_transactions.merchant_code) as counts, UPPER(group_concat(DISTINCT route_type))  as routes,UPPER(group_concat(DISTINCT transaction_type))  as trType,sum(case when
            (aeps_transactions.is_trn_credited="1")then (aeps_transactions.transaction_amount) else 0 end) as credited_amount')
        );
        $query->whereBetween(
            'aeps_transactions.created_at',
            [
                Carbon::createFromFormat('Y-m-d', $req->from)
                    ->format('Y-m-d'), Carbon::createFromFormat('Y-m-d', $req->to)
            ]
        );
        $query->groupBy('aeps_transactions.merchant_code');
        $data = $query->where('aeps_transactions.status', 'success')
            ->where('aeps_transactions.user_id', $req->user_id)->get();

        $resp = "";

        foreach ($data as $da) {
            $merchantCode = $da->merchant_code;
            $firstName = $da->first_name;
            $middleName = "";
            $lastName = "";
            if (!empty($da->middle_name) && $da->middle_name != null) {
                $middleName = $da->middle_name;
            }
            if (!empty($da->last_name) && $da->last_name != null) {
                $lastName = $da->last_name;
            }
            $resp .= "<tr><td>{$merchantCode} <br/> {$firstName} {$middleName} {$lastName} </td>
                    <td>{$da->counts}</td>
                    <td>{$da->totalAmount}</td>
                    <td>{$da->credited_amount}</td>
                    <td>{$da->routes}</td>
                    <td>{$da->trType}</td>
                    </tr>";
        }

        $response['data'] = $resp;
        return $response;
    }

    public function AEPSAgentsList()
    {
        $data['page_title'] = "AEPS Agent";
        $data['site_title'] = "AEPS Agent";
        $data['view'] = ADMIN . ".aeps.agents_list";
        $is_view_action = 0;
        $is_delete_action = 0;
        if (Auth::user()->hasRole('super-admin') || Auth::user()->hasRole('support') || Auth::user()->hasRole('aeps-support')) {
            $is_view_action = 1;
        }
        if (Auth::user()->hasRole('super-admin') || Auth::user()->hasRole('support')) {
            $is_delete_action = 1;
        }
        $data['is_view_action'] = $is_view_action;
        $data['is_delete_action'] = $is_delete_action;
        $data['user'] = DB::table('users')->select('name', 'email', 'id', 'mobile')->where('is_admin', '0')->get();

        $id = 0;
        return view($data['view'], compact('id'))->with($data);
    }

    public function AEPSTransactionList()
    {
        $data['page_title'] = "AEPS Transactions";
        $data['site_title'] = "AEPS Transactions";
        $data['view'] = ADMIN . ".aeps.aeps_transactions";
        $data['userData'] = DB::table('users')->select(DB::raw('id,concat(name," (",email,")") as userName'))->where('is_admin', '0')->get();
        $is_action = 0;
        if (Auth::user()->hasRole('super-admin') || Auth::user()->hasRole('support')) {
            $is_action = 1;
        }
        $data['is_action'] = $is_action;

        $id = 0;
        return view($data['view'], compact('id'))->with($data);
    }

    public function viewAEPSAgents($id)
    {
        if ($id) {

            $agent = Agent::select('agents.mobile', 'agents.first_name', 'agents.middle_name', 'agents.middle_name', 'agents.last_name', 'agents.email_id', 'agents.merchant_code', 'agents.aadhar_number', 'agents.pan_no', 'agents.address', 'agents.dob', 'agents.shop_address', 'agents.shop_name', 'agents.pin_code', 'agents.shop_pin', 'agents.created_at', 'agents.ekyc_documents_uploaded_at', 'states.state_name', 'districts.district_title', 'agents.id as id', 'agents.is_attachment_send', 'agents.documents_status', 'agents.ekyc', 'agents.is_ekyc_documents_uploaded', 'agents.doc_accepted_at', 'agents.doc_rejected_at')->where('agents.id', $id)->leftjoin('states', 'agents.state', '=', 'states.id')->leftJoin('districts', 'districts.id', '=', 'agents.district')->first();

            if (!empty($agent)) {
                $url = env('AEPS_KYC_URL') . '/api/document_list';
                $request = ['merchant_code' => $agent->merchant_code];
                $result = CommonHelper::curl($url, "POST", json_encode($request), ["Content-Type: application/json"], 'yes', 1, 'aeps_docs', 'AEPSDocuments');
                $response = json_decode($result['response']);
                $data['mobile'] = $agent->mobile;
                $data['id'] = $agent->id;
                $data['first_name'] = $agent->first_name;
                $data['middle_name'] = $agent->middle_name;
                $data['last_name'] = $agent->last_name;
                $data['email'] = $agent->email_id;
                $data['merchant_code'] = $agent->merchant_code;
                $data['aadhaar_no'] = $agent->aadhar_number;
                $data['pan_no'] = $agent->pan_no;
                $data['address'] = $agent->address;
                $data['dob'] = $agent->dob;
                $data['shop_address'] = $agent->shop_address;
                $data['pin_code'] = $agent->pin_code;
                $data['shop_pin'] = $agent->shop_pin;
                $data['states_name'] = $agent->state_name;
                $data['district_name'] = $agent->district_title;
                $data['created_at'] = $agent->created_at;
                $data['documents_status'] = $agent->documents_status;
                $data['is_attachment_send'] = $agent->is_attachment_send;
                $data['ekyc_documents_uploaded_at'] = $agent->ekyc_documents_uploaded_at;
                $data['is_ekyc_documents_uploaded'] = $agent->is_ekyc_documents_uploaded;
                $data['doc_rejected_at'] = $agent->doc_rejected_at;
                $data['doc_accepted_at'] = $agent->doc_accepted_at;
                $data['shop_name'] = $agent->shop_name;
                if ($agent->ekyc != '') {
                    $ekyc = json_decode($agent->ekyc, 1);
                    $data['sbm_is_ekyc'] = (isset($ekyc['sbm']['is_ekyc']) ? $ekyc['sbm']['is_ekyc'] : '');
                    $data['airtel_is_ekyc'] = (isset($ekyc['airtel']['is_ekyc']) ? $ekyc['airtel']['is_ekyc'] : '');
                    $data['icici_is_ekyc'] = (isset($ekyc['icici']['is_ekyc']) ? $ekyc['icici']['is_ekyc'] : '');
                    $data['paytm_is_ekyc'] = (isset($ekyc['paytm']['is_ekyc']) ? $ekyc['paytm']['is_ekyc'] : '');
                }
                if (isset($response) && $response->success && !empty($response->data)) {

                    $data['aadhaar_front_url'] = $response->data->aadhaar_front_url;
                    $data['aadhaar_back_url'] = $response->data->aadhaar_back_url;
                    $data['pan_front_url'] = $response->data->pan_front_url;
                    $data['shop_photo_url'] = $response->data->shop_photo_url;
                    $data['photo_url'] = $response->data->photo_url;
                    $data['status'] = $response->data->status;
                    $data['remarks'] = $response->data->remarks;

                }

            }
        }
        $data['page_title'] = "AEPS Agent Details";
        $data['site_title'] = "AEPS Agent";
        $data['view'] = ADMIN . ".aeps.agent_view";

        return view($data['view'], compact('id'))->with($data);
    }

    public function changeKycStatus(Request $request, $id)
    {
        if ($id && (Auth::user()->hasRole('super-admin') || Auth::user()->hasRole('support') || Auth::user()->hasRole('aeps-support'))) {
            $validator = Validator::make(
                $request->all(),
                [
                    'remarks' => "required",
                ]
            );

            if ($validator->fails()) {
                $message = json_decode(json_encode($validator->errors()), true);
                return ResponseHelper::missing('Some params are missing.', $message);
            }
            $agent = Agent::where('id', $id)->first();
            if (!empty($agent)) {
                $url = env('AEPS_KYC_URL') . '/api/updateStatus';
                $action = $request->action;
                $merchant_code = $agent->merchant_code;
                $remarks = $request->remarks;
                $request = [
                    'action' => $action,
                    'merchant_code' => $merchant_code,
                    'remarks' => $remarks
                ];
                $rejected_date = '';
                $accepted_date = '';
                if ($action == 'rejected') {
                    $is_keyc = 2;
                    $documents_status = 'rejected';
                    $rejected_date = date('Y-m-d H:i:s');
                } elseif ($action == 'accepted') {
                    $is_keyc = 1;
                    $documents_status = 'accepted';
                    $accepted_date = date('Y-m-d H:i:s');
                }
                $result = CommonHelper::curl($url, "POST", json_encode($request), ["Content-Type: application/json"], 'yes', 1, 'aeps_ekyc_doc', 'AEPSDocuments');
                $response = json_decode($result['response']);

                if ($response->success) {
                    $merchantExits = Agent::where(['id' => $id, 'merchant_code' => $merchant_code])->first();
                    if ($documents_status == 'rejected') {
                        $roots = array('sbm', 'paytm');
                    } else {
                        $roots = array('sbm');
                    }

                    AEPSController::ekycStatusLogs($merchantExits->user_id, $merchant_code, $merchantExits->ekyc);
                    Agent::where('id', $id)->update(['documents_status' => $documents_status, 'documents_remarks' => $remarks, 'doc_accepted_at' => $accepted_date ? $accepted_date : $merchantExits->doc_accepted_at, 'doc_rejected_at' => $rejected_date ? $rejected_date : $merchantExits->doc_rejected_at]);
                    foreach ($roots as $root) {
                        self::kycUpdate($merchantExits->user_id, $merchant_code, $root, $is_keyc);
                    }
                    self::sendKycStatusEmail($merchantExits->user_id, $merchant_code, $is_keyc);
                    $message = 'Files uploaded successfully';
                    $agenData = Agent::where(['user_id' => $merchantExits->user_id, 'merchant_code' => $merchant_code])->first();
                    AEPSController::ekycStatusLogs($merchantExits->user_id, $merchant_code, $agenData->ekyc);

                    $this->status = true;
                    $this->modal = true;
                    $this->message = "Status Changed Successfully";
                    $this->redirect = true;
                    return $this->populateresponse();
                } else {
                    $this->status = $this::FAILED_STATUS;
                    $this->message = array("message" => $response->message);
                    $this->message_object = true;
                    return $this->populateresponse();
                }
            }
        } else {
            $this->message = array("message" => "You don't have access to perform this action.");
            $this->message_object = true;
            $this->status = $this::FAILED_STATUS;
            return $this->populateresponse();
        }
    }


    public function changeAgentStatus(Request $request)
    {
        if (Auth::user()->hasRole('super-admin')) {
            $id = $request->id;
            $action = $request->action;
            if ($id) {
                if ($action == 'inactive') {
                    $status = '0';
                } else {

                    $status = '1';
                }

                Agent::where('id', $id)->update(['is_active' => $status, 'updated_at' => date('Y-m-d H:i:s')]);
                $this->status = true;
                $this->modal = true;
                $this->message = "Status Changed Successfully";
                $this->redirect = true;
                return $this->populateresponse();
            } else {
                $this->status = $this::FAILED_STATUS;
                $this->message = array("message" => 'Something went wrong');
                $this->message_object = true;
                return $this->populateresponse();
            }
        } else {
            $this->message = array("message" => "You don't have access to perform this action.");
            $this->message_object = true;
            $this->status = $this::FAILED_STATUS;
            return $this->populateresponse();
        }

    }

    public static function serviceActivityLog($id, $isActivation)
    {
        $userService = DB::table('user_services')->where('id', $id)->first();
        if (isset($userService) && $userService->service_id == PAYOUT_SERVICE_ID) {
            if ($isActivation)
                \ActivityLog::addToLog('payout_activation', $userService->user_id);
            else
                \ActivityLog::addToLog('payout_deactivation', $userService->user_id);
        } else if (isset($userService) && $userService->service_id == AEPS_SERVICE_ID) {
            if ($isActivation)
                \ActivityLog::addToLog('aeps_activation', $userService->user_id);
            else
                \ActivityLog::addToLog('aeps_deactivation', $userService->user_id);
        } else if (isset($userService) && $userService->service_id == UPI_SERVICE_ID) {
            if ($isActivation)
                \ActivityLog::addToLog('upi_activation', $userService->user_id);
            else
                \ActivityLog::addToLog('upi_deactivation', $userService->user_id);
        } else if (isset($userService) && $userService->service_id == AUTO_COLLECT_SERVICE_ID) {
            if ($isActivation)
                \ActivityLog::addToLog('smart-collect_activation', $userService->user_id);
            else
                \ActivityLog::addToLog('smart-collect_deactivation', $userService->user_id);
        } else if (isset($userService) && $userService->service_id == PARTNER_VAN_SERVICE_ID) {
            if ($isActivation)
                \ActivityLog::addToLog('partner-van_activation', $userService->user_id);
            else
                \ActivityLog::addToLog('partner-van_deactivation', $userService->user_id);
        }
    }

    public static function kycUpdate($userId, $merchantCode, $routeType, $type = 0)
    {
        $fileName = 'public/' . $merchantCode . '.txt';
        $resp['status'] = false;
        $resp['message'] = "";
        try {
            //code...
            $agentDataEkycIsEmpty = DB::table('agents')
                ->where(['user_id' => $userId, 'merchant_code' => $merchantCode])
                ->select('ekyc', DB::raw("json_extract(ekyc, '$.$routeType') as routeData"))
                ->first();

            $currDate = date('Y-m-d H:i:s');
            $expireDate = "NA";
            $is_keyc = $type;
            if ($routeType == 'airtel') {
                $expireDate = Carbon::now()->addMonth();
            }
            if (isset($agentDataEkycIsEmpty->ekyc) && !empty($agentDataEkycIsEmpty->ekyc)) {
                if (isset($agentDataEkycIsEmpty) && !empty($agentDataEkycIsEmpty->routeData)) {
                    $query = "UPDATE agents SET ekyc = JSON_SET(ekyc, '$.$routeType.is_ekyc',  $is_keyc), ekyc = JSON_SET(ekyc, '$.$routeType.ekyc_date', '$currDate') , ekyc = JSON_SET(ekyc, '$.$routeType.ekyc_expire_date', '$expireDate') where merchant_code = '$merchantCode' and user_id = $userId";
                    $check = DB::select($query);
                } else {
                    $data = json_decode($agentDataEkycIsEmpty->ekyc, TRUE);
                    $arrayData = [$routeType => ["is_ekyc" => $is_keyc, "ekyc_date" => "$currDate", "ekyc_expire_date" => "$expireDate"]];
                    $jsonData = json_encode(array_merge($data, $arrayData));
                    $query = "UPDATE agents SET ekyc =  '$jsonData' where merchant_code = '$merchantCode' and user_id = $userId";
                    DB::select($query);
                }
            } else {
                $ekycData = '{"' . $routeType . '":{"is_ekyc":' . $is_keyc . ',"ekyc_date":"' . $currDate . '","ekyc_expire_date":"' . $expireDate . '"}}';
                $query = "UPDATE agents SET ekyc = '$ekycData' where merchant_code = '$merchantCode' and user_id = $userId";
                DB::select($query);
            }


            $resp['status'] = true;
            $resp['message'] = "Ekyc update sucessfully.";
        } catch (\Exception $e) {
            Storage::disk('local')->append($fileName, $e . date('H:i:s'));
            $resp['status'] = false;
            $resp['message'] = "Some error occured." . $e->getMessage();
        }

        return $resp;
    }

    public static function sendKycStatusEmail($userId, $merchantCode, $is_keyc)
    {
        $user = DB::table('users')
            ->select('email', 'name', 'account_number')
            ->where('id', $userId)
            ->first();
        if (!empty($user->email)) {
            $agent = DB::table('agents')
                ->select('first_name', 'last_name', 'documents_remarks')
                ->where('user_id', $userId)
                ->where('merchant_code', $merchantCode)
                ->first();
            $name = $agent->first_name . ' ' . $agent->last_name;
            $remarks = $agent->documents_remarks;
            $message = "";
            if ($is_keyc == 0) {
                $message = "KYC documents submitted successfully for merchant $name ($merchantCode)";
            } elseif ($is_keyc == 1) {
                $message = "KYC <b>approved</b> for merchant $name ($merchantCode)";
            } elseif ($is_keyc == 2) {
                $message = "KYC <b>rejected</b> for merchant $name ($merchantCode). Please re-upload KYC documents.<br/>Remarks:$remarks";
            }
            $mailParms = [
                'email' => $user->email,
                'name' => $user->name,
                'message' => $message
            ];
            dispatch(new SendTransactionEmailJob((object) $mailParms, 'AEPSKycUpdate'));
        }
    }

    public static function sendUserStatusEmail($userId, $status, $reason)
    {
        $user = DB::table('users')
            ->select('users.email as email', 'users.name as name', 'users.account_number as account_number', 'users.transaction_amount as transaction_amount', 'users.locked_amount as locked_amount', 'business_name')
            ->leftJoin('business_infos', 'business_infos.user_id', 'users.id')
            ->where('users.id', $userId)
            ->first();
        if (!empty($user->email)) {
            $userService = DB::table('user_services')
                ->select('service_account_number', 'transaction_amount', 'locked_amount')
                ->where('user_id', $userId)
                ->where('service_id', PAYOUT_SERVICE_ID)
                ->first();

            $name = $user->name . ' ' . $user->email;
            $message = "";
            if ($status == 2) {
                $message = "Inactive";
            } elseif ($status == 3) {
                $message = "Suspended";
            } elseif ($status == 4) {
                $message = "Blocked";
            }
            $GlobalConfigUserStatus = DB::table('global_config')
                ->select('attribute_1', 'attribute_2', 'attribute_3')
                ->where(['slug' => 'user_account_update_send_balance'])
                ->first();

            $toEmails = ['aditya.yadav@mahagram.in'];

            if (isset($GlobalConfigUserStatus)) {
                if (!empty($GlobalConfigUserStatus->attribute_2)) {
                    $toEmails = explode(',', $GlobalConfigUserStatus->attribute_2);
                }
            }

            $mailParms = [
                'email' => $toEmails,
                'name' => $user->name,
                'userEmail' => $user->email,
                'reason' => $reason,
                'fullName' => $name,
                'businessName' => $user->business_name,
                'message' => $message,
                'primaryAccountNo' => $user->account_number,
                'primaryAccountBalance' => $user->transaction_amount,
                'payoutAccountNo' => isset($userService->service_account_number) ? $userService->service_account_number : "",
                'payoutAccountBalance' => isset($userService->transaction_amount) ? $userService->transaction_amount : "",
            ];
            dispatch(new SendTransactionEmailJob((object) $mailParms, 'userStatusUpdate'));
        }
    }



    public function getList($type, $userId = null)
    {
        if (Auth::user()->hasRole('super-admin') || Auth::user()->hasRole('support')) {
            switch ($type) {
                case 'bank-list':
                    $userBank = DB::table('user_bank_infos')
                        ->where('user_id', $userId)
                        ->orderBy('id', 'asc')
                        ->get();

                    if ($userBank->isNotEmpty()) {
                        return ResponseHelper::success('success', $userBank);
                    }

                    return ResponseHelper::failed('No Bank info found');
                    break;

                case 'config':
                    $userConfig = DB::table('user_config')
                        ->select(
                            'schemes.id',
                            'scheme_name',
                            'user_salt',
                            'upi_stack_callbacks',
                            'upi_stack_settlements',
                            'smart_collect_callbacks',
                            'smart_collect_settlements',
                            'load_money_request',
                            'threshold',
                            'is_auto_settlement',
                            'is_sdk_enable',
                            'upi_stack_vpa_limit',
                            'smart_collect_vpa_van_limit'
                        )
                        ->leftJoin('schemes', 'schemes.id', '=', 'user_config.scheme_id')
                        ->where('user_id', $userId)
                        ->first();

                    if (!empty($userConfig)) {
                        return ResponseHelper::success('success', $userConfig);
                    }

                    return ResponseHelper::failed('User Config Found');
                    break;

                case 'scheme-info':
                    $schemeInfo = DB::table('scheme_rules')
                        ->select(
                            'scheme_rules.start_value',
                            'scheme_rules.end_value',
                            'scheme_rules.type',
                            'scheme_rules.fee',
                            'scheme_rules.min_fee',
                            'scheme_rules.max_fee',
                            'scheme_rules.is_active',
                            'service_name',
                            'name'
                        )
                        ->leftJoin('global_services', 'global_services.service_id', '=', 'scheme_rules.service_id')
                        ->leftJoin('global_products', 'global_products.product_id', '=', 'scheme_rules.product_id')
                        ->where('scheme_rules.scheme_id', $userId)
                        ->orderBy('service_name', 'asc')
                        ->get();

                    if ($schemeInfo->isNotEmpty()) {
                        return ResponseHelper::success('success', $schemeInfo);
                    }

                    return ResponseHelper::failed('User Config Found');
                    break;

                case 'van-list':
                    $userBank = DB::table('user_van_accounts')
                        ->select(
                            'account_holder_name',
                            'account_number',
                            'ifsc',
                            'status',
                            'root_type',
                            DB::raw('authorized_remitters as remitters'),
                            'created_at'
                        )
                        ->where('user_id', $userId)
                        ->orderBy('id', 'asc')
                        ->get();

                    //fetching cf partner VAN Info
                    $cfVan = DB::table('business_infos')
                        ->select('van_acc_id', 'business_name', 'van', 'van_ifsc', 'van_2', 'van_2_ifsc', 'van_status')
                        ->where('user_id', $userId)
                        ->whereNotNull('van')
                        ->first();

                    if (!empty($cfVan)) {

                        $addedOn = '-';
                        $remitterAccount = '-';
                        $remitterIfsc = '-';


                        try {

                            //getting van acccount info
                            $vanHelper = new CashfreeAutoCollectHelper();

                            $result = $vanHelper->vanManager([], '/cac/v1/va/' . $cfVan->van_acc_id, 0, 'GET', 'getVanDetails');

                            if ($result['code'] == 200) {

                                $cashfreeResponse = json_decode($result['response']);

                                if ($cashfreeResponse->subCode == "200") {
                                    $addedOn = @$cashfreeResponse->data->addedOn;
                                    $remitterAccount = @$cashfreeResponse->data->remitterAccount;
                                    $remitterIfsc = @$cashfreeResponse->data->remitterIfsc;
                                }
                            }

                        } catch (Exception $e) {
                        }


                        $cfVanInfo[0] = [
                            'account_holder_name' => $cfVan->business_name,
                            'account_number' => $cfVan->van,
                            'ifsc' => $cfVan->van_ifsc,
                            'root_type' => "cf_van",
                            'status' => $cfVan->van_status,
                            'remitterAccount' => $remitterAccount,
                            'remitterIfsc' => $remitterIfsc,
                            'created_at' => $addedOn
                        ];

                        if (!empty($cfVan->van_2)) {
                            $cfVanInfo[1] = [
                                'account_holder_name' => $cfVan->business_name,
                                'account_number' => $cfVan->van_2,
                                'ifsc' => $cfVan->van_2_ifsc,
                                'root_type' => "cf_van",
                                'status' => $cfVan->van_status,
                                'remitterAccount' => $remitterAccount,
                                'remitterIfsc' => $remitterIfsc,
                                'created_at' => $addedOn
                            ];
                        }
                    }

                    $collection = new Collection();

                    if ($userBank->isNotEmpty()) {
                        $collection = $collection->concat($userBank);
                    }

                    if (!empty($cfVanInfo)) {
                        $collection = $collection->concat($cfVanInfo);
                    }

                    if ($collection->isNotEmpty()) {
                        return ResponseHelper::success('success', $collection);
                    }

                    return ResponseHelper::failed('No VAN info found');
                    break;

                default:
                    return null;
                    break;
            }
        } else {
            return abort(404);
        }
    }

    public function updateKYC(Request $request)
    {
        $id = $request->id;
        $action = $request->action;
        $route = $request->route;
        $resp['status'] = false;
        $resp['message'] = "";
        if ($id) {
            $agent = Agent::where('id', $id)->where('is_active', '1')->first();
            if (!empty($agent)) {
                $ekycData = $agent->ekyc;
                if ($action == 'approved') {
                    $ekyc = 1;
                } else {
                    $ekyc = 0;
                }

                $url = env('AEPS_KYC_URL') . '/api/updateStatus';
                $merchant_code = $agent->merchant_code;
                $remarks = 'accepted';
                $request = [
                    'action' => 'accepted',
                    'merchant_code' => $merchant_code,
                    'remarks' => $remarks
                ];

                $result = CommonHelper::curl($url, "POST", json_encode($request), ["Content-Type: application/json"], 'yes', 1, 'aeps_ekyc_doc', 'AEPSDocuments');
                $response = json_decode($result['response']);

                if ($response->success) {
                    Agent::where('id', $id)->update(['documents_status' => 'accepted', 'documents_remarks' => 'approved']);

                    if ($route == 'paytm' || $route == 'sbm') {
                        self::kycUpdate($agent->user_id, $agent->merchant_code, $route, $ekyc);
                    }
                    $resp['status'] = true;
                    $resp['message'] = "Updated Successfully.";
                }
            } else {
                $resp['status'] = false;
                $resp['message'] = "";
            }
        }
        return $resp;
    }

    public function updatePayoutRoot(Request $request)
    {
        if (Auth::user()->hasRole('support')) {


            $api_root = $request->api_root;
            $user_id = decrypt($request->user_id);
            $web_root = $request->web_root;
            if (!$api_root && !$web_root) {
                return ResponseHelper::failed("Payout root is required.");
            }
            if ($user_id) {
                DB::table('user_config')->where('user_id', $user_id)->update(['api_integration_id' => $api_root, 'web_integration_id' => $web_root]);
                return ResponseHelper::success("Record updated successfully.");
            } else {
                return ResponseHelper::failed("Invalid user ID.");
            }
        } else {
            return abort(404);
        }
    }



    /**
     * aepsStatusDiscrepancy function
     *
     * @param Request $request
     * @return void
     */
    public function aepsStatusDiscrepancy(Request $request)
    {
        try {

            $aeps = new AEPSController;

            $params = [
                'refernceno' => $request->clientRefId
            ];
            $requestType = 'statuscheck';

            $response = $aeps->APICaller($params, $requestType, '');
            if (isset($response['statuscode'])) {
                return $response;
            } else {
                $response['statuscode'] = "999";
                $response['message'] = "Something went wrong";
                return $response;
            }

        } catch (Exception $e) {
            return ResponseHelper::failed(SOMETHING_WENT_WRONG, $e->getMessage());
        }
    }

    /**
     * aepsStatusUpdate function
     *
     * @param Request $request
     * @return void
     */
    public function aepsStatusUpdate(Request $request)
    {
        try {
            if (Auth::user()->hasRole('super-admin')) {
                /**  Add Transaction Details */
                $status['status'] = false;
                $status['message'] = "Status not changed";
                $validator = Validator::make(
                    $request->all(),
                    [
                        'orderId' => "required",
                        'status' => "required",
                        'agentId' => "required",
                        'failedMessage' => ["nullable", "string"],
                        'rrn' => ["nullable", "string"],
                        'stanno' => ["nullable", "string"],
                    ]
                );

                if ($validator->fails()) {
                    $resp = json_decode(json_encode($validator->errors()), true);
                    return ResponseHelper::failed(@array_values($resp)[0][0]);
                }

                if ($request->status == 'success') {

                    $trnData = DB::table('aeps_transactions')
                        ->where('client_ref_id', $request->orderId)
                        ->first();
                    if (isset($trnData) && !empty($trnData) && $trnData->status == 'failed') {

                        $adminId = "ADMIN::" . Auth::user()->id;
                        AepsTransaction::where(['id' => $trnData->id])->update([
                            'status' => 'success',
                            'resp_stan_no' => $request->stanno,
                            'rrn' => $request->rrn,
                            'txt_1' => $adminId,
                        ]);
                        $status['status'] = 1;
                    } else if (isset($trnData) && !empty($trnData) && $trnData->status == 'pending') {
                        $adminId = "ADMIN::" . Auth::user()->id;
                        AepsTransaction::where(['id' => $trnData->id])->update([
                            'status' => 'success',
                            'resp_stan_no' => $request->stanno,
                            'rrn' => $request->rrn,
                            'txt_1' => $adminId,
                        ]);
                        $status['status'] = 1;
                    }
                } else if ($request->status == 'failed') {
                    $trnData = DB::table('aeps_transactions')
                        ->where('client_ref_id', $request->orderId)
                        ->first();
                    if (isset($trnData) && !empty($trnData) && $trnData->status == 'success') {
                        $utrTxn = DB::table('transactions')->select('id', 'txn_id', 'service_id')
                            ->where('tr_identifiers', 'aeps_inward_credit')
                            ->where('txn_ref_id', $trnData->trn_ref_id)
                            ->first();
                        if (empty($utrTxn)) {
                            return ResponseHelper::failed($status['message']);
                        }
                        $txnId = CommonHelper::getRandomString('txn', false);
                        $txnRefId = $request->orderId;
                        $commission = 0;
                        $tds = 0;
                        $trAmount = 0;
                        if ($trnData->is_commission_credited == '1' && !empty($trnData->commission_ref_id)) {
                            $trAmount = $trnData->transaction_amount;
                            $commission = $trnData->commission;
                            $tds = $trnData->tds;
                            $rvTxnAmount = $trnData->transaction_amount + $trnData->commission + $trnData->tds;
                        } else {
                            $trAmount = $trnData->transaction_amount;
                            $rvTxnAmount = $trnData->transaction_amount;
                        }
                        $txnNarration = $rvTxnAmount . ' debited against disputed UTR ' . $trnData->rrn;
                        $adminId = "ADMIN::" . Auth::user()->id;

                        DB::select("CALL aepsDisputeTxnJob($trnData->user_id, $trnData->id, '$txnId', '$txnRefId', '$trnData->rrn', $rvTxnAmount, $trAmount, $commission, $tds, '$txnNarration', '$utrTxn->service_id', '$utrTxn->txn_id', '$adminId', @outData)");

                        $results = DB::select('select @outData as outData');
                        $response = json_decode($results[0]->outData);
                        $status['status'] = $response->status;
                    } else if (isset($trnData) && !empty($trnData) && $trnData->status == 'pending') {
                        $adminId = "ADMIN::" . Auth::user()->id;
                        AepsTransaction::where(['id' => $trnData->id])->update([
                            'status' => 'failed',
                            'resp_stan_no' => $request->stanno,
                            'rrn' => $request->rrn,
                            'txt_1' => $adminId,
                        ]);
                        $status['status'] = 1;
                    }
                }

                if ($status['status']) {
                    return ResponseHelper::success('Status change successfull');
                } else {
                    return ResponseHelper::failed($status['message']);
                }
            }

        } catch (Exception $e) {
            return ResponseHelper::failed(SOMETHING_WENT_WRONG, $e->getMessage());
        }
    }


    public static function rechargeResponseFormat($response)
    {

        if (!empty($response->status)) {
            return [
                "clientRefId" => @$response->clientrefid,
                "amount" => $response->amount,
                "txnId" => $response->txnid,
                "remarks" => $response->remarks,
            ];
        } else {
            return [];
        }
    }

    public function updateUpiCollectIntegration(Request $request)
    {
        if(Auth::user()->hasRole('super-admin') && !empty($request->user_id))
        {
            DB::table('user_config')->where('user_id', $request->user_id)->update(['upi_collect_integration_id' => $request->integration_id]);
                return ResponseHelper::success("Record updated successfully.");
            
        }else {
            $data['url'] = url('admin/dashboard');
            return view('errors.401')->with($data);
        }
    }

    public function updateReseller(Request $request, $id)
    {
        try {
            if (Auth::user()->hasRole('super-admin')) {
                    $user = User::find($id);
                    $user->reseller = $request->reseller;
                    $user->save();

                    $this->status = true;
                    $this->modal = true;
                    $this->alert = false;
                    $this->message = "Reseller updated successfully.";
                    $this->redirect = true;
                    return $this->populateresponse();
                
            } else {
                return abort(404);
            }
        } catch(Exception $e) {
            $this->status_code = '100';
            $this->status = true;
            $this->modal = true;
            $this->alert = true;
            $this->message_object = true;
            $this->message = array('message' => "Error: " . $e->getMessage());
            $this->redirect = false;
            return $this->populateresponse();
        }
    } 
}

