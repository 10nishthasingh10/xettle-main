<?php

namespace App\Http\Controllers\Spa\Api\v1;

use App\Helpers\CommonHelper;
use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class DmtController extends Controller
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
            $startDate = trim($request->startDate);
            $endDate = trim($request->endDate);


            $amountSuccess = DB::connection('slave')
                ->table('dmt_fund_transfers')
                ->select(
                    DB::raw('count(id) as txnCont'),
                    DB::raw('sum(amount) as totAmt'),
                    DB::raw('sum(fee+tax) as feeAndTax')
                )
                ->where('user_id', $userId)
                ->where('status', 'processed')
                ->whereDate('created_at', '>=', $startDate)
                ->whereDate('created_at', '<=', $endDate)
                ->first();

            $amountProcessing = DB::connection('slave')
                ->table('dmt_fund_transfers')
                ->select(
                    DB::raw('count(id) as txnCont'),
                    DB::raw('sum(amount) as totAmt'),
                    DB::raw('sum(fee+tax) as feeAndTax')
                )
                ->where('user_id', $userId)
                ->where('status', 'processing')
                ->whereDate('created_at', '>=', $startDate)
                ->whereDate('created_at', '<=', $endDate)
                ->first();

            $amountFailed = DB::connection('slave')
                ->table('dmt_fund_transfers')
                ->select(
                    DB::raw('count(id) as txnCont'),
                    DB::raw('sum(amount) as totAmt'),
                    DB::raw('sum(fee+tax) as feeAndTax')
                )
                ->where('user_id', $userId)
                ->where('status', 'failed')
                ->whereDate('created_at', '>=', $startDate)
                ->whereDate('created_at', '<=', $endDate)
                ->first();

            $merchant = DB::connection('slave')
                ->table('dmt_outlets')
                ->leftJoin('dmt_fund_transfers', 'dmt_fund_transfers.outlet_id', 'dmt_outlets.outlet_id')
                ->where('dmt_outlets.user_id', $userId)
                ->whereDate('dmt_fund_transfers.created_at', '>=', $startDate)
                ->whereDate('dmt_fund_transfers.created_at', '<=', $endDate)
                ->distinct('dmt_outlets.outlet_id')
                ->count();

            // $commission = DB::connection('slave')
            //     ->table('aeps_transactions')
            //     ->select(DB::raw('sum(commission) as totalAmount,count(id) as totalCount'))
            //     ->where(['status' =>  'success'])
            //     ->where('user_id',  $userId)
            //     ->where('commission', '!=', 0)
            //     ->whereIn('transaction_type', ['cw', 'ms'])
            //     ->whereDate('aeps_transactions.created_at', '>=', $startDate)
            //     ->whereDate('aeps_transactions.created_at', '<=', $endDate)
            //     ->first();



            $returnData['merchant'] = [
                'count' => CommonHelper::numberFormat($merchant, 0),
                'countRaw' => ($merchant)
            ];

            $returnData['amountSuccess'] = [
                'txnCont' => CommonHelper::numberFormat(@$amountSuccess->txnCont, 0),
                'txnContRaw' => $amountSuccess->txnCont,
                'totAmt' => CommonHelper::numberFormat(@$amountSuccess->totAmt, 2),
                'totAmtRaw' => round(@$amountSuccess->totAmt, 2),
                'feeAndTax' => CommonHelper::numberFormat(@$amountSuccess->feeAndTax, 2),
                'feeAndTaxRaw' => round(@$amountSuccess->feeAndTax, 2)
            ];

            $returnData['amountProcessing'] = [
                'txnCont' => CommonHelper::numberFormat(@$amountProcessing->txnCont, 0),
                'txnContRaw' => $amountProcessing->txnCont,
                'totAmt' => CommonHelper::numberFormat(@$amountProcessing->totAmt, 2),
                'totAmtRaw' => round(@$amountProcessing->totAmt, 2),
                'feeAndTax' => CommonHelper::numberFormat(@$amountProcessing->feeAndTax, 2),
                'feeAndTaxRaw' => round(@$amountProcessing->feeAndTax, 2)
            ];

            $returnData['amountFailed'] = [
                'txnCont' => CommonHelper::numberFormat(@$amountFailed->txnCont, 0),
                'txnContRaw' => $amountFailed->txnCont,
                'totAmt' => CommonHelper::numberFormat(@$amountFailed->totAmt, 2),
                'totAmtRaw' => round(@$amountFailed->totAmt, 2),
                'feeAndTax' => CommonHelper::numberFormat(@$amountFailed->feeAndTax, 2),
                'feeAndTaxRaw' => round(@$amountFailed->feeAndTax, 2)
            ];

            // $returnData['amountProcessing'] = $amountProcessing;
            // $returnData['amountFailed'] = $amountFailed;
            // $returnData['commissionAmount'] = CommonHelper::numberFormat(@$commission->totalAmount);
            // $returnData['commissionCount '] = CommonHelper::numberFormat(@$commission->totalCount);

            return ResponseHelper::success('success', $returnData);
        } catch (Exception $e) {
            return ResponseHelper::swwrong('Something went wrong.', $e->getMessage());
        }
    }


    /**
     * Merchant List
     */
    public function merchantList(Request $request)
    {
        try {
            $userId = Auth::user()->id;

            $records = DB::table('dmt_outlets')
                ->select('id', 'merchant_code', 'outlet_id', 'name', 'email', 'mobile')
                ->where('user_id', $userId)
                ->where('is_active', '1')
                ->get();

            return ResponseHelper::success('Record fetched successfully.', $records);
        } catch (Exception $e) {
            return ResponseHelper::swwrong('Something went wrong.', $e->getMessage());
        }
    }


    /**
     * Merchants List
     */
    public function dmtMerchants(Request $request)
    {
        try {
            $userId = Auth::user()->id; //$request->user()->id;

            $draw = $request->get('draw');
            $per_page = $request->get("per_page"); // total number of rows per page
            $per_page = !empty($per_page) ? $per_page : 10;

            $columnName_arr = $request->get('columns');
            $order_arr = $request->get('order');
            $search_arr = $request->get('search');

            //$columnIndex = $columnIndex_arr[0]['column']; // Column index
            $columnName = !empty($columnName_arr) ? $columnName_arr : 'id'; // Column name
            $columnSortOrder = !empty($order_arr) ? $order_arr : 'desc'; // asc or desc
            $searchValue = $search_arr; // Search value


            $startDate = trim($request->get('startDate'));
            $endDate = trim($request->get('endDate'));


            $records = DB::table('dmt_outlets')
                ->select(
                    'merchant_code',
                    'name',
                    'outlet_id',
                    'gender',
                    'email',
                    'mobile',
                    'address',
                    'dob',
                    'pan',
                    'district_name',
                    'address',
                    DB::raw('concat("XXXX-XXXX-", right(aadhaar, 4)) as aadhaar'),
                    'pincode',
                    DB::raw("DATE_FORMAT(created_at, '%Y-%m-%d %H:%i:%S') as created_at"),
                )
                ->where('user_id', $userId);


            if (!empty($startDate) && !empty($endDate)) {
                $records->whereDate('created_at', '>=', $startDate)
                    ->whereDate('created_at', '<=', $endDate);
            }


            if (!empty($searchValue)) {
                $records = $records->where(function ($sql) use ($searchValue) {
                    return $sql->orWhere('merchant_code', 'like', '%' . $searchValue . '%')
                        ->orWhere('email', 'like', '%' . $searchValue . '%')
                        ->orWhere('outlet_id', 'like', '%' . $searchValue . '%')
                        ->orWhere('pan', 'like', '%' . $searchValue . '%')
                        ->orWhere('mobile', 'like', '%' . $searchValue . '%')
                        // ->orWhere('aadhaar', 'like', '%' . $searchValue . '%')
                        ->orWhere("name", "LIKE", "%$searchValue%");
                });
            }

            $records = $records->orderBy($columnName, $columnSortOrder)
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
     * Remitters List
     */
    public function dmtRemitters(Request $request)
    {
        try {
            $userId = Auth::user()->id; //$request->user()->id;

            $draw = $request->get('draw');
            $per_page = $request->get("per_page"); // total number of rows per page
            $per_page = !empty($per_page) ? $per_page : 10;

            $columnName_arr = $request->get('columns');
            $order_arr = $request->get('order');
            $search_arr = trim($request->get('search'));

            //$columnIndex = $columnIndex_arr[0]['column']; // Column index
            $columnName = !empty($columnName_arr) ? $columnName_arr : 'id'; // Column name
            $columnSortOrder = !empty($order_arr) ? $order_arr : 'desc'; // asc or desc
            $searchValue = $search_arr; // Search value


            $startDate = trim($request->get('startDate'));
            $endDate = trim($request->get('endDate'));


            $records = DB::table('dmt_remitters')
                ->select(
                    'dmt_remitters.first_name',
                    'dmt_remitters.last_name',
                    'dmt_remitters.outlet_id',
                    'dmt_remitters.mobile',
                    'dmt_remitters.is_active',
                    'dmt_remitters.pin',
                    DB::raw("DATE_FORMAT(dmt_remitters.created_at, '%Y-%m-%d %H:%i:%S') as created_at"),
                    'dmt_outlets.name',
                    'dmt_outlets.email',
                    'dmt_outlets.mobile',
                )
                ->leftJoin('dmt_outlets', 'dmt_outlets.outlet_id', '=', 'dmt_remitters.outlet_id')
                ->where('dmt_remitters.user_id', $userId)
                ->where('dmt_outlets.user_id', $userId);


            if (!empty($startDate) && !empty($endDate)) {
                $records->whereDate('dmt_remitters.created_at', '>=', $startDate)
                    ->whereDate('dmt_remitters.created_at', '<=', $endDate);
            }


            if (!empty($searchValue)) {
                $records = $records->where(function ($sql) use ($searchValue) {
                    return $sql->orWhere('merchant_code', 'like', '%' . $searchValue . '%')
                        ->orWhere('dmt_remitters.outlet_id', 'like', '%' . $searchValue . '%')
                        ->orWhere('dmt_remitters.mobile', 'like', '%' . $searchValue . '%')
                        ->orWhere('dmt_outlets.email', 'like', '%' . $searchValue . '%')
                        ->orWhere('dmt_outlets.name', 'like', '%' . $searchValue . '%')
                        ->orWhere('dmt_outlets.mobile', 'like', '%' . $searchValue . '%')
                        ->orWhereRaw("CONCAT(dmt_remitters.first_name, ' ', dmt_remitters.last_name) LIKE '%$searchValue%'");
                });
            }

            $records = $records->orderBy('dmt_remitters.' . $columnName, $columnSortOrder)
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
     * Transactions
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

            //$columnIndex = $columnIndex_arr[0]['column']; // Column index
            // $columnName = !empty($columnName_arr) ? $columnName_arr : 'aeps_transactions.id'; // Column name
            $columnSortOrder = !empty($order_arr) ? $order_arr : 'desc'; // asc or desc
            // $columnName = $columnName_arr; // Column name
            // $columnSortOrder = $order_arr; // asc or desc
            $searchValue = $search_arr; // Search value

            $records = DB::table('dmt_fund_transfers')
                ->select(
                    'dmt_fund_transfers.merchant_code',
                    'dmt_fund_transfers.order_ref_id',
                    'dmt_fund_transfers.outlet_id',
                    'dmt_fund_transfers.client_ref_id',
                    'dmt_fund_transfers.mobile',
                    'dmt_fund_transfers.beni_id',
                    'dmt_fund_transfers.beni_name',
                    // 'dmt_fund_transfers.bank_account',
                    DB::raw('concat("XXXXXX", right(dmt_fund_transfers.bank_account, 5)) as bank_account'),
                    'dmt_fund_transfers.bank_ifsc',
                    'dmt_fund_transfers.mode',
                    'dmt_fund_transfers.utr',
                    'dmt_fund_transfers.amount',
                    'dmt_fund_transfers.fee',
                    'dmt_fund_transfers.tax',
                    'dmt_fund_transfers.status',
                    DB::raw("DATE_FORMAT(dmt_fund_transfers.created_at, '%Y-%m-%d %H:%i:%S') as created_at"),
                    'dmt_outlets.name',
                    'dmt_outlets.email'
                    // 'dmt_outlets.mobile',
                )
                ->leftJoin('dmt_outlets', 'dmt_outlets.outlet_id', '=', 'dmt_fund_transfers.outlet_id')
                ->where('dmt_fund_transfers.user_id', $userId)
                ->where('dmt_outlets.user_id', $userId);


            if (!empty($txnStatus)) {
                $records->where('dmt_fund_transfers.status', '=', $txnStatus);
            }

            if (!empty($startDate) && !empty($endDate)) {
                $records->whereDate('dmt_fund_transfers.created_at', '>=', $startDate)
                    ->whereDate('dmt_fund_transfers.created_at', '<=', $endDate);
            }


            if (!empty($searchValue)) {
                $records->where(function ($sql) use ($searchValue) {
                    return $sql->orWhere('dmt_fund_transfers.client_ref_id', 'like', '%' . $searchValue . '%')
                        ->orWhere('dmt_fund_transfers.order_ref_id', 'like', '%' . $searchValue . '%')
                        ->orWhere('dmt_fund_transfers.utr', 'like', '%' . $searchValue . '%')
                        ->orWhere('dmt_fund_transfers.status', 'like', '%' . $searchValue . '%')
                        ->orWhere('dmt_fund_transfers.outlet_id', 'like', '%' . $searchValue . '%')
                        ->orWhere('dmt_fund_transfers.beni_id', 'like', '%' . $searchValue . '%')
                        ->orWhere('dmt_fund_transfers.mobile', 'like', '%' . $searchValue . '%')
                        ->orWhere('dmt_outlets.name', 'like', '%' . $searchValue . '%')
                        // ->orWhere('dmt_outlets.mobile', 'like', '%' . $searchValue . '%')
                        ->orWhere('dmt_outlets.email', 'like', '%' . $searchValue . '%');
                });
            }

            $records = $records->orderBy('dmt_fund_transfers.id', $columnSortOrder)
                ->paginate($per_page);

            if ($records->isNotEmpty()) {
                $responseData['records'] = $records;
            } else {
                $responseData['records'] = null;
            }

            $responseData['startDate'] = $startDate;
            $responseData['endDate'] = $endDate;
            $responseData['txnStatus'] = [
                // 'queued',
                'processing',
                'processed',
                'failed',
                'reversed'
            ];

            return ResponseHelper::success('Record fetched successfully.', $responseData);
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

            $records = DB::table('dmt_fund_transfers')
                ->select(
                    'dmt_fund_transfers.merchant_code',
                    'dmt_fund_transfers.order_ref_id',
                    'dmt_fund_transfers.outlet_id',
                    'dmt_fund_transfers.client_ref_id',
                    'dmt_fund_transfers.mobile',
                    'dmt_fund_transfers.beni_id',
                    DB::raw('concat("XXXXXX", right(dmt_fund_transfers.bank_account, 5)) as bank_account'),
                    'dmt_fund_transfers.mode',
                    'dmt_fund_transfers.utr',
                    'dmt_fund_transfers.amount',
                    'dmt_fund_transfers.fee',
                    'dmt_fund_transfers.tax',
                    'dmt_fund_transfers.status',
                    DB::raw("DATE_FORMAT(dmt_fund_transfers.created_at, '%Y-%m-%d %H:%i:%S') as created_at"),
                    'dmt_outlets.name',
                    'dmt_outlets.email',
                    'dmt_outlets.mobile',
                )
                ->leftJoin('dmt_outlets', 'dmt_outlets.outlet_id', '=', 'dmt_fund_transfers.outlet_id')
                ->where('dmt_fund_transfers.user_id', $userId)
                ->where('dmt_outlets.user_id', $userId)
                ->orderBy('dmt_fund_transfers.id', 'desc')
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
}
