<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\AepsTransaction;
use App\Models\User;
use App\Models\Order;
use App\Models\FundReceiveCallback;
use App\Models\UPICallback;
use App\Models\Transaction;
use App\Models\Contact;
use App\Models\AutoCollectCallback;
use App\Models\UserSettlement;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Rap2hpoutre\FastExcel\FastExcel;

class UserReportController extends Controller
{

    public function index()
    {
        $data['page_title'] =  "All Reports";
        $data['site_title'] =  "All Reports";
        $data['view']       = ADMIN . '/' . ".reports.reports";
        $data['dateFrom']   = date('Y-m-d');
        $data['dateTo']     = date('Y-m-d');
        $data['userData']   = DB::table('users')->select(DB::raw('id,concat(name," (",email,")") as userName'))->where('is_admin', '0')->get();

        return view($data['view'])->with($data);
    }

    public function transactionReport(Request $request)
    {
        $service = $request->service;
        switch ($service) {
            case 'payout':
                $data['userData'] = DB::table('users')->select(DB::raw('id,concat(name," (",email,")") as userName'))->where('is_admin', '0')->get();

                $fromDate = $toDate = date('Y-m-d');

                //query for total success order
                $sqlQuery = DB::table('orders')
                    ->select(
                        DB::raw("sum(orders.amount) AS totalAmount"),
                        DB::raw("COUNT(orders.id) AS totalCount"),
                        DB::raw("sum(orders.fee) AS totalFee"),
                        DB::raw("sum(orders.tax) AS totalTax"),
                    )->where('orders.status', 'processed');

                if ($request->isMethod('post')) {
                    if (!empty($request->from)) {
                        $fromDate = $request->from;
                    }

                    if (!empty($request->to)) {
                        $toDate = $request->to;
                    }

                    $sqlQuery->whereDate('orders.created_at', '>=', $fromDate)
                        ->whereDate('orders.created_at', '<=', $toDate);

                    if (!empty($request->user_id)) {
                        $sqlQuery->where('orders.user_id', $request->user_id);
                    }

                    $result['success'] = $sqlQuery->first();
                    return response()->json($result);
                }

                $sqlQuery->whereDate('orders.created_at', '>=', $fromDate)
                    ->whereDate('orders.created_at', '<=', $toDate);

                $data['totalAmount']['success'] = $sqlQuery->first();
                $data['dateFrom'] = $fromDate;
                $data['dateTo'] = $toDate;

                break;

            case 'upi':
                $data['userData'] = User::select(DB::raw('id,concat(name," (",email,")") as userName'))->where('is_admin', '0')->get();

                $fromDate = $toDate = date('Y-m-d');

                //query for total Static QR
                $sqlQuery = DB::table('upi_callbacks')
                    ->select(
                        DB::raw("SUM(amount) AS totalAmount"),
                        DB::raw("COUNT(id) AS totalCount")
                    );
                // $sqlQuery = DB::table('transactions')
                //     ->select(
                //         DB::raw("sum(tr_amount) AS totalAmount"),
                //         DB::raw("COUNT(id) AS totalCount")
                //     )
                //     ->whereIn('tr_identifiers', ['upi_inward_credit', 'upi_stack_vpa_fee', 'upi_stack_verify_fee']);

                $getUpiStackFeeAndTax = DB::table('transactions')
                    ->select(
                        DB::raw("sum(tr_fee) AS totalUpiFee"),
                        DB::raw("sum(tr_tax) AS totalUpiTax"),
                        DB::raw("COUNT(id) AS totalCount")
                    )
                    ->whereIn('tr_identifiers', ['upi_inward_credit', 'upi_stack_vpa_fee', 'upi_stack_verify_fee']);

                if ($request->isMethod('post')) {
                    if (!empty($request->from)) {
                        $fromDate = $request->from;
                    }

                    if (!empty($request->to)) {
                        $toDate = $request->to;
                    }

                    $sqlQuery->whereDate('created_at', '>=', $fromDate)
                        ->whereDate('created_at', '<=', $toDate);

                    $getUpiStackFeeAndTax->whereDate('created_at', '>=', $fromDate)
                        ->whereDate('created_at', '<=', $toDate);

                    if (!empty($request->user_id)) {
                        $sqlQuery->where('user_id', $request->user_id);
                        $getUpiStackFeeAndTax->where('user_id', $request->user_id);
                    }

                    $result['upi_stack'] = $sqlQuery->first();
                    $result['upi_stack_fee_and_tax'] = $getUpiStackFeeAndTax->first();
                    return response()->json($result);
                }


                $sqlQuery->whereDate('created_at', '>=', $fromDate)
                    ->whereDate('created_at', '<=', $toDate);

                $getUpiStackFeeAndTax->where('tr_identifiers', 'van_inward_credit');

                $data['totalAmount']['upi_stack'] = $sqlQuery->first();
                $data['totalAmount']['upi_stack_fee_and_tax'] = $getUpiStackFeeAndTax->first();
                $data['dateFrom'] = $fromDate;
                $data['dateTo'] = $toDate;
                break;

            case 'van':
                $data['userData'] = User::select(DB::raw('id,concat(name," (",email,")") as userName'))->where('is_admin', '0')->get();

                $fromDate = $toDate = date('Y-m-d');

                $sqlQuery = DB::table('fund_receive_callbacks')
                    ->select(
                        DB::raw("sum(amount) AS totalAmount"),
                        DB::raw("COUNT(id) AS totalCount")
                    );

                $getPartnerFeeAndTax = DB::table('transactions')
                    ->select(
                        DB::raw("sum(tr_fee) AS totalPartnerFee"),
                        DB::raw("sum(tr_tax) AS totalPartnerTax"),
                        DB::raw("COUNT(id) AS totalCountPartnerFeeAndTax")
                    )->where('tr_identifiers', 'van_inward_credit');

                $sqlQuerySmartCollect = DB::table('cf_merchants_fund_callbacks')
                    ->select(
                        DB::raw("sum(amount) AS totalAmountSmartCollect"),
                        DB::raw("COUNT(id) AS totalCountSmartCollect")
                    );

                $getSmartCollectFeeAndTax = DB::table('transactions')
                    ->select(
                        DB::raw("sum(tr_fee) AS totalSmartCollectFee"),
                        DB::raw("sum(tr_tax) AS totalSmartCollectTax"),
                        DB::raw("COUNT(id) AS totalCountSmartCollectFeeAndTax")
                    )->whereIn('tr_identifiers',['smart_collect_vpa_fee','smart_collect_van_fee','smart_collect_vpa','smart_collect_van']);

                if ($request->isMethod('post')) {
                    if (!empty($request->from)) {
                        $fromDate = $request->from;
                    }

                    if (!empty($request->to)) {
                        $toDate = $request->to;
                    }

                    $sqlQuery->whereDate('created_at', '>=', $fromDate)
                        ->whereDate('created_at', '<=', $toDate);

                    $getPartnerFeeAndTax->whereDate('created_at', '>=', $fromDate)
                        ->whereDate('created_at', '<=', $toDate);

                    $sqlQuerySmartCollect->whereDate('created_at', '>=', $fromDate)
                        ->whereDate('created_at', '<=', $toDate);

                    $getSmartCollectFeeAndTax->whereDate('created_at', '>=', $fromDate)
                        ->whereDate('created_at', '<=', $toDate);

                    if (!empty($request->user_id)) {
                        $sqlQuery->where('user_id', $request->user_id);
                        $getPartnerFeeAndTax->where('user_id', $request->user_id);
                        $sqlQuerySmartCollect->where('user_id', $request->user_id);
                        $getSmartCollectFeeAndTax->where('user_id', $request->user_id);
                    }

                    $result = [$sqlQuery->first(), $getPartnerFeeAndTax->first(), $sqlQuerySmartCollect->first(), $getSmartCollectFeeAndTax->first()];

                    return response()->json($result);
                }

                $sqlQuery->whereDate('created_at', '>=', $fromDate)
                    ->whereDate('created_at', '<=', $toDate);

                $getPartnerFeeAndTax = $getPartnerFeeAndTax->whereDate('created_at', '>=', $fromDate)
                    ->whereDate('created_at', '<=', $toDate);

                $sqlQuerySmartCollect = $sqlQuerySmartCollect->whereDate('created_at', '>=', $fromDate)
                    ->whereDate('created_at', '<=', $toDate);

                $getSmartCollectFeeAndTax = $getSmartCollectFeeAndTax->whereDate('created_at', '>=', $fromDate)
                    ->whereDate('created_at', '<=', $toDate);

                $data['totalAmount'] = [$sqlQuery->first(), $getPartnerFeeAndTax->first(), $sqlQuerySmartCollect->first(), $getSmartCollectFeeAndTax->first()];
                $data['dateFrom'] = $fromDate;
                $data['dateTo'] = $toDate;
                break;

            case 'aeps':
                $data['userData'] = User::select(DB::raw('id,concat(name," (",email,")") as userName'))->where('is_admin', '0')->get();
                $query = AepsTransaction::select(DB::raw('sum(transaction_amount) as totalAmount,count(id) as totalCount'));
                if ($request->isMethod('post')) {
                    if ((isset($request->from) && !empty($request->from)) && (isset($request->to) && !empty($request->to))) {
                        if ($request->from == $request->to) {
                            $query->whereDate('created_at', '=', Carbon::createFromFormat('Y-m-d', $request->from)->format('Y-m-d'));
                        } else {
                            $query->whereBetween('created_at', [Carbon::createFromFormat('Y-m-d', $request->from)
                                ->format('Y-m-d'), Carbon::createFromFormat('Y-m-d', $request->to)->addDay(1)->format('Y-m-d')]);
                        }
                    }
                    if (isset($request->user_id) && !empty($request->user_id)) {
                        $query->where('user_id', $request->user_id);
                    }
                    $result = $query->first();
                    echo json_encode($result);
                    exit;
                }
                $data['totalAmount'] = $query->first();
                break;

            default:
                return abort(404);
                break;
        }

        return view($data['view'])->with($data);
    }

