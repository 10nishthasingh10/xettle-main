<?php

namespace App\Http\Controllers\Spa\Api\v1;

use App\Helpers\CommonHelper;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Helpers\ResponseHelper;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class OCRController extends Controller
{

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

            $userId = $request->user()->id;

            $service = DB::table('user_services')->where('user_id', $userId)->where('service_id', VALIDATE_SERVICE_ID)->first();

            if (!empty($service)) {

                $startDate = trim($request->startDate);
                $endDate = trim($request->endDate);

                $data = DB::table('ocrs')
                    ->select(
                        DB::raw('sum(tax+fee) as totalAmount'),
                        DB::raw('count(*) as totalCount'),
                        'type',
                        'status'
                    )->where('user_id', $userId)
                    ->where('status', 'success')
                    ->whereDate('created_at', '>=', $startDate)
                    ->whereDate('created_at', '<=', $endDate)
                    ->groupBy('type', 'status')
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
            }

            return ResponseHelper::failed('Service is not active.');
        } catch (Exception $e) {
            return ResponseHelper::swwrong('Something went wrong.', $e->getMessage());
        }
    }


    public function transactions(Request $request)
    {
        try {
            $userId = $request->user()->id;

            // $draw = $request->get('draw');
            $per_page = $request->get("per_page"); // total number of rows per page

            $columnName_arr = $request->get('columns');
            $order_arr = $request->get('order');
            $search_arr = $request->get('search');

            $startDate = trim($request->get('startDate'));
            $endDate = trim($request->get('endDate'));
            $txnType = trim($request->get('type'));
            $txnStatus = strtolower(trim($request->get('txnStatus')));

            //$columnIndex = $columnIndex_arr[0]['column']; // Column index
            $columnName = !empty($columnName_arr) ? $columnName_arr : 'id'; // Column name
            $columnSortOrder = !empty($order_arr) ? $order_arr : 'desc'; // asc or desc
            $searchValue = $search_arr; // Search value

            $records = DB::table('ocrs')
                ->select(
                    'client_ref_id',
                    'order_ref_id',
                    'fee',
                    'tax',
                    'request_id',
                    // 'document1',
                    // 'document2',
                    'type',
                    'status',
                    'failed_message',
                    DB::raw("DATE_FORMAT(created_at, '%Y-%m-%d %H:%i:%S') as created_at")
                )
                ->where('user_id', $userId);

            if (!empty($txnType)) {
                $records->where('type', $txnType);
            }

            if (!empty($txnStatus)) {
                $records->where('status', $txnStatus);
            }

            if (!empty($startDate) && !empty($endDate)) {
                $records->whereDate('created_at', '>=', $startDate)
                    ->whereDate('created_at', '<=', $endDate);
            }


            if (!empty($searchValue)) {
                $records->where(function ($sql) use ($searchValue) {
                    return $sql->orWhere('request_id', 'like', '%' . $searchValue . '%')
                        ->orWhere('order_ref_id', 'like', '%' . $searchValue . '%')
                        ->orWhere('client_ref_id', 'like', '%' . $searchValue . '%');
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
            $responseData['txnStatus'] = [
                // 'queued',
                'pending',
                'failed',
                'success',
                // 'reject'
            ];
            $responseData['type'] = [
                'pan',
                'aadhaar'
                // 'driving'
            ];


            return ResponseHelper::success('Record fetched successfully.', $responseData);
        } catch (Exception $e) {
            return ResponseHelper::swwrong('Something went wrong.', $e->getMessage());
        }
    }


    public function recentTransaction(Request $request)
    {
        try {
            $userId = $request->user()->id;

            // Get records, also we have included search filter as well
            $records = DB::table('ocrs')
                ->select(
                    'client_ref_id',
                    'order_ref_id',
                    'fee',
                    'tax',
                    'request_id',
                    // 'document1',
                    // 'document2',
                    'type',
                    'status',
                    'failed_message',
                    DB::raw("DATE_FORMAT(created_at, '%Y-%m-%d %H:%i:%S') as created_at")
                )
                ->where('user_id', $userId)
                ->orderBy('id', 'desc')
                ->limit(10)
                ->get();

            if ($records->isNotEmpty()) {
                return ResponseHelper::success('Record fetched successfully', $records);
            }

            return ResponseHelper::failed('No record found');
        } catch (Exception $e) {
            return ResponseHelper::swwrong('Something went wrong.', $e->getMessage());
        }
    }
}
