<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\CommonHelper;
use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class DashboardController extends Controller
{
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
                $settlement = DB::connection('slave')
                    ->table('orders')
                    ->select(DB::raw('sum(amount) as totalAmount, count(id) as totalCount'))
                    ->where('status', $type)
                    ->whereDate('created_at', '>=', $fromDate)
                    ->whereDate('created_at', '<=', $toDate)
                    ->first();
                $data['status'] = true;
                $data['type'] = 'payout_' . $type;
                $data['totalAmount'] = CommonHelper::numberFormat($settlement->totalAmount);
                $data['totalCount'] = $settlement->totalCount;
                break;

            case 'aeps':
                $aeps = DB::connection('slave')
                    ->table('aeps_transactions')
                    ->select(DB::raw('sum(transaction_amount) as totalAmount, count(id) as totalCount'))
                    ->where('status', $type)
                    ->whereIn('transaction_type', ['cw', 'ap'])
                    ->whereDate('created_at', '>=', $fromDate)
                    ->whereDate('created_at', '<=', $toDate)
                    ->first();
                $data['status'] = true;
                $data['type'] = 'aeps_' . $type;
                $data['totalAmount'] = CommonHelper::numberFormat($aeps->totalAmount);
                $data['totalCount'] = $aeps->totalCount;
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
