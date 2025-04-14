<?php

namespace App\Http\Controllers\Spa\Api\v1;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Models\UserSettlementForUser;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SpaAppTransactions extends Controller
{

    public function primaryWallet(Request $request)
    {
        try {
            $userId = Auth::user()->id;

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
            $txnType = strtolower(trim($request->get('txnType')));
            $serviceType = strtolower(trim($request->get('serviceType')));


            $records = DB::table('transactions')
                ->select(
                    'transactions.trans_id',
                    'transactions.txn_id',
                    'transactions.txn_ref_id',
                    'transactions.account_number',
                    'transactions.order_id',
                    'transactions.tr_total_amount',
                    'transactions.tr_amount',
                    'transactions.tr_fee',
                    'transactions.tr_tax',
                    'transactions.tr_date',
                    'transactions.tr_type',
                    'transactions.tr_identifiers',
                    'transactions.service_id',
                    'transactions.closing_balance',
                    'transactions.tr_narration',
                    'transactions.tr_reference',
                    'transactions.remarks',
                    DB::raw("DATE_FORMAT(transactions.created_at, '%Y-%m-%d %H:%i:%S') as created_at"),
                    'global_services.service_name'
                )
                ->leftJoin('global_services', 'global_services.service_id', '=', 'transactions.service_id')
                ->where('transactions.user_id', $userId);


            if (!empty($txnType)) {
                $records->where('transactions.tr_type', '=', $txnType);
            }

            if (!empty($serviceType)) {

                if ($serviceType === 'internal_transfer') {
                    $records->where('transactions.tr_identifiers', '=', 'internal_transfer');
                } else {
                    $records->where('transactions.service_id', '=', $serviceType);
                }
            }

            if (!empty($startDate) && !empty($endDate)) {
                $records->whereDate('transactions.created_at', '>=', $startDate)
                    ->whereDate('transactions.created_at', '<=', $endDate);
            }


            if (!empty($searchValue)) {
                $records = $records->where(function ($sql) use ($searchValue) {
                    return $sql->orWhere('transactions.account_number', 'like', '%' . $searchValue . '%')
                        ->orWhere('transactions.txn_id', 'like', '%' . $searchValue . '%')
                        ->orWhere('transactions.txn_ref_id', 'like', '%' . $searchValue . '%')
                        ->orWhere('transactions.service_id', 'like', '%' . $searchValue . '%')
                        ->orWhere('transactions.tr_amount', 'like', '%' . $searchValue . '%')
                        ->orWhere('transactions.order_id', 'like', '%' . $searchValue . '%')
                        ->orWhere('global_services.service_name', 'like', '%' . $searchValue . '%');
                });
            }

            $records = $records->orderBy('transactions.' . $columnName, $columnSortOrder)
                ->paginate($per_page);


            if ($records->isNotEmpty()) {
                $responseData['records'] = $records;
            } else {
                $responseData['records'] = null;
            }

            $responseData['startDate'] = $startDate;
            $responseData['endDate'] = $endDate;
            $responseData['txnType'] = [
                'dr',
                'cr'
            ];

            //serviceType
            $serviceType = DB::table('global_services')
                ->leftJoin('user_services', 'global_services.service_id', '=', 'user_services.service_id')
                ->select('global_services.service_id', 'global_services.service_name')
                ->where('global_services.is_active', '1')
                ->where('global_services.service_type', '1')
                ->where('global_services.is_activation_allowed', '1')
                ->where('user_services.user_id', $userId)
                ->orderBy('global_services.service_name', 'asc')
                ->get();

            $responseData['serviceType'] = $serviceType;

            return ResponseHelper::success('Record fetched successfully.', $responseData);
        } catch (Exception $e) {
            return ResponseHelper::swwrong('Something went wrong.', $e->getMessage());
        }
    }

    /**
     * Transactions List
     */
    public function autoSettlements(Request $request)
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

            $bankReferenceId = trim($request->get('bankReferenceId'));
            $startDate = trim($request->get('startDate'));
            $endDate = trim($request->get('endDate'));
            $txnStatus = strtolower(trim($request->get('txnStatus')));

            $records = UserSettlementForUser::with(['getSettlementLog'])
                ->select(
                    'id',
                    'settlement_ref_id',
                    'mode',
                    'amount',
                    'fee',
                    'tax',
                    'account_number',
                    'account_ifsc',
                    'beneficiary_name',
                    'status',
                    DB::raw("DATE_FORMAT(created_at, '%Y-%m-%d %H:%i:%S') as created_at")
                )
                ->where('user_id', $userId);

            if (!empty($startDate) && !empty($endDate)) {
                $records->whereDate('created_at', '>=', $startDate)
                    ->whereDate('created_at', '<=', $endDate);
            }


            if (!empty($txnStatus)) {
                $records->where('status', '=', $txnStatus);
            }


            if (!empty($bankReferenceId)) {

                $records->whereHas('getSettlementLog', function (Builder $query) use ($bankReferenceId) {
                    $query->where('bank_reference', 'like', "%{$bankReferenceId}%");
                });
            } else if (!empty($searchValue)) {

                $records->where(function ($sql) use ($searchValue) {
                    $sql->orWhere('settlement_ref_id', 'like', '%' . $searchValue . '%')
                        ->orWhere('amount', 'like', '%' . $searchValue . '%')
                        ->orWhere('status', 'like', '%' . $searchValue . '%')
                        ->orWhere('account_number', 'like', '%' . $searchValue . '%')
                        ->orWhere('account_ifsc', 'like', '%' . $searchValue . '%');
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
                'hold',
                // 'initiate',
                'processing',
                'processed',
                'failed'
            ];

            return ResponseHelper::success('Record fetched successfully.', $responseData);
        } catch (Exception $e) {
            return ResponseHelper::swwrong('Something went wrong.', $e->getMessage());
        }
    }
}
