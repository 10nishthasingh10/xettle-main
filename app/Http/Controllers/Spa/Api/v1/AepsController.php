<?php

namespace App\Http\Controllers\Spa\Api\v1;

use App\Helpers\CommonHelper;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Helpers\ResponseHelper;
use DateTime;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;


class AepsController extends Controller
{

    /**
     * Dashboard Card Data
     */
    public function index(Request $request)
    {
        try {
            $validator = Validator::make(
                $request->all(),
                [
                    'startDate' => 'required|date|before_or_equal:endDate',
                    'endDate' => 'required|date'
                ]
            );


            if ($validator->fails()) {
                $message = json_decode(json_encode($validator->errors()), true);
                return ResponseHelper::missing($message);
            }

            $userId = Auth::user()->id;
            $startDate = !empty($request->startDate) ? trim($request->startDate) : date('Y-m-d');
            $endDate = !empty($request->endDate) ? trim($request->endDate) : date('Y-m-d');


            $amount = DB::connection('slave')
                ->table('aeps_transactions')
                ->select(DB::raw('sum(transaction_amount) as totAmt'), DB::raw('sum(fee+tax) as feeAndTax'))
                ->where('user_id', $userId)
                ->where('status', 'success')
                ->whereDate('created_at', '>=', $startDate)
                ->whereDate('created_at', '<=', $endDate)
                ->whereIn('transaction_type', ['cw', 'ap'])
                ->first();

            $amount_ap = DB::connection('slave')
                ->table('aeps_transactions')
                ->select(DB::raw('sum(fee+tax) as ap_feeAndTax'))
                ->where('user_id', $userId)
                ->where('transaction_type', 'ap')
                ->where('status', 'success')
                ->whereDate('created_at', '>=', $startDate)
                ->whereDate('created_at', '<=', $endDate)
                ->first();

            $settledAmount = DB::connection('slave')
                ->table('transactions')
                ->select(DB::raw('sum(tr_amount) as SettAmount'))
                ->where('user_id', $userId)
                ->where('status', 'success')
                ->whereDate('created_at', '>=', $startDate)
                ->whereDate('created_at', '<=', $endDate)
                ->whereIn('tr_identifiers', ['aeps_apinward_credit', 'aeps_inward_credit'])
                ->first();


            $merchant = DB::connection('slave')
                ->table('agents')
                ->leftJoin('aeps_transactions', 'aeps_transactions.merchant_code', 'agents.merchant_code')
                ->where('agents.user_id', $userId)
                ->whereDate('aeps_transactions.created_at', '>=', $startDate)
                ->whereDate('aeps_transactions.created_at', '<=', $endDate)
                ->distinct('agents.merchant_code')
                ->count();

            $commission = DB::connection('slave')
                ->table('aeps_transactions')
                ->select(DB::raw('sum(commission) as totalAmount,count(id) as totalCount'))
                ->where(['status' =>  'success'])
                ->where('user_id',  $userId)
                ->where('commission', '!=', 0)
                ->whereIn('transaction_type', ['cw', 'ms'])
                ->whereDate('aeps_transactions.created_at', '>=', $startDate)
                ->whereDate('aeps_transactions.created_at', '<=', $endDate)
                ->first();


            $returnData['merchant'] = CommonHelper::numberFormat($merchant);
            $returnData['amount'] = CommonHelper::numberFormat(@$amount->totAmt - @$amount->feeAndTax);
            $returnData['apCharges'] = CommonHelper::numberFormat(@$amount_ap->ap_feeAndTax);
            $returnData['settAmount'] = CommonHelper::numberFormat(@$settledAmount->SettAmount);
            $returnData['commissionAmount'] = CommonHelper::numberFormat(@$commission->totalAmount);
            $returnData['commissionCount'] = CommonHelper::numberFormat(@$commission->totalCount);

            return ResponseHelper::success('success', $returnData);
        } catch (Exception $e) {
            return ResponseHelper::swwrong('Something went wrong.', $e->getMessage());
        }
    }



