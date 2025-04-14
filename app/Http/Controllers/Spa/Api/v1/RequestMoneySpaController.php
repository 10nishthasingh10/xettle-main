<?php

namespace App\Http\Controllers\Spa\Api\v1;

use App\Helpers\CommonHelper;
use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class RequestMoneySpaController extends Controller
{
    /**
     * request for load money
     */
    public function requestMoney()
    {
        try {

            $validator = Validator::make(
                request()->all(),
                [
                    'amount' => "required|numeric|min:1",
                    'utr' => "required|alpha_num|unique:load_money_request,utr",
                ],
                [
                    'utr.unique' => "The UTR has already been requested."
                ]
            );

            if ($validator->fails()) {
                return ResponseHelper::missing($validator->errors());
            }

            $userId = Auth::user()->id;
            $amount = trim(request()->get('amount'));
            $utr = trim(request()->get('utr'));

            //check is load money request is active
            if (!CommonHelper::isLoadMoneyRequestActive($userId)) {
                return  ResponseHelper::failed('Request Money is not active.');
            }

            $checkUtr = DB::table('load_money_request')
                ->select('amount', 'is_trn_credited', 'trn_credited_at')
                ->where('utr', $utr)
                ->first();

            if (!empty($checkUtr)) {
                $message = ['utr' => ["UTR {$utr} is already credited at {$checkUtr->trn_credited_at}."]];
                return ResponseHelper::missing('Some params are missing.', $message);
            }

            $requestId = CommonHelper::getRandomString('FND', false);

            $insert = DB::table('load_money_request')->insert([
                'request_id' => $requestId,
                'user_id' => $userId,
                'amount' => $amount,
                'utr' => $utr,
                'status' => 'pending',
                'is_trn_credited' => '0',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ]);

            if ($insert) {
                return ResponseHelper::success('Request has been raised successfully.');
            }

            return ResponseHelper::failed('Request is not raised.');
        } catch (Exception $e) {
            return ResponseHelper::swwrong('Error: Something went wrong.', ['err' => $e->getMessage()]);
        }
    }


    public function allTransactions()
    {
        try {
            $userId = request()->user()->id;

            // $draw = request()->get('draw');
            $per_page = request()->get("per_page"); // total number of rows per page

            $columnName_arr = request()->get('columns');
            $order_arr = request()->get('order');
            $search_arr = request()->get('search');

            $startDate = trim(request()->get('startDate'));
            $endDate = trim(request()->get('endDate'));
            $txnType = trim(request()->get('type'));
            $txnStatus = strtolower(trim(request()->get('txnStatus')));

            //$columnIndex = $columnIndex_arr[0]['column']; // Column index
            $columnName = !empty($columnName_arr) ? $columnName_arr : 'id'; // Column name
            $columnSortOrder = !empty($order_arr) ? $order_arr : 'desc'; // asc or desc
            $searchValue = $search_arr; // Search value

            $records = DB::table('load_money_request')
                ->select(
                    'id',
                    'request_id',
                    'txn_id',
                    'amount',
                    'utr',
                    'status',
                    'remarks',
                    DB::raw("DATE_FORMAT(trn_credited_at, '%Y-%m-%d %H:%i:%S') as credited_at"),
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
                        ->orWhere('utr', 'like', '%' . $searchValue . '%')
                        ->orWhere('txn_id', 'like', '%' . $searchValue . '%');
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
                'approved',
                'rejected',
                'pending'
            ];


            return ResponseHelper::success('Record fetched successfully.', $responseData);
        } catch (Exception $e) {
            return ResponseHelper::swwrong('Something went wrong.', $e->getMessage());
        }
    }
}
