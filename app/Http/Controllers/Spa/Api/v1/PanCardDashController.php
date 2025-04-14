<?php

namespace App\Http\Controllers\Spa\Api\v1;

use App\Helpers\CommonHelper;
use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PanCardDashController extends Controller
{
    //
    public function agentDetails(Request $request)
    {
        try {
            $userId = Auth::user()->id;
            // $userId = $request->user_id()->id;


            $startDate = trim($request->get('startDate'));
            $endDate = trim($request->get('endDate'));
            $searchValue = $request->get('search');
            $per_page = $request->get("per_page"); // total number of rows per page
            $per_page = !empty($per_page) ? $per_page : '10';


            $details = DB::table('pan_agents')
                ->select('psa_id as psaCode', 'first_name', 'middle_name', 'last_name', 'mobile', 'email', 'pin', 'dob', 'states.state_name', 'districts.district_title', 'address', 'pan', DB::raw('concat("XXXX-XXXX-", right(pan_agents.aadhaar, 4)) as aadhaar_no'), DB::raw("DATE_FORMAT(pan_agents.created_at, '%Y-%m-%d %H:%i:%S') as created_at"))
                // ->where('status','=','1')
                ->where('user_id', '=', $userId)
                ->leftJoin('districts', 'pan_agents.district', '=', 'districts.id')
                ->leftJoin('states', 'pan_agents.state', '=', 'states.id');
            // ->paginate($per_page);




            if (!empty($startDate) && !empty($endDate)) {
                $details->whereDate('pan_agents.created_at', '>=', $startDate)
                    ->whereDate('pan_agents.created_at', '<=', $endDate);
            } //else {
            //     $details->whereDate('pan_agents.created_at', '>=', date('Y-m-d 00:00:00'));
            // }

            

            if (!empty($searchValue)) {
                $details = $details->where(function ($sql) use ($searchValue) {
                    return $sql->orWhere('psa_id', 'like', '%' . $searchValue . '%')
                        ->orWhere('email', 'like', '%' . $searchValue . '%')
                        ->orWhere('pan', 'like', '%' . $searchValue . '%')
                        ->orWhere('mobile', 'like', '%' . $searchValue . '%')
                        ->orWhereRaw("CONCAT(`first_name`, ' ', `last_name`) LIKE '%$searchValue%'");
                });
            }


            $details = $details
                ->paginate($per_page);


            if (!empty($details)) {
                $responseData['records'] = $details;
            } else {
                $responseData['records'] = null;
            }
            $responseData['startDate'] = $startDate;
            $responseData['endDate'] = $endDate;
            return ResponseHelper::success('Record fetched successfully.', $responseData);
        } catch (Exception $e) {
            return ResponseHelper::swwrong('Something went wrong.', $e->getMessage());
        }
    }

    public function agenttxn(Request $request)
    {
        try {
            $userId = Auth::user()->id;
            $startDate = trim($request->get('startDate'));
            $endDate = trim($request->get('endDate'));
            $txnStatus = strtolower(trim($request->get('txnStatus')));
            $searchValue = $request->get('search');
            $per_page = $request->get("per_page"); // total number of rows per page
            $per_page = !empty($per_page) ? $per_page : '10';

            $order_arr = $request->get('order');
            $columnSortOrder = !empty($order_arr) ? $order_arr : 'desc';

            $details = DB::table('pan_txns')
                ->select('psa_code', 'txn_id', 'order_ref_id', 'app_no', 'ope_txn_id', 'coupon_type', 'email', 'mobile', 'fee as amount', 'status','failed_message', DB::raw("DATE_FORMAT(pan_txns.created_at, '%Y-%m-%d %H:%i:%S') as created_at"))
                ->where('user_id', '=', $userId);

            if (!empty($startDate) && !empty($endDate)) {
                $details->whereDate('pan_txns.created_at', '>=', $startDate)
                    ->whereDate('pan_txns.created_at', '<=', $endDate);
            } //else {
            //     $details->whereDate('pan_agents.created_at', '>=', date('Y-m-d 00:00:00'));
            // }

            if (!empty($txnStatus)) {
                $details->where('pan_txns.status', '=', $txnStatus);
            }

            if (!empty($searchValue)) {
                $details = $details->where(function ($sql) use ($searchValue) {
                    return $sql->orWhere('psa_code', 'like', '%' . $searchValue . '%')
                        ->orWhere('txn_id', 'like', '%' . $searchValue . '%')
                        ->orWhere('order_ref_id', 'like', '%' . $searchValue . '%')
                        // ->orWhere('app_no', 'like', '%' . $searchValue . '%')
                        ->orWhere('email', 'like', '%' . $searchValue . '%')
                        ->orWhere('mobile', 'like', '%' . $searchValue . '%')
                        ->orWhere('status', 'like', '%' . $searchValue . '%');
                });
            }




            $details = $details->orderBy('pan_txns.id', $columnSortOrder)
                ->paginate($per_page);



            if ($details->isNotEmpty()) {
                $responseData['records'] = $details;
            } else {
                $responseData['records'] = null;
            }

            $responseData['startDate'] = $startDate;
            $responseData['endDate'] = $endDate;
            $responseData['txnStatus'] = [
                'success',
                'pending',
                'failed'
            ];


            return ResponseHelper::success('Record fetched successfully.', $responseData);
        } catch (Exception $e) {
            return ResponseHelper::swwrong('Something went wrong.', $e->getMessage());
        }
    }

    public function panrecentTransaction(Request $request)
    {

        try {
            $userId = $request->user()->id;

            $records = DB::table('pan_txns')
                ->select('psa_code', 'txn_id', 'order_ref_id', 'app_no as uti_app_no', 'ope_txn_id as operator_txn_id', 'coupon_type', 'mobile', 'fee as amount', 'status', DB::raw("DATE_FORMAT(pan_txns.created_at, '%Y-%m-%d %H:%i:%S') as created_at"))
                ->where('user_id', '=', $userId)
                ->orderBy('pan_txns.id', 'desc')
                ->limit(10)
                ->get();


            if (!empty($records)) {
                return ResponseHelper::success('Record fetched successfully', $records);
            } else {
                return ResponseHelper::failed('No record found', []);
            }
        } catch (Exception $e) {
            return ResponseHelper::swwrong('Error: ' . $e->getMessage());
        }
    }

    public function dashboardCardDetail(Request $request)
    {
        try {
            $validator = \Validator::make(
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
                ->table('pan_txns')
                ->select(
                    DB::raw('count(id) as txnCont'),
                    DB::raw('sum(fee) as totAmt'),
                    DB::raw('sum(fee+tax) as feeAndTax')
                )
                ->where('user_id', $userId)
                ->where('status', 'success')
                ->whereDate('created_at', '>=', $startDate)
                ->whereDate('created_at', '<=', $endDate)
                ->first();

            $amountProcessing = DB::connection('slave')
                ->table('pan_txns')
                ->select(
                    DB::raw('count(id) as txnCont'),
                    DB::raw('sum(fee) as totAmt'),
                    DB::raw('sum(fee+tax) as feeAndTax')
                )
                ->where('user_id', $userId)
                ->where('status', 'pending')
                ->whereDate('created_at', '>=', $startDate)
                ->whereDate('created_at', '<=', $endDate)
                ->first();

            $amountFailed = DB::connection('slave')
                ->table('pan_txns')
                ->select(
                    DB::raw('count(id) as txnCont'),
                    DB::raw('sum(fee) as totAmt'),
                    DB::raw('sum(fee+tax) as feeAndTax')
                )
                ->where('user_id', $userId)
                ->where('status', 'failed')
                ->whereDate('created_at', '>=', $startDate)
                ->whereDate('created_at', '<=', $endDate)
                ->first();

            $merchant = DB::connection('slave')
                ->table('pan_txns')
                ->select(
                    DB::raw('count(psa_code)'))
                ->groupBy('psa_code')
                ->where('pan_txns.user_id', $userId)
                ->whereDate('pan_txns.created_at', '>=', $startDate)
                ->whereDate('pan_txns.created_at', '<=', $endDate)
                ->get();

            $returnData['merchant'] = [
                'count' => CommonHelper::numberFormat(count($merchant), 0),
                'countRaw' => count($merchant)
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


            return ResponseHelper::success('success', $returnData);
        } catch (Exception $e) {
            return ResponseHelper::swwrong('Something went wrong.', $e->getMessage());
        }
    }
}