    public function merchants(Request $request)
    {
        try {
            $userId = Auth::user()->id; //$request->user()->id;

            $draw = $request->get('draw');
            $per_page = $request->get("per_page"); // total number of rows per page
            $per_page = !empty($per_page) ? $per_page : 10;

            $columnName_arr = $request->get('columns');
            $order_arr = $request->get('order');
            $search_arr = $request->get('search');


            $startDate = trim($request->get('startDate'));
            $endDate = trim($request->get('endDate'));
            $docStatus = strtolower(trim($request->get('docStatus')));
            // $ekycStatus = trim($request->get('ekycStatus'));

            //$columnIndex = $columnIndex_arr[0]['column']; // Column index
            $columnName = !empty($columnName_arr) ? $columnName_arr : 'id'; // Column name
            $columnSortOrder = !empty($order_arr) ? $order_arr : 'desc'; // asc or desc
            $searchValue = $search_arr; // Search value

            $records = DB::table('agents')
                ->select(
                    'merchant_code',
                    'first_name',
                    'last_name',
                    'middle_name',
                    'email_id',
                    'mobile',
                    'address',
                    'dob',
                    'pan_no',
                    'shop_name',
                    'shop_address',
                    'shop_pin',
                    'is_ekyc_documents_uploaded',
                    'documents_status',
                    'documents_remarks',
                    'ekyc',
                    DB::raw('concat("XXXX-XXXX-", right(aadhar_number, 4)) as aadhar_number'),
                    'pin_code',
                    DB::raw("DATE_FORMAT(created_at, '%Y-%m-%d %H:%i:%S') as created_at"),
                )
                ->where('user_id', $userId);


            if (!empty($docStatus)) {
                $records->where('documents_status', '=', $docStatus);
            }

            if (!empty($startDate) && !empty($endDate)) {
                $records->whereDate('created_at', '>=', $startDate)
                    ->whereDate('created_at', '<=', $endDate);
            }


            if (!empty($searchValue)) {
                $records = $records->where(function ($sql) use ($searchValue) {
                    return $sql->orWhere('merchant_code', 'like', '%' . $searchValue . '%')
                        ->orWhere('email_id', 'like', '%' . $searchValue . '%')
                        ->orWhere('pan_no', 'like', '%' . $searchValue . '%')
                        ->orWhere('mobile', 'like', '%' . $searchValue . '%')
                        ->orWhereRaw("CONCAT(`first_name`, ' ', `last_name`) LIKE '%$searchValue%'");
                });
            }

            $records = $records->orderBy($columnName, $columnSortOrder)
                ->paginate($per_page);


            if ($records->isNotEmpty()) {
                $responseData['records'] = $records;
            } else {
                $responseData['records'] = null;
            }

            $responseData['startDate'] = $startDate;
            $responseData['endDate'] = $endDate;
            $responseData['docStatus'] = [
                'pending',
                'accepted',
                'rejected'
            ];

            return ResponseHelper::success('Record fetched successfully.', $responseData);
        } catch (Exception $e) {
            return ResponseHelper::swwrong('Something went wrong.', $e->getMessage());
        }
    }


