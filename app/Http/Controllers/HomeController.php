<?php

namespace App\Http\Controllers;

use App\Helpers\CommonHelper;
use App\Helpers\ExportGlobal;
use App\Helpers\ResponseHelper;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use Yajra\DataTables\Html\Builder;
use App\Models\UserService;
use App\Models\Service;
use App\Models\Transaction;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware(['auth']);
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        session(['is_theme_change' => Auth::user()->is_theme_change]);
        $data['page_title'] =  "Dashboard";
        $data['site_title'] =  "Dashboard";
        $data['view']       = "home";
        $data['serviceData'] = UserService::leftJoin('global_services','global_services.service_id','user_services.service_id')
        ->select('global_services.service_id','global_services.service_name','user_services.*','global_services.url')
        ->where('user_services.user_id',Auth::user()->id)
        ->where('user_services.is_active','1')->get();
        
        $data['allService'] = Service::where('is_active','1')->get();
        $data['transaction'] = Transaction::where(['tr_identifiers'=>'internal_transfer', 'tr_type'=>'dr','user_id'=>Auth::user()->id, 'account_number'=> Auth::user()->account_number])
        ->orderBy('id','desc')->limit(10)->get();

        $chart = CommonHelper::chart(Auth::user()->id,'transactions','30Days', 'internal_transfer');

        $data['lastMonth'] = $chart['days'];
        $data['lastMonthAmount'] = $chart['amount'];

        
        $data['unsettledSmartCollect'] = CommonHelper::getUnsettledBalance(Auth::user()->id, 'smart_collect');
        $data['unsettledUpiStack'] = CommonHelper::getUnsettledBalance(Auth::user()->id, 'upi_stack');
        $data['unsettledVirtualAccount'] = CommonHelper::getUnsettledBalance(Auth::user()->id, 'virtual_account');
        $data['unsettledThresholdAmount'] = CommonHelper::getUnsettledBalance(Auth::user()->id, 'threshold');

        $data['isInternalTransfer'] = DB::table('user_config')->select('is_internal_transfer_enable')
            ->where('user_id', Auth::user()->id)->first();
        $data['globalInternalTransfer'] = DB::table('global_config')
            ->select('attribute_1')
            ->where('slug', 'internal_tranfer_enable')->first()->attribute_1;

       // dd($data['allService']); exit;
        return view($data['view'])->with($data);
    }

    public function user(Request $request,Builder $builder)
    {
        $data['site_title']     = $data['page_title'] = ' Rewards Achiever';
        $data['page_title_desc']= "List of Rewards Achiever";
        if($request->ajax()){
            $result = \App\Models\User::all();
            return DataTables::of($result)
            ->editColumn('name',function($dataFetched){
                return ucwords($dataFetched['name']);
            })
            ->rawColumns(['name'])
            ->make(true);
        }
        $data['html'] = $builder
            ->addColumn(['data' => 'name', 'name' => 'name','title' => 'Name','width' => 120])
            ->addColumn(['data' => 'mobile', 'name' => 'mobile','title' => 'Mobile','width' => 80])
            ->addColumn(['data' => 'email', 'name' => 'email','title' => 'Email','width' => 80])
            ->addColumn(['data' => 'created_at', 'name' => 'created_at','title' => 'Date','width' => 80]);

        return view(USER.'/'.PAYOUT.'.contact.list')->with($data);
    }

    public function sendOrderDataTotEmail()
    {

        $resp['status'] = false;
        $resp['message'] = "Export table initiate.";
        $GlobalConfig = DB::table('global_config')
                ->select('attribute_1', 'attribute_2', 'attribute_4', 'attribute_3', 'attribute_5')
                ->where(['slug' => 'export_excel_send_email'])
                ->first();
        if (isset($GlobalConfig) && $GlobalConfig->attribute_1 == 1) {
            $tables = isset($GlobalConfig->attribute_2) ? $GlobalConfig->attribute_2 : "orders";
            $heading = isset($GlobalConfig->attribute_3) ? $GlobalConfig->attribute_3 : "Order Table";
            $date = isset($GlobalConfig->attribute_4) ? $GlobalConfig->attribute_4 : date('Y-m-d',strtotime("-2 days")).'-'.date('Y-m-d',strtotime("-1 days"));
            $dateArray = explode("/",$date);
            $fromDate = $dateArray[0];
            $toDate = $dateArray[1];
            $to = isset($GlobalConfig->attribute_5) ? $GlobalConfig->attribute_5 : "aditya.yadav@mahagram.in";
            $tablesArray = explode(",",$tables);
            $headingAarray = explode(",",$heading);
            $toEmail = explode(",",$to);
            if (count($headingAarray) == count($tablesArray) ) {
                foreach ($tablesArray as $key => $tablesArrays) {
                    $filename = $date.'-'.time().$tablesArrays.'.xlsx';
                    $file = Excel::store(new ExportGlobal($tablesArrays, $fromDate, $toDate), $filename);
                    $data = array('data' => $headingAarray[$key], 'table' => $tablesArrays, 'date' => $date);
                    Mail::send('emails.sendData', $data, function($message) use($tablesArrays, $headingAarray, $key, $toEmail, $filename){
                    $message->to($toEmail)
                        ->subject($headingAarray[$key]);
                        $message->attach(url('/').'/storage/app/'.$filename);
                    });
                }
                    $resp['message'] = "Export table data on emails";
                    $resp['status'] = true;
            } else {
                $resp['message'] = "Heading and table records not matched.";
            }
        } else {
            $resp['message'] = "Excel export disabeld from admin.";
        }

        return $resp;
    }

    /**
     * filterutr
     *
     * @param  mixed $utr
     * @return void
     */
    public function filterutr($utr)
    {
        $userId = Auth::user()->id;
        $orders = DB::table('orders')
            ->select('order_ref_id', 'client_ref_id', 'user_id', 'currency', 'amount', 'bank_reference')
            ->where('bank_reference', 'LIKE', "%$utr%")
            ->where('user_id', $userId)
            ->limit(10)
            ->get();

        $upiCallbacks = DB::table('upi_callbacks')
            ->select('payee_vpa', 'npci_txn_id', 'user_id', 'original_order_id', 'amount', 'merchant_txn_ref_id', 'customer_ref_id', 'payer_vpa', 'payer_acc_name', 'payer_mobile', 'payer_mobile', 'payer_ifsc')
            ->where('customer_ref_id', 'LIKE', "%$utr%")
            ->where('user_id', $userId)
            ->limit(10)
            ->get();

        $upiFundCallbacks = DB::table('cf_merchants_fund_callbacks')
            ->select('ref_no', 'utr', 'user_id', 'v_account_id', 'amount', 'v_account_number', 'remitter_account', 'remitter_ifsc', 'remitter_name', 'remitter_vpa')
            ->where('utr', 'LIKE', "%$utr%")
            ->where('user_id', $userId)
            ->limit(10)
            ->get();
        $upiFundReceiveCallback = DB::table('fund_receive_callbacks')
            ->select('utr', 'v_account_number', 'user_id', 'v_account_id', 'amount', 'remitter_account', 'remitter_ifsc', 'remitter_name', 'remitter_vpa')
            ->where('utr', 'LIKE', "%$utr%")
            ->where('user_id', $userId)
            ->limit(10)
            ->get();
        $aepsData = DB::table('aeps_transactions')
            ->select('rrn as utr', 'merchant_code', 'user_id', 'client_ref_id', 'transaction_amount', 'bankiin')
            ->where('rrn', 'LIKE', "%$utr%")
            ->where('user_id', $userId)
            ->limit(10)
            ->get();

        $payoutService = DB::table('global_services')
            ->where('service_id', PAYOUT_SERVICE_ID)->first();
        $payoutUrl = asset('').'/media/logos/'.$payoutService->url;
        $ordersData = '';
        foreach ($orders as $value) {
            $ordersData .= '<a href="'.url('payout/orders').'?bank_ref='.$value->bank_reference.'"  style="text-decoration:none;"><div class="search-suggestions-group"><div class="ssg-header"><img src="'.$payoutUrl.'" height="40" width="40"/>
            <div class="ssg-name" style="margin-left:20px;"> '.$value->bank_reference.' </div><div class="ssg-info"></div></div><div class="ssg-content"><div class="ssg-items ssg-items-list"></div></div></div></a>';
        }

        $upiService = DB::table('global_services')
            ->where('service_id', UPI_SERVICE_ID)->first();
        $upiUrl = asset('').'/media/logos/'.$upiService->url;

        foreach ($upiCallbacks as $value) {
            $ordersData .= '<a href="'.url('upi/upicallbacks').'?bank_ref='.$value->customer_ref_id.'" style="text-decoration:none;"><div class="search-suggestions-group"><div class="ssg-header"><img src="'.$upiUrl.'" height="40" width="40"/>
            <div class="ssg-name" style="margin-left:20px;"> '.$value->customer_ref_id.' </div><div class="ssg-info"></div></div><div class="ssg-content"><div class="ssg-items ssg-items-list"></div></div></div></a>';
        }

        $upiFundService = DB::table('global_services')
            ->where('service_id', 'srv_1639475949')->first();
        $upiFundUrl = asset('').'/media/logos/'.$upiFundService->url;

        foreach ($upiFundCallbacks as $value) {
            $ordersData .= '<a href="'.url('collect/payments').'?bank_ref='.$value->utr.'"  style="text-decoration:none;"><div class="search-suggestions-group"><div class="ssg-header"><img src="'.$upiFundUrl.'" height="40" width="40"/>
            <div class="ssg-name" style="margin-left:20px;"> '.$value->utr.' </div><div class="ssg-info"></div></div><div class="ssg-content"><div class="ssg-items ssg-items-list"></div></div></div></a>';
        }
            // Partner van
        $upiFundReceiveService = DB::table('global_services')
            ->where('service_id', 'srv_1635429299')->first();
        $upiFundReceiveUrl = asset('').'/media/logos/'.$upiFundReceiveService->url;

        foreach ($upiFundReceiveCallback as $value) {
            $ordersData .= '<a href="'.url('user/van-callbacks').'?bank_ref='.$value->utr.'"  style="text-decoration:none;"><div class="search-suggestions-group"><div class="ssg-header"><img src="'.$upiFundReceiveUrl.'" height="40" width="40"/>
            <div class="ssg-name" style="margin-left:20px;"> '.$value->utr.' </div><div class="ssg-info"></div></div><div class="ssg-content"><div class="ssg-items ssg-items-list"></div></div></div></a>';
        }

             // AEPS Transactions
        $aepsService = DB::table('global_services')
             ->where('service_id', AEPS_SERVICE_ID)->first();
         $aepsUrl = asset('').'/media/logos/'.$aepsService->url;

         foreach ($aepsData as $value) {
             $ordersData .= '<a href="'.url('aeps/transactions').'?bank_ref='.$value->utr.'"  style="text-decoration:none;"><div class="search-suggestions-group"><div class="ssg-header"><img src="'.$aepsUrl.'" height="40" width="40"/>
             <div class="ssg-name" style="margin-left:20px;"> '.$value->utr.' </div><div class="ssg-info"></div></div><div class="ssg-content"><div class="ssg-items ssg-items-list"></div></div></div></a>';
         }
        $data['message'] = 'Records found successfully.';
        $data['status'] = true;
        if(!empty($ordersData)) {
            $data['data'] = $ordersData;
        } else {
            $data['data'] ='<div class="search-suggestions-group"><div class="ssg-header">
            <div class="ssg-name" style="margin-left:20px;color:red;"> No records found. </div><div class="ssg-info"></div></div><div class="ssg-content"><div class="ssg-items ssg-items-list"></div></div></div>';
        }

        return $data;
    }
}
