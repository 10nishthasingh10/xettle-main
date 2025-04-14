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


class MicroAtmSpaController extends Controller
{

    /**
     * All ransaction
     * Data table
     */
    public function transactions(Request $request)
    {
        try {

            $userId = Auth::user()->id; //$request->user()->id;

            // $draw = $request->get('draw');
            $per_page = $request->get("per_page"); // total number of rows per page
            $per_page = !empty($per_page) ? $per_page : 10;

            // $columnName_arr = $request->get('columns');
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

            $records = DB::table('matm_transactions')
                ->select(
                    'matm_transactions.commission',
                    'matm_transactions.tds',
                    'matm_transactions.order_ref_id',
                    'matm_transactions.trn_ref_id',
                    'matm_transactions.bank_ref_no',
                    'matm_transactions.mid',
                    'matm_transactions.tid',
                    'matm_transactions.invoice_no',
                    'matm_transactions.client_ref_id',
                    'matm_transactions.transaction_type',
                    'matm_transactions.bank_response_code',
                    'matm_transactions.failed_message',
                    'matm_transactions.microatm_bank_response',
                    'matm_transactions.bank_name',
                    'matm_transactions.card_type',
                    'matm_transactions.rrnno',
                    'matm_transactions.transaction_amount',
                    // 'matm_transactions.resp_stan_no',
                    'matm_transactions.merchant_code',
                    'matm_transactions.status',
                    DB::raw("DATE_FORMAT(matm_transactions.created_at, '%Y-%m-%d %H:%i:%S') as created_at"),
                    'matm_transactions.commission_ref_id',
                    'matm_transactions.cardno'
                    // DB::raw('concat("XXXX-XXXX-", right(matm_transactions.cardno, 4)) as cardno'),
                )
                // ->leftJoin('banks', 'banks.iin', '=', 'matm_transactions.bankiin')
                ->where('matm_transactions.user_id', $userId);


            if (!empty($txnStatus)) {
                $records->where('matm_transactions.status', '=', $txnStatus);
            }

            if (!empty($txnType)) {
                $records->where('matm_transactions.transaction_type', '=', $txnType);
            }

            if (!empty($startDate) && !empty($endDate)) {
                $records->whereDate('matm_transactions.created_at', '>=', $startDate)
                    ->whereDate('matm_transactions.created_at', '<=', $endDate);
            }


            if (!empty($searchValue)) {
                $records = $records->where(function ($sql) use ($searchValue) {
                    return $sql->orWhere('matm_transactions.client_ref_id', 'like', '%' . $searchValue . '%')
                        ->orWhere('matm_transactions.rrnno', 'like', '%' . $searchValue . '%')
                        ->orWhere('matm_transactions.merchant_code', 'like', '%' . $searchValue . '%')
                        ->orWhere('matm_transactions.order_ref_id', 'like', '%' . $searchValue . '%')
                        ->orWhere('matm_transactions.bank_ref_no', 'like', '%' . $searchValue . '%');
                });
            }

            $records = $records->orderBy('matm_transactions.id', $columnSortOrder)
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
                'processed',
                'failed',
                'disputed',
                'reveresd'
            ];

            $responseData['txnType'] = [
                'be',
                'cw'
            ];

            return ResponseHelper::success('Record fetched successfully.', $responseData);
        } catch (Exception $e) {
            return ResponseHelper::swwrong('Something went wrong.', $e->getMessage());
        }
    }



    public function settlements(Request $request)
    {
        try {

            $userId = Auth::user()->id; //$request->user()->id;

            // $draw = $request->get('draw');
            $per_page = $request->get("per_page"); // total number of rows per page
            $per_page = !empty($per_page) ? $per_page : 10;

            // $columnName_arr = $request->get('columns');
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
                ->where('transactions.tr_identifiers', 'matm_inward_credit');


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
                    'service' => "required|in:matm_cw,matm_settle,matm_settled,matm_commission",
                    'status' => "nullable|in:pending,processed,failed,disputed,reveresd",
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

                case 'matm_cw':
                    $settlement = DB::connection('slave')
                        ->table('matm_transactions')
                        ->select(DB::raw('SUM(transaction_amount) as totalAmount, count(id) as totalCount'))
                        ->where('transaction_type', 'cw')
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


                case 'matm_settle':
                    $settlement = DB::connection('slave')
                        ->table('matm_transactions')
                        ->select(DB::raw('SUM(transaction_amount) as totalAmount, count(id) as totalCount'))
                        ->where('transaction_type', 'cw')
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



                case 'matm_settled':
                    $settlement = DB::connection('slave')
                        ->table('matm_transactions')
                        ->select(DB::raw('SUM(transaction_amount) as totalAmount, count(id) as totalCount'))
                        ->where('transaction_type', 'cw')
                        ->where('status', 'success')
                        ->where('is_trn_credited', '1')
                        ->whereNotNull('trn_credited_at')
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


                    // case 'aeps_ap_charges':
                    //     $apCharges = DB::connection('slave')
                    //         ->table('aeps_transactions')
                    //         ->select(DB::raw('SUM(fee+tax) as apFeeTax, count(id) as totalCount'))
                    //         ->where('user_id', $userId)
                    //         ->where('transaction_type', 'ap')
                    //         ->where('status', 'success')
                    //         ->whereDate('created_at', '>=', $startDate)
                    //         ->whereDate('created_at', '<=', $endDate)
                    //         ->first();

                    //     $response['service'] = $serviceType;
                    //     $response['status'] = $statusType;
                    //     $response['totalAmount'] = CommonHelper::numberFormat($apCharges->apFeeTax);
                    //     $response['totalAmountRaw'] = round($apCharges->apFeeTax);
                    //     $response['totalCount'] = CommonHelper::numberFormat($apCharges->totalCount, 0);
                    //     $response['totalCountRaw'] = $apCharges->totalCount;


                case 'matm_commission':
                    $settlement = DB::connection('slave')
                        ->table('matm_transactions')
                        ->select(DB::raw('SUM(commission) as totalAmount, count(id) as totalCount'))
                        ->where('user_id', $userId)
                        ->where('is_commission_credited','1')
                        ->whereDate('commission_credited_at', '>=', $startDate)
                        ->whereDate('commission_credited_at', '<=', $endDate)
                        ->first();

                    $response['service'] = $serviceType;
                    $response['status'] = $statusType;
                    $response['totalAmount'] = CommonHelper::numberFormat($settlement->totalAmount);
                    $response['totalAmountRaw'] = round($settlement->totalAmount);
                    $response['totalCount'] = CommonHelper::numberFormat($settlement->totalCount, 0);
                    $response['totalCountRaw'] = $settlement->totalCount;

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

            $records = DB::table('matm_transactions')
                ->select(
                    'matm_transactions.commission',
                    'matm_transactions.tds',
                    'matm_transactions.order_ref_id',
                    'matm_transactions.trn_ref_id',
                    'matm_transactions.bank_ref_no',
                    'matm_transactions.mid',
                    'matm_transactions.tid',
                    'matm_transactions.invoice_no',
                    'matm_transactions.client_ref_id',
                    'matm_transactions.transaction_type',
                    'matm_transactions.bank_response_code',
                    'matm_transactions.failed_message',
                    'matm_transactions.microatm_bank_response',
                    'matm_transactions.bank_name',
                    'matm_transactions.card_type',
                    'matm_transactions.rrnno',
                    'matm_transactions.transaction_amount',
                    // 'matm_transactions.resp_stan_no',
                    'matm_transactions.merchant_code',
                    'matm_transactions.status',
                    DB::raw("DATE_FORMAT(matm_transactions.created_at, '%Y-%m-%d %H:%i:%S') as created_at"),
                    // 'matm_transactions.commission_ref_id',
                    'matm_transactions.cardno'
                    // DB::raw('concat("XXXX-XXXX-", right(matm_transactions.cardno, 4)) as cardno'),
                )
                // ->leftJoin('banks', 'banks.iin', '=', 'aeps_transactions.bankiin')
                ->where('matm_transactions.user_id', $userId)
                ->orderBy('matm_transactions.id', 'desc')
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