    public function transactions(Request $request)
    {
        try {

            $userId = Auth::user()->id; //$request->user()->id;

            $draw = $request->get('draw');
            $per_page = $request->get("per_page"); // total number of rows per page
            $per_page = !empty($per_page) ? $per_page : 10;

            $columnName_arr = $request->get('columns');
            $order_arr = $request->get('order');
            $search_arr = $request->get('search');

            $startDate = trim($request->get('startDate'));
            $endDate = trim($request->get('endDate'));
            $txnStatus = strtolower(trim($request->get('txnStatus')));
            $txnType = strtolower(trim($request->get('txnType')));

            //$columnIndex = $columnIndex_arr[0]['column']; // Column index
            // $columnName = !empty($columnName_arr) ? $columnName_arr : 'aeps_transactions.id'; // Column name
            $columnSortOrder = !empty($order_arr) ? $order_arr : 'desc'; // asc or desc
            // $columnName = $columnName_arr; // Column name
            // $columnSortOrder = $order_arr; // asc or desc
            $searchValue = $search_arr; // Search value

            $records = DB::table('aeps_transactions')
                ->select(
                    'aeps_transactions.bankiin',
                    'aeps_transactions.commission',
                    'aeps_transactions.client_ref_id',
                    'aeps_transactions.route_type',
                    'aeps_transactions.transaction_type',
                    'aeps_transactions.resp_bank_message',
                    'aeps_transactions.failed_message',
                    'aeps_transactions.rrn',
                    'aeps_transactions.transaction_amount',
                    'aeps_transactions.resp_stan_no',
                    // 'aeps_transactions.transaction_date',
                    DB::raw("DATE_FORMAT(aeps_transactions.transaction_date, '%Y-%m-%d %H:%i:%S') as transaction_date"),
                    'aeps_transactions.merchant_code',
                    'aeps_transactions.mobile_no',
                    'aeps_transactions.trn_ref_id',
                    'aeps_transactions.status',
                    // 'aeps_transactions.created_at',
                    DB::raw("DATE_FORMAT(aeps_transactions.created_at, '%Y-%m-%d %H:%i:%S') as created_at"),
                    'aeps_transactions.commission_ref_id',
                    DB::raw('concat("XXXX-XXXX-", right(aeps_transactions.aadhaar_no, 4)) as aadhaar_no'),
                    'banks.bank'
                )
                ->leftJoin('banks', 'banks.iin', '=', 'aeps_transactions.bankiin')
                ->where('aeps_transactions.user_id', $userId);


            if (!empty($txnStatus)) {
                $records->where('aeps_transactions.status', '=', $txnStatus);
            }

            if (!empty($txnType)) {
                $records->where('aeps_transactions.transaction_type', '=', $txnType);
            }

            if (!empty($startDate) && !empty($endDate)) {
                $records->whereDate('aeps_transactions.created_at', '>=', $startDate)
                    ->whereDate('aeps_transactions.created_at', '<=', $endDate);
            }


            if (!empty($searchValue)) {
                $records = $records->where(function ($sql) use ($searchValue) {
                    return $sql->orWhere('aeps_transactions.client_ref_id', 'like', '%' . $searchValue . '%')
                        ->orWhere('aeps_transactions.rrn', 'like', '%' . $searchValue . '%')
                        ->orWhere('aeps_transactions.merchant_code', 'like', '%' . $searchValue . '%')
                        ->orWhere('aeps_transactions.trn_ref_id', 'like', '%' . $searchValue . '%')
                        ->orWhere('aeps_transactions.mobile_no', 'like', '%' . $searchValue . '%');
                });
            }

            $records = $records->orderBy('aeps_transactions.id', $columnSortOrder)
                ->paginate($per_page);

            if ($records->isNotEmpty()) {
                $responseData['records'] = $records;
            } else {
                $responseData['records'] = null;
            }

            $responseData['startDate'] = $startDate;
            $responseData['endDate'] = $endDate;
            $responseData['txnStatus'] = [
                'pending',
                'success',
                'failed',
                'disputed'
            ];

            $responseData['txnType'] = [
                'ms',
                'be',
                'ap',
                'cw'
            ];

            return ResponseHelper::success('Record fetched successfully.', $responseData);
            // return ResponseHelper::failed('Record not found.');
        } catch (Exception $e) {
            return ResponseHelper::swwrong('Something went wrong.', $e->getMessage());
        }
    }



