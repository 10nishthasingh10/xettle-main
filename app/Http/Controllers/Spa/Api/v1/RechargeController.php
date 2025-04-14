<?php

namespace App\Http\Controllers\Spa\Api\v1;

use App\Helpers\CommonHelper;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Helpers\ResponseHelper;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class RechargeController extends Controller
{

    /**
     * Dashboard
     */
    public function index(Request $request)
    {
        try {

            // $userId = $request->user()->id;
            $userId = Auth::user()->id;
            // $service = DB::table('user_services')->where('user_id', $userId)->where('service_id', RECHARGE_SERVICE_ID)->first();

            // if (!empty($service)) {
            $date = date('Y-m-d');
            $startDate = trim($request->get('startDate'));
            $endDate = trim($request->get('endDate'));


            if (empty($startDate)) {
                $startDate = $date;
            }

            if (empty($endDate)) {
                $endDate = $date;
            }


            $data = DB::table('recharges')
                ->select(
                    DB::raw('sum(recharges.amount) as totalAmount'),
                    DB::raw('count(recharges.id) as totalCount'),
                    'recharges.status',
                    'mst_operators.type',
                )
                ->leftJoin('mst_operators', 'recharges.operator_id', '=', 'mst_operators.id')
                ->whereDate('recharges.created_at', '>=', $startDate)
                ->whereDate('recharges.created_at', '<=', $endDate)
                ->where('recharges.user_id', $userId)
                ->groupBy('mst_operators.type', 'recharges.status')
                ->get();


            if ($data->isNotEmpty()) {

                $response = [];

                foreach ($data as $row) {

                    $response[] = [
                        'totalAmount' => CommonHelper::numberFormat($row->totalAmount, 2),
                        'totalAmountRaw' => round($row->totalAmount, 2),
                        'totalCount' => CommonHelper::numberFormat($row->totalCount, 0),
                        'totalCountRaw' => $row->totalCount,
                        'status' => $row->status,
                        'type' => $row->type,
                    ];
                }

                return ResponseHelper::success('Record fetched successfully', $response);
            }
            return ResponseHelper::failed('Record not found.');
        } catch (Exception $e) {
            return ResponseHelper::swwrong('Error: ' . $e->getMessage());
        }
    }



    /**
     * Recent Transactions 
     */
    public function recentTransaction(Request $request)
    {

        try {
            $userId = $request->user()->id;

            // Get records, also we have included search filter as well
            $records = DB::table('recharges')
                ->select(
                    'recharges.*',
                    'mst_operators.name',
                    'mst_operators.type'
                )
                ->leftJoin('mst_operators', 'recharges.operator_id', '=', 'mst_operators.id')
                ->where('recharges.user_id', $userId)
                ->orderBy('recharges.id', 'desc')
                ->limit(10)
                ->get();

            $responseData = [];

            foreach ($records as $record) {

                $responseData[] = array(
                    'id' => $record->id,
                    'operator_id' => $record->operator_id,
                    'operator_name' => $record->name,
                    "order_ref_id" => $record->order_ref_id,
                    "merchant_code" => $record->merchant_code,
                    'phone' => $record->phone,
                    'amount' => $record->amount,
                    'commission' => $record->commission,
                    'tax' => $record->tax,
                    "status" => $record->status,
                    "created_at" => $record->created_at
                );
            }

            if (count($responseData) > 0) {
                return ResponseHelper::success('Record fetched successfully', $responseData);
            } else {
                return ResponseHelper::failed('No record found', []);
            }
        } catch (Exception $e) {
            return ResponseHelper::swwrong('Error: ' . $e->getMessage());
        }
    }


    /**
     * All Transactions
     */
    public function transactions(Request $request)
    {
        try {

            $userId = $request->user()->id;

            // $draw = $request->get('draw');
            $per_page = trim($request->get("per_page")); // total number of rows per page
            $per_page = !empty($per_page) ? $per_page : 10;

            // $columnName_arr = $request->get('columns');
            // $order_arr = $request->get('order');
            $search_arr = $request->get('search');

            //$columnIndex = $columnIndex_arr[0]['column']; // Column index
            // $columnName = $columnName_arr; // Column name
            // $columnSortOrder = $order_arr; // asc or desc
            $searchValue = $search_arr; // Search value


            $startDate = trim($request->get('startDate'));
            $endDate = trim($request->get('endDate'));
            $txnStatus = strtolower(trim($request->get('txnStatus')));
            $oprType = strtolower(trim($request->get('oprType')));


            $records = DB::table('recharges')
                ->select(
                    'recharges.*',
                    'mst_operators.name as operator_name',
                    'mst_operators.type as operator_type'
                )
                ->leftJoin('mst_operators', 'recharges.operator_id', '=', 'mst_operators.id')
                ->where('recharges.user_id', $userId);

            if (!empty($txnStatus)) {
                $records->where('recharges.status', '=', $txnStatus);
            }

            if (!empty($oprType)) {
                $records->where('mst_operators.type', '=', $oprType);
            }

            if (!empty($startDate) && !empty($endDate)) {
                $records->whereDate('recharges.created_at', '>=', $startDate)
                    ->whereDate('recharges.created_at', '<=', $endDate);
            }

            if (!empty($searchValue)) {
                $records = $records->where(function ($sql) use ($searchValue) {
                    return $sql->where('recharges.order_ref_id', 'like', '%' . $searchValue . '%')
                        ->orWhere('recharges.merchant_code', 'like', '%' . $searchValue . '%')
                        ->orWhere('recharges.stan_no', 'like', '%' . $searchValue . '%')
                        ->orWhere('recharges.phone', 'like', '%' . $searchValue . '%');
                });
            }

            $records = $records->orderBy('recharges.id', 'desc')
                ->paginate($per_page);


            if ($records->isNotEmpty()) {
                $responseData['records'] = $records;
            } else {
                $responseData['records'] = null;
            }

            $responseData['startDate'] = $startDate;
            $responseData['endDate'] = $endDate;

            $responseData['oprType'] = [
                'mobile',
                'dth'
            ];

            $responseData['txnStatus'] = [
                // 'hold',
                // 'queued',
                'processing',
                'processed',
                // 'cancelled',
                // 'reversed',
                'failed'
            ];

            return ResponseHelper::success('Record fetched successfully.', $responseData);
        } catch (Exception $e) {
            return ResponseHelper::swwrong('Error: ' . $e->getMessage());
        }
    }
}
