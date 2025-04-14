<?php

namespace App\Http\Controllers;

use App\Models\Agent;
use App\Models\AepsTransaction;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Helpers\NumberFormat;
use App\Models\UserService;
use Illuminate\Support\Facades\DB;
use App\Helpers\ResponseHelper;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Validator;
use CommonHelper;
use Rap2hpoutre\FastExcel\FastExcel;

class Resellers extends Controller
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
        if (Auth::user()->hasRole('reseller')) {
            session(['is_theme_change' => Auth::user()->is_theme_change]);

            $data['page_title'] = "Dashboard";
            $data['site_title'] = "Dashboard";
            $data['view'] = "reseller.dashboard";

            $data['userList'] = DB::table('users')
                ->select('id', 'name', 'email')
                ->where('is_admin', '1')
                // ->where('reseller' , '10')
                ->orderBy('name', 'asc')
                ->get();

            $data['serviceList'] = DB::table('global_services')
                ->select('service_id', 'service_name')
                ->where('is_active', '1')
                ->orderBy('service_name', 'asc')
                ->get();

            return view($data['view'])->with($data);
        } else {
            $data['url'] = url('admin/dashboard');
            return view('errors.401')->with($data);
        }
    }

    public function dashboardBalances()
    {
        if (Auth::user()->hasRole('reseller')) {
             $commonHelper = new CommonHelper();
             $resellerassign = $commonHelper->getUsersAssignedToReseller();
            $resellerassignUsers = User::whereIn('id', $resellerassign)->get();
            $totalTransactionAmount = $resellerassignUsers->sum('transaction_amount');
            $return['primary'] = $totalTransactionAmount;

            $resellerassignUsers = UserService::whereIn('user_id', $resellerassign)->get();
            $totalTransactionAmount = $resellerassignUsers->sum('transaction_amount');
            $return['payout'] = $totalTransactionAmount;

            $returnOrderQueue = DB::table('orders')->whereIn('user_id', $resellerassign)->where('status', 'queued')->get();
            $totalTransactionAmount = 0;
            $totalOrderCount = 0;

            foreach ($returnOrderQueue as $order) {
                $totalAmountWithFeeAndTax = $order->amount + $order->fee + $order->tax;
                $totalTransactionAmount += $totalAmountWithFeeAndTax;
                $totalOrderCount++;
            }
            $return['orderQueue'] = $totalTransactionAmount;
            $return['orderQueueCount'] = $totalOrderCount;

            // $returnOrderProcess = DB::table('orders')
            //     ->select(
            //         DB::raw("SUM(amount+fee+tax) AS amt"),
            //         DB::raw("COUNT(id) AS counts")
            //     )
            //     ->where('status', 'processing')
            //     ->first();
            $returnOrderQueue = DB::table('orders')->whereIn('user_id', $resellerassign)->where('status', 'processing')->get();
            $totalTransactionAmount = 0;
            $totalOrderCount = 0;

            foreach ($returnOrderQueue as $order) {
                $returnOrderProcess = $order->amount + $order->fee + $order->tax;
                $totalTransactionAmount += $returnOrderProcess;
                $totalOrderCount++;
            }
            $return['orderProcess'] = $totalTransactionAmount;
            $return['orderProcessCount'] = $totalOrderCount;


            $return['primaryActual'] = number_format($return['primary'], 2);
            $return['primary'] = NumberFormat::init()->change($return['primary'], 2);
            $return['payoutActual'] = number_format($return['payout'], 2);
            $return['payout'] = NumberFormat::init()->change($return['payout'], 2);
            $return['orderQueueActual'] = number_format($return['orderQueue'], 2);
            $return['orderQueue'] = NumberFormat::init()->change($return['orderQueue'], 2);
            $return['orderProcessActual'] = number_format($return['orderProcess'], 2);
            $return['orderProcess'] = NumberFormat::init()->change($return['orderProcess'], 2);

            return response()->json($return);
    } else {
        $data['url'] = url('admin/dashboard');
        return view('errors.401')->with($data);
    }
    }

    public function allTransaction(Request $request)
    {
        if (Auth::user()->hasRole('reseller')) {
            $commonHelper = new CommonHelper();
            $data['page_title'] = "All Transactions";
            $data['site_title'] = "All Transactions";
            $data['view'] = "reseller.alltransaction";
            $data['user'] = DB::table('users')->select('name', 'email', 'id', 'mobile')->where('is_admin', '0')->get();
            $data['resellerassign'] = $commonHelper->getUsersAssignedToReseller();
            // dd($data['resellerassign']);
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
    public function resellerList(Request $request)
    {
        if (Auth::user()->hasRole('reseller')) {
            // $commonHelper = new CommonHelper();
            $data['page_title'] = "Reseller";
            $data['site_title'] = "Reseller";
            $data['view'] = "reseller.resellerList";
            $data['user'] = DB::table('users')->select('name', 'email', 'id', 'mobile')->where('is_admin', '0')->get();
            $id = 0;
            return view($data['view'], compact('id'))->with($data);
        } else {
            $data['url'] = url('admin/dashboard');
            return view('errors.401')->with($data);
        }
    }

    public function UpiCollects(Request $request)
    {
        if (Auth::user()->hasRole('reseller')) {
            // $commonHelper = new CommonHelper();
            $data['page_title'] = "Reseller";
            $data['site_title'] = "Reseller";
            $data['view'] = "reseller.upicollectList";
            $data['user'] = DB::table('users')->select('name', 'email', 'id', 'mobile')->where('is_admin', '0')->get();
            $id = 0;
            return view($data['view'], compact('id'))->with($data);
        } else {
            $data['url'] = url('admin/dashboard');
            return view('errors.401')->with($data);
        }
    }

    public function Payout(Request $request)
    {
        if (Auth::user()->hasRole('reseller')) {
            // $commonHelper = new CommonHelper();
            $data['page_title'] = "Reseller";
            $data['site_title'] = "Reseller";
            $data['view'] = "reseller.payout";
            $data['user'] = DB::table('users')->select('name', 'email', 'id', 'mobile')->where('is_admin', '0')->get();
            $id = 0;
            return view($data['view'], compact('id'))->with($data);
        } else {
            $data['url'] = url('admin/dashboard');
            return view('errors.401')->with($data);
        }
    }

    public function transactionReport(Request $request)
    {
        if (Auth::user()->hasRole('reseller')) {
            $service = $request->service;
            switch ($service) {
                case 'payout':
                    $commonHelper = new CommonHelper();
                    $data['page_title'] = "Payout Report";
                    $data['site_title'] = "Users";
                    $data['view'] = "reseller.payout_reports";
                    $resellerassign = $commonHelper->getUsersAssignedToReseller();
                    $data['userData'] = DB::table('users')->select(DB::raw('id,concat(name," (",email,")") as userName'))
                    ->whereIn('id', $resellerassign)->where('is_admin', '0')->get();
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
                    $commonHelper = new CommonHelper();
                    $data['page_title'] = "UPI Report";
                    $data['site_title'] = "Users";
                    $data['view'] = "reseller.upireports";
                    $resellerassign = $commonHelper->getUsersAssignedToReseller();
                    $data['userData'] = DB::table('users')->select(DB::raw('id,concat(name," (",email,")") as userName'))
                    ->whereIn('id', $resellerassign)->where('is_admin', '0')->get();
                   
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
                $commonHelper = new CommonHelper();
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

    public function getActiveTxnDetail(Request $request)
    {
        try {
            if (!Auth::user()->hasRole('reseller')) {

                $validator = Validator::make(
                    $request->all(),
                    [
                        'rowNum' => "required|numeric|min:1",
                        'date' => "required|date",
                        'service' => "required"
                    ]
                );

                if ($validator->fails()) {
                    $message = json_decode(json_encode($validator->errors()), true);
                    return ResponseHelper::missing($message);
                }

                $date = trim($request->date);
                $userId = trim($request->rowNum);
                $service = trim($request->service);


                $dataInfo = (new AdminCommonHelper())->getActiveTxnInfo($service, $userId, $date);

                return ResponseHelper::success('success', $dataInfo);
            } else {
                return abort(404);
            }
        } catch (Exception $e) {
            return ResponseHelper::swwrong($e->getMessage());
        }
    }

    public function getRecords(Request $request)
    {

        $validator = Validator::make(
            $request->all(),
            [
                // 'service' => "required",
                'type' => "nullable|in:business,earning,success,failed,processed,processing,pending",
                'fromDate' => "nullable|date",
                'toDate' => "nullable|date|after_or_equal:fromDate",
            ]
        );


        if ($validator->fails()) {
            $message = json_decode(json_encode($validator->errors()), true);
            return ResponseHelper::missing('Some params are missing.', $message);
        }


        $service = trim($request->service);
        $type = trim($request->type);
        $data = [];

        $fromDate = date('Y-m-d H:i:s');
        $toDate = $fromDate;

        if (!empty($request->fromDate))
            $fromDate = trim($request->fromDate);

        if (!empty($request->toDate))
            $toDate = trim($request->toDate);


        switch ($service) {

            case 'payout':
                $commonHelper = new CommonHelper();
                    $resellerassign = $commonHelper->getUsersAssignedToReseller();
                    $settlement = DB::table('orders')->whereIn('user_id', $resellerassign)
                    ->where('status', $type)
                    // ->whereDate('created_at', '>=', $fromDate)
                    // ->whereDate('created_at', '<=', $toDate)
                    ->get();
                    $totalAmount = $settlement->sum('amount');
                    $totalCount = $settlement->count();
                $data['status'] = true;
                $data['type'] = 'payout_' . $type;
                $data['totalAmount'] = CommonHelper::numberFormat($totalAmount);
                $data['totalCount'] = $totalCount;
                break;

            case 'upi':
                $commonHelper = new CommonHelper();
                    $resellerassign = $commonHelper->getUsersAssignedToReseller();
                    $aeps = DB::table('upi_collects')->whereIn('user_id', $resellerassign)
                    ->where('status', $type)
                    // ->whereDate('created_at', '>=', $fromDate)
                    // ->whereDate('created_at', '<=', $toDate)
                    ->get();
                    $totalAmount = $aeps->sum('amount');
                    $totalCount = $aeps->count();
                $data['status'] = true;
                $data['type'] = 'aeps_' . $type;
                $data['totalAmount'] = CommonHelper::numberFormat($totalAmount);
                $data['totalCount'] = $totalCount;
                break;

            case 'dmt':
                $matm = DB::connection('slave')
                    ->table('dmt_fund_transfers')
                    ->select(DB::raw('sum(amount) as totalAmount, count(id) as totalCount'))
                    ->where('status', $type)
                    ->whereDate('created_at', '>=', $fromDate)
                    ->whereDate('created_at', '<=', $toDate)
                    ->first();
                $data['status'] = true;
                $data['type'] = 'dmt_' . $type;
                $data['totalAmount'] = CommonHelper::numberFormat($matm->totalAmount);
                $data['totalCount'] = $matm->totalCount;
                break;

            case 'recharge':
                $matm = DB::connection('slave')
                    ->table('recharges')
                    ->select(DB::raw('sum(amount) as totalAmount, count(id) as totalCount'))
                    ->where('status', $type)
                    ->whereDate('created_at', '>=', $fromDate)
                    ->whereDate('created_at', '<=', $toDate)
                    ->first();
                $data['status'] = true;
                $data['type'] = 'recharge_' . $type;
                $data['totalAmount'] = CommonHelper::numberFormat($matm->totalAmount);
                $data['totalCount'] = $matm->totalCount;
                break;

            case 'matm':
                $matm = DB::connection('slave')
                    ->table('matm_transactions')
                    ->select(DB::raw('sum(transaction_amount) as totalAmount, count(id) as totalCount'))
                    ->where('status', $type)
                    ->whereDate('created_at', '>=', $fromDate)
                    ->whereDate('created_at', '<=', $toDate)
                    ->first();
                $data['status'] = true;
                $data['type'] = 'matm_' . $type;
                $data['totalAmount'] = CommonHelper::numberFormat($matm->totalAmount);
                $data['totalCount'] = $matm->totalCount;
                break;

            case 'pancard':
                $matm = DB::connection('slave')
                    ->table('pan_txns')
                    ->select(DB::raw('sum(fee) as totalAmount, count(id) as totalCount'))
                    ->where('status', $type)
                    ->whereDate('created_at', '>=', $fromDate)
                    ->whereDate('created_at', '<=', $toDate)
                    ->first();
                $data['status'] = true;
                $data['type'] = 'pancard_' . $type;
                $data['totalAmount'] = CommonHelper::numberFormat($matm->totalAmount);
                $data['totalCount'] = $matm->totalCount;
                break;

            case 'validation':
                $matm = DB::connection('slave')->table('validations')
                    ->select(DB::raw('sum(fee) as totalAmount, count(id) as totalCount'))
                    ->where('status', $type)
                    ->whereDate('created_at', '>=', $fromDate)
                    ->whereDate('created_at', '<=', $toDate)
                    ->first();
                $data['status'] = true;
                $data['type'] = 'validation_' . $type;
                $data['totalAmount'] = CommonHelper::numberFormat($matm->totalAmount);
                $data['totalCount'] = $matm->totalCount;
                break;

            default:
                $data['status'] = false;
                $data['type'] = '';
                $data['totalAmount'] = 0;
                $data['totalCount'] = 0;
                break;
        }

        return ResponseHelper::success('Record fetched successfully', $data);
    }

}