    public function settlements(Request $request)
    {
        try {

            $userId = Auth::user()->id; //$request->user()->id;

            $draw = $request->get('draw');
            $per_page = $request->get("per_page"); // total number of rows per page
            $per_page = !empty($per_page) ? $per_page : 10;

            $columnName_arr = $request->get('columns');
            $order_arr = $request->get('order');
            $search_arr = $request->get('search');

            $startDate = trim($request->get('startDate'));
            $endDate = trim($request->get('endDate'));

            //$columnIndex = $columnIndex_arr[0]['column']; // Column index
            // $columnName = $columnName_arr; // Column name
            $columnSortOrder = !empty($order_arr) ? $order_arr : 'desc'; // asc or desc
            // $columnSortOrder = $order_arr; // asc or desc
            $searchValue = $search_arr; // Search value

            $records = DB::table('transactions')
                ->select(
                    'transactions.trans_id',
                    'transactions.txn_id',
                    'transactions.txn_ref_id',
                    'transactions.order_id',
                    'transactions.tr_type',
                    'transactions.tr_amount',
                    'transactions.tr_total_amount',
                    'transactions.tr_fee',
                    'transactions.tr_tds',
                    'transactions.tr_tax',
                    'transactions.closing_balance',
                    'transactions.tr_date',
                    'transactions.tr_reference',
                    DB::raw("DATE_FORMAT(transactions.created_at, '%Y-%m-%d %H:%i:%S') as created_at")
                )
                ->where('transactions.user_id', $userId)
                ->where('transactions.tr_identifiers', 'aeps_inward_credit');


            if (!empty($startDate) && !empty($endDate)) {
                $records->whereDate('transactions.created_at', '>=', $startDate)
                    ->whereDate('transactions.created_at', '<=', $endDate);
            }


            if (!empty($searchValue)) {
                $records = $records->where(function ($sql) use ($searchValue) {
                    return $sql->orWhere('transactions.txn_id', 'like', '%' . $searchValue . '%')
                        ->orWhere('transactions.txn_ref_id', 'like', '%' . $searchValue . '%')
                        ->orWhere('transactions.order_id', 'like', '%' . $searchValue . '%');
                });
            }

            $records = $records->orderBy('transactions.id', $columnSortOrder)
                ->paginate($per_page);


            if ($records->isNotEmpty()) {
                return ResponseHelper::success('Record fetched successfully.', $records);
            }
            return ResponseHelper::failed('Record not found.');
        } catch (Exception $e) {
            return ResponseHelper::swwrong('Something went wrong.', $e->getMessage());
        }
    }