    public function excelDownload()
    {
        $data['page_title'] =  "All Excel File Download";
        $data['site_title'] =  "All Reports";
        $data['view']       = USER . '/' . ".download.list";
        $data['user']       = DB::table('users')->select('id', 'name', 'email', 'mobile')->where('is_admin', '0')->get();
        return view($data['view'])->with($data);
    }

    public function excelDownloadLink($id)
    {
        $data = DB::table('excel_reports')
            ->select('file_url')->where('id', $id)->first();
            if (isset($data)) {
                $pathToFile = storage_path('app/'.$data->file_url);
                return response()->download($pathToFile);
            } else {
                return response()->json(array('message' => 'no records founds'));
            }
    }

    public function getCountRecord()
    {
        return DB::table('excel_reports')
        ->count();
    }

    public function ajaxGenerateExcelFile(Request $request)
    {
        $resp['status'] = false;
        $resp['message'] = '';
        $resp['count'] = 0;
        if (isset($request->reports) && isset($request->from) && isset($request->to) && isset($request->userId))  {
           $reportName = $request->reports;
            $loginUserId = Auth::user()->id;
            $userId = $request->userId;
            $type = 0;
            // if ($userId[0] == 0) {
            //     $type = 1;
            // }

            $startDate = $request->from;
            $endDate = $request->to;
            $checkExcits = DB::table('excel_reports')
            ->where([
                'user_id' => $loginUserId,
                'start_date' => $startDate,
                'end_date' => $endDate,
                'file_name' => $reportName
            ])->first();
            if (!isset($checkExcits)) {
                if($reportName=='UserTransactions')
                {
                   $data = \App\Jobs\MultipleExcelFileDownloadJob::dispatch($loginUserId, $startDate, $endDate, $userId, $reportName, $type, ''); 
                }else
                {
                    $filename = $startDate.time().'.xlsx';
                    $data = $this->downloadExcel($loginUserId,$startDate, $endDate, $userId, $reportName, $type,$filename);
                    
                    // if (isset($userId)) {
                    //     $userId = implode(",",$userId);
                    // }
                    DB::table('excel_reports')
                        ->insert([
                            'user_id' => $loginUserId,
                            'start_date' => $startDate,
                            'end_date' => $endDate,
                            'file_name' => $reportName,
                            'search_key' => $userId.' '.$reportName,
                            'file_url' => date('Y-m-d').'/'.$loginUserId.'/'.$filename
                        ]);
                }
                
                
                $resp['status'] = true;
                $resp['message'] = 'This report is generated sucessfully.';
                $resp['count'] =  DB::table('excel_reports')
                ->count() - 1;
            } else {
                $resp['status'] = false;
                $resp['message'] = 'This report already created.';
            }
        } else {
            $resp['status'] = false;
            $resp['message'] = 'Reports , from  and to fields are required';
        }

        return response()->json($resp);
    }