    /**
     * Counts
     */
    public function countStatus(Request $request)
    {
        try {
            $validator = Validator::make(
                $request->all(),
                [
                    'startDate' => "nullable|date:before_or_equal",
                    'endDate' => "nullable|date",
                    'service' => "required|in:doc_kyc,ekyc,aeps_cw,aeps_ap,aeps_ap_charges,aeps_settle,aeps_settled,aeps_commission",
                    'status' => "nullable|in:pending,accepted,rejected,success,failed,disputed",
                ]
            );

            if ($validator->fails()) {
                return ResponseHelper::missing('Missing Params', $validator->errors());
            }


            $serviceType = (trim($request->service));
            $statusType = (trim($request->status));
            $startDate = !empty($request->startDate) ? trim($request->startDate) : date('Y-m-d');
            $endDate = !empty($request->endDate) ? trim($request->endDate) : date('Y-m-d');
            $userId = Auth::user()->id;

            switch ($serviceType) {

                case 'aeps_cw':
                    $settlement = DB::connection('slave')
                        ->table('aeps_transactions')
                        ->select(DB::raw('SUM(transaction_amount) as totalAmount, count(id) as totalCount'))
                        ->where('transaction_type', 'cw')
                        ->where('status', $statusType)
                        ->where('user_id', $userId)
                        ->whereDate('created_at', '>=', $startDate)
                        ->whereDate('created_at', '<=', $endDate)
                        ->first();

                    // $data['status'] = true;
                    $response['service'] = $serviceType;
                    $response['status'] = $statusType;
                    $response['totalAmount'] = CommonHelper::numberFormat($settlement->totalAmount);
                    $response['totalAmountRaw'] = round($settlement->totalAmount);
                    $response['totalCount'] = CommonHelper::numberFormat($settlement->totalCount, 0);
                    $response['totalCountRaw'] = $settlement->totalCount;

                    break;


                case 'aeps_ap':
                    $settlement = DB::connection('slave')
                        ->table('aeps_transactions')
                        ->select(DB::raw('SUM(transaction_amount) as totalAmount, count(id) as totalCount'))
                        ->where('transaction_type', 'ap')
                        ->where('status', $statusType)
                        ->where('user_id', $userId)
                        ->whereDate('created_at', '>=', $startDate)
                        ->whereDate('created_at', '<=', $endDate)
                        ->first();

                    $response['service'] = $serviceType;
                    $response['status'] = $statusType;
                    $response['totalAmount'] = CommonHelper::numberFormat($settlement->totalAmount);
                    $response['totalAmountRaw'] = round($settlement->totalAmount);
                    $response['totalCount'] = CommonHelper::numberFormat($settlement->totalCount, 0);
                    $response['totalCountRaw'] = $settlement->totalCount;

                    break;

                case 'aeps_settle':
                    $settlement = DB::connection('slave')
                        ->table('aeps_transactions')
                        ->select(DB::raw('SUM(transaction_amount) as totalAmount, count(id) as totalCount'))
                        ->whereIn('transaction_type', ['ap', 'cw'])
                        ->where('status', 'success')
                        ->where('is_trn_credited', '0')
                        ->whereNull('trn_credited_at')
                        ->where('user_id', $userId)
                        ->whereDate('created_at', '>=', $startDate)
                        ->whereDate('created_at', '<=', $endDate)
                        ->first();

                    $response['service'] = $serviceType;
                    $response['status'] = $statusType;
                    $response['totalAmount'] = CommonHelper::numberFormat($settlement->totalAmount);
                    $response['totalAmountRaw'] = round($settlement->totalAmount);
                    $response['totalCount'] = CommonHelper::numberFormat($settlement->totalCount, 0);
                    $response['totalCountRaw'] = $settlement->totalCount;

                    break;



                case 'aeps_settled':
                    $settlement = DB::connection('slave')
                        ->table('aeps_transactions')
                        ->select(DB::raw('SUM(transaction_amount) as totalAmount, count(id) as totalCount'))
                        ->whereIn('transaction_type', ['ap', 'cw'])
                        ->where('status', 'success')
                        ->where('is_trn_credited', '1')
                        ->whereNotNull('trn_credited_at')
                        ->where('user_id', $userId)
                        ->whereDate('created_at', '>=', $startDate)
                        ->whereDate('created_at', '<=', $endDate)
                        ->first();

                    // $settledAmount = DB::connection('slave')
                    //     ->table('transactions')
                    //     ->select(DB::raw('sum(tr_amount) as SettAmount'))
                    //     ->where('user_id', $userId)
                    //     ->where('status', 'success')
                    //     ->whereDate('created_at', '>=', $startDate)
                    //     ->whereDate('created_at', '<=', $endDate)
                    //     ->whereIn('tr_identifiers', ['aeps_apinward_credit', 'aeps_inward_credit'])
                    //     ->first();

                    $response['service'] = $serviceType;
                    $response['status'] = $statusType;
                    $response['totalAmount'] = CommonHelper::numberFormat($settlement->totalAmount);
                    $response['totalAmountRaw'] = round($settlement->totalAmount);
                    $response['totalCount'] = CommonHelper::numberFormat($settlement->totalCount, 0);
                    $response['totalCountRaw'] = $settlement->totalCount;

                    break;


                case 'aeps_ap_charges':
                    $apCharges = DB::connection('slave')
                        ->table('aeps_transactions')
                        ->select(DB::raw('SUM(fee+tax) as apFeeTax, count(id) as totalCount'))
                        ->where('user_id', $userId)
                        ->where('transaction_type', 'ap')
                        ->where('status', 'success')
                        ->whereDate('created_at', '>=', $startDate)
                        ->whereDate('created_at', '<=', $endDate)
                        ->first();

                    $response['service'] = $serviceType;
                    $response['status'] = $statusType;
                    $response['totalAmount'] = CommonHelper::numberFormat($apCharges->apFeeTax);
                    $response['totalAmountRaw'] = round($apCharges->apFeeTax);
                    $response['totalCount'] = CommonHelper::numberFormat($apCharges->totalCount, 0);
                    $response['totalCountRaw'] = $apCharges->totalCount;


                case 'aeps_commission':
                    $settlement = DB::connection('slave')
                        ->table('aeps_transactions')
                        ->select(DB::raw('SUM(commission) as totalAmount, count(id) as totalCount'))
                        ->where('user_id', $userId)
                        ->where('status', 'success')
                        ->whereDate('created_at', '>=', $startDate)
                        ->whereDate('created_at', '<=', $endDate)
                        ->first();

                    $response['service'] = $serviceType;
                    $response['status'] = $statusType;
                    $response['totalAmount'] = CommonHelper::numberFormat($settlement->totalAmount);
                    $response['totalAmountRaw'] = round($settlement->totalAmount);
                    $response['totalCount'] = CommonHelper::numberFormat($settlement->totalCount, 0);
                    $response['totalCountRaw'] = $settlement->totalCount;

                    break;

                case 'doc_kyc':
                    $sqlResult = DB::connection('slave')
                        ->table('agents')
                        ->selectRaw("COUNT(id) as agentCounts")
                        ->where('documents_status', $statusType)
                        ->where('user_id', $userId)
                        // ->whereDate('created_at', '>=', $startDate)
                        // ->whereDate('created_at', '<=', $endDate)
                        ->first();

                    $response = [
                        'result' => $sqlResult,
                        'service' => $serviceType,
                        'status' => $statusType,
                    ];
                    break;

                case 'ekyc':

                    $sqlResult = DB::connection('slave')
                        ->table('agents')
                        ->selectRaw("COUNT(id) as agentCounts")
                        ->where('user_id', $userId);

                    if ($statusType === 'success') {
                        $sqlResult = $sqlResult->whereNotNull('ekyc')
                            ->where('ekyc', '!=', '0')
                            ->whereDate('created_at', '>=', $startDate)
                            ->whereDate('created_at', '<=', $endDate)
                            ->first();
                    } else {
                        $sqlResult = $sqlResult->whereDate('created_at', '>=', $startDate)
                            ->whereDate('created_at', '<=', $endDate)
                            ->where(function ($sql) {
                                return $sql->whereNull('ekyc')
                                    ->orWhere('ekyc', '=', '0');
                            })
                            ->first();
                    }

                    $response = [
                        'result' => $sqlResult,
                        'service' => $serviceType,
                        'status' => $statusType,
                    ];

                    break;

                default:
                    $response = [
                        'result' => null,
                        'service' => $serviceType,
                        'status' => $statusType,
                    ];
                    break;
            }

            return ResponseHelper::success('data fetched', $response);
        } catch (Exception $e) {
            return ResponseHelper::swwrong('Something went wrong.', $e->getMessage());
        }
    }