    public function removeExportFile(Request $request,$id)
    {
        if($id)
        {
            $checkExcits = DB::table('excel_reports')->where('id',$id)->first();
            if($checkExcits)
            {
                DB::table('excel_reports')->where('id', '=', $id)->delete();
                unlink(storage_path('app/'.$checkExcits->file_url));
                $resp['status'] = true;
                $resp['message'] = 'This report file is removed sucessfully.';
                $resp['count'] =  DB::table('excel_reports')
                ->count();
                return response()->json($resp);
            }
        }
    }

    public function downloadUser()
    {
        ini_set('max_execution_time',1800);
        ini_set('memory_limit',"5096000000");
            function usersGenerator()
            {
                  foreach (Order::where(DB::raw('date(created_at)'),'2022-02-10')->cursor() as $user) {
                      yield $user;
                  }
            }
            $filename = time().'.csv';
            return (new FastExcel(usersGenerator()))->export($filename,function($user){
                return [
                    'Contact ID' => $user->contact_id,
                    'User Id'=>$user->user_id,
                    'Payout Id'=>$user->payout_id,
                    'Batch Id'=>$user->batch_id,
                    'Order Ref Id'=>$user->order_ref_id,
                    'Amount'=>$user->amount,
                    'Fee'=>$user->amount,
                    'Tax'=>$user->tax,
                    'Fee'=>$user->fee
                ];
            });
    }