    /**
     * Recent Transactions
     */
    public function recentTransaction(Request $request)
    {
        try {
            $userId = $request->user()->id;
            // $type = $request->type;

            $records = DB::table('aeps_transactions')
                ->select(
                    'aeps_transactions.bankiin',
                    'aeps_transactions.commission',
                    'aeps_transactions.client_ref_id',
                    'aeps_transactions.route_type',
                    'aeps_transactions.transaction_type',
                    'aeps_transactions.resp_bank_message',
                    'aeps_transactions.rrn',
                    'aeps_transactions.transaction_amount',
                    'aeps_transactions.resp_stan_no',
                    'aeps_transactions.transaction_date',
                    'aeps_transactions.merchant_code',
                    'aeps_transactions.mobile_no',
                    'aeps_transactions.trn_ref_id',
                    'aeps_transactions.status',
                    // 'aeps_transactions.created_at',
                    DB::raw("DATE_FORMAT(aeps_transactions.created_at, '%Y-%m-%d %H:%i:%S') as created_at"),
                    'aeps_transactions.commission_ref_id',
                    DB::raw('concat("XXXX-XXXX-", right(aeps_transactions.aadhaar_no, 4)) as aadhaar_no'),
                    'banks.bank'
                )
                ->leftJoin('banks', 'banks.iin', '=', 'aeps_transactions.bankiin')
                ->where('aeps_transactions.user_id', $userId)
                ->orderBy('aeps_transactions.id', 'desc')
                ->limit(10)
                ->get();


            if ($records->isNotEmpty()) {
                return ResponseHelper::success('Record fetched successfully', $records);
            }

            return ResponseHelper::failed('No record found', []);
        } catch (Exception $e) {
            return ResponseHelper::swwrong('Something went wrong.', $e->getMessage());
        }
    }


    private function validateDate($date, $format = 'Y-m-d')
    {
        $d = DateTime::createFromFormat($format, $date);
        return $d && $d->format($format) === $date;
    }
}