    public function downloadExcel($loginUserId,$startDate,$endDate,$userId,$reportName,$type,$filename)
    {
        ini_set('max_execution_time',1800);
        ini_set('memory_limit',"5096000000");
        
        function usersGenerator($reportName,$startDate,$endDate,$userId,$type)
            {
                switch($reportName){
                    case 'Smart_Payout':
                        $sqlModel = Order::select('contacts.first_name as contact_first_name','contacts.last_name as contact_last_name','contacts.email as contact_email','contacts.phone as contacts_phone','contacts.type as contact_type','contacts.account_number as contact_account_number','contacts.account_ifsc as contacts_account_ifsc','contacts.vpa_address as vpaAddress','contacts.reference as contacts_reference','client_ref_id','batch_id','order_ref_id','amount','fee','tax','mode','purpose','narration','remark','orders.udf1','orders.udf2','status','bank_reference','orders.created_at')->join('contacts','contacts.contact_id','=','orders.contact_id')->where('status','processed');
                        if ($type == 0) {
                            $sqlModel = $sqlModel->where('orders.user_id', $userId);
                        }
                        $sqlModel->whereBetween('orders.created_at', [$startDate.' 00:00:00', $endDate.' 23:59:59']);
                        break;
                    case 'AutoSettlement':
                        $sqlModel = UserSettlement::on('slave')->select('settlement_ref_id as ref_id','mode','amount','fee','tax','account_number','account_ifsc','beneficiary_name','status','created_at')->where('status','processed');
                        if ($type == 0) {
                            $sqlModel = $sqlModel->where('user_id', $userId);
                        }
                        $sqlModel->whereBetween('created_at', [$startDate.' 00:00:00', $endDate.' 23:59:59']);
                        break;
                    case 'UPI_Stack':
                        $sqlModel = UPICallback::select('payee_vpa','amount','fee','tax','txn_note','npci_txn_id','original_order_id','merchant_txn_ref_id','bank_txn_id','customer_ref_id','payer_vpa','payer_acc_name','payer_mobile','created_at');
                        if ($type == 0) {
                            $sqlModel = $sqlModel->where('upi_callbacks.user_id', $userId);
                        }
                        $sqlModel->whereBetween('created_at', [$startDate.' 00:00:00', $endDate.' 23:59:59']);
                        break;
                    case 'Smart_Collect':
                        $sqlModel = AutoCollectCallback::select('v_account_id','virtual_vpa_id','v_account_number','amount','fee','tax','utr','credit_ref_no','remitter_account','remitter_name','remitter_vpa','created_at');
                        if ($type == 0) {
                            $sqlModel = $sqlModel->where('cf_merchants_fund_callbacks.user_id', $userId);
                        }
                        $sqlModel->whereBetween('created_at', [$startDate.' 00:00:00', $endDate.' 23:59:59']);
                        break;

                    case 'Patner_VAN':
                        $sqlModel = FundReceiveCallback::select('v_account_id','v_account_number','amount','fee','tax','utr','reference_id','remitter_account','remitter_ifsc','remitter_name','created_at');

                        if ($type == 0) {
                            $sqlModel = $sqlModel->where('fund_receive_callbacks.user_id', $userId);
                        }
                        $sqlModel->whereBetween('created_at', [$startDate.' 00:00:00', $endDate.' 23:59:59']);
                        break;
                    case 'Transactions':
                        $sqlModel = Transaction::select('trans_id','txn_id','txn_ref_id','account_number','user_id','order_id','tr_type','tr_amount','tr_total_amount','tr_fee','tr_tax','closing_balance','tr_date','tr_identifiers','udf1','udf2','udf3','udf4','created_at');
                        if ($type == 0) {
                            $sqlModel = $sqlModel->where('transactions.user_id', $userId);
                        }
                        $sqlModel->whereBetween('created_at', [$startDate.' 00:00:00', $endDate.' 23:59:59']);
                        break;
                    case 'Contacts':
                        $sqlModel = Contact::select('contact_id','first_name','last_name','email','phone','account_number','account_ifsc','vpa_address','created_at');
                        if ($type == 0) {
                            $sqlModel = $sqlModel->where('user_id', $userId);
                        }
                        //$sqlModel->whereBetween('created_at', [$startDate.' 00:00:00', $endDate.' 23:59:59']);
                        break;
                    case 'Users':
                        $sqlModel = User::select('users.name','users.email','users.mobile','business_infos.business_name','business_infos.gstin','business_infos.address','users.created_at')
                        ->leftJoin('business_infos','users.id','=','business_infos.user_id')
                        ->where('users.is_admin','0');
                        if ($type == 0) {
                            $sqlModel = $sqlModel->where('users.id', $userId);
                        }
                        //$sqlModel->whereBetween('users.created_at', [$startDate.' 00:00:00', $endDate.' 23:59:59']);
                        break;
                    case 'AEPS':
                        $sqlModel = AepsTransaction::select('merchant_code','client_ref_id','transaction_type',DB::raw("CONCAT('********', SUBSTR(aadhaar_no,-4)) AS aadhaar_no"),'mobile_no','transaction_amount', 'commission', 'rrn','status','created_at');
                        if ($type == 0) {
                            $sqlModel = $sqlModel->where('user_id', $userId);
                        }
                        $sqlModel->where('status', 'success');
                        $sqlModel->whereIn('transaction_type', ['ms', 'cw']);
                        $sqlModel->whereBetween('created_at', [$startDate.' 00:00:00', $endDate.' 23:59:59']);
                        break;

                    }
              foreach ($sqlModel->cursor() as $user) {
                    $user->CreatedAt = $user->created_at->format('Y-m-d H:i:s');
                    unset($user->created_at);

                  yield $user;
                    
              }
            }
            $path = storage_path('app/'.date('Y-m-d').'/'.$loginUserId.'/');
            $filename = $path.$filename;
            if(!is_dir($path)) {
                mkdir($path,0777,true);
            }
            return (new FastExcel(usersGenerator($reportName,$startDate,$endDate,$userId,$type)))->export($filename);

        
    }


}
