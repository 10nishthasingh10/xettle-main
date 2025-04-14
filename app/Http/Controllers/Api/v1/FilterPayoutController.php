<?php

namespace App\Http\Controllers\Api\v1;

use App\Helpers\CommonHelper;
use App\Helpers\NumberFormat;
use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class FilterPayoutController extends Controller
{

    /**
     * Filter data at User Payout Dashboard page
     */
    public function inwardOutward(Request $request)
    {

        $validator = Validator::make(
            $request->all(),
            [
                'startDate' => 'required|date|before_or_equal:endDate',
                'endDate' => 'required|date',
            ]
        );

        if ($validator->fails()) {
            $message = json_decode(json_encode($validator->errors()), true);
            return ResponseHelper::missing($message);
        }


        $startDate = trim($request->startDate);
        $endDate = trim($request->endDate);
        $userId = Auth::user()->id;
        $lables = [];

        $commonHelper = new CommonHelper();

        if ($startDate === $endDate) {
            $dateRange = $commonHelper->dateRange($startDate . " 00:00:00", $endDate . " 23:00:00", 'PT1H');

            foreach ($dateRange as $val) {
                $lables[] = [
                    'x' => $val->format('h A'),
                    'z' => ($val->format('YmdH'))
                ];
            }

            //08 AM
            $fullDateFormat = '%h %p';
            $stampDateFromat = '%Y%m%d%H';
        } else {
            $dateRange = $commonHelper->dateRange($startDate, $endDate);

            foreach ($dateRange as $val) {
                $lables[] = [
                    'x' => $val->format('M d'),
                    'z' => ($val->format('Ymd'))
                ];
            }

            //Jun 18
            $fullDateFormat = '%b %d';
            $stampDateFromat = '%Y%m%d';
        }



        //fetch user payout account number
        $userServiceAccountNumber = DB::connection('slave')
            ->table('user_services')
            ->select('*')
            ->join('global_services', 'global_services.service_id', 'user_services.service_id')
            ->where('global_services.service_slug', 'payout')
            ->where('user_services.user_id', $userId)
            ->first()->service_account_number;



        //calculate dr amount in a date range
        $drAmt = DB::connection('slave')
            ->table('orders')->select(
                DB::raw('SUM(amount) as dr_amt'),
                DB::raw('COUNT(id) as dr_req'),
            )
            ->where('status', 'processed')
            ->whereDate('created_at', '>=', $startDate)
            ->whereDate('created_at', '<=', $endDate)
            ->where('user_id', $userId)
            ->first();


        //calculate cr amount in a date range
        $crAmt = DB::connection('slave')
            ->table('transactions')
            ->select(
                DB::raw('sum(tr_amount) as cr_amt'),
                DB::raw('count(id) as cr_req'),
            )
            ->where('tr_type', 'cr')
            ->whereDate('tr_date', '>=', $startDate)
            ->whereDate('tr_date', '<=', $endDate)
            ->where('user_id', $userId)
            ->where('account_number', $userServiceAccountNumber)
            ->where('tr_identifiers', 'internal_transfer')
            ->first();


        $numberFormat = NumberFormat::init();
        $totIn = $numberFormat->change($crAmt->cr_amt);
        $totOut = $numberFormat->change($drAmt->dr_amt);

        $returnData = [
            'start_date' => $startDate,
            'end_date' => $endDate,
            'tot_in' => $totIn,
            'tot_in_acc' => $crAmt->cr_amt,
            'req_in' => $numberFormat->change($crAmt->cr_req),
            'req_in_acc' => ($crAmt->cr_req),
            'tot_out' => $totOut,
            'tot_out_acc' => $drAmt->dr_amt,
            'req_out' => $numberFormat->change($drAmt->dr_req),
            'req_out_acc' => ($drAmt->dr_req)
        ];


        //credit transactions
        $crRec = DB::connection('slave')
            ->table('transactions')
            ->select(
                DB::raw('sum(tr_amount) as totAmt'),
                DB::raw("DATE_FORMAT(created_at,'$stampDateFromat') as stamp"),
                DB::raw("DATE_FORMAT(created_at,'$fullDateFormat') as mDate"),
            )
            ->where('tr_type', 'cr')
            ->whereDate('tr_date', '>=', $startDate)
            ->whereDate('tr_date', '<=', $endDate)
            ->where('user_id', $userId)
            ->where('account_number', $userServiceAccountNumber)
            ->where('tr_identifiers', 'internal_transfer')
            ->groupBy('mDate')
            ->orderBy('tr_date', 'ASC')
            ->get();


        $drRec = DB::connection('slave')
            ->table('orders')->select(
                DB::raw('sum(amount) as totAmt'),
                DB::raw("DATE_FORMAT(created_at,'$stampDateFromat') as stamp"),
                DB::raw("DATE_FORMAT(created_at,'$fullDateFormat') as mDate"),
            )
            ->where('status', 'processed')
            ->whereDate('created_at', '>=', $startDate)
            ->whereDate('created_at', '<=', $endDate)
            ->where('user_id', $userId)
            ->groupBy('mDate')
            ->orderBy('created_at', 'ASC')
            ->get();

        $returnData['lables'] = $lables;
        $returnData['inward'] = $crRec;
        $returnData['payout'] = $drRec;


        return ResponseHelper::success("Data found.", $returnData);
    }




    /**
     * Filter data for Order History Chart at Payout dashboard
     */
    // public function orderHistoryChart(Request $request)
    // {
    //     $validator = Validator::make(
    //         $request->all(),
    //         [
    //             'startDate' => 'required|date|before_or_equal:endDate',
    //             'endDate' => 'required|date',
    //         ]
    //     );


    //     if ($validator->fails()) {
    //         $message = json_decode(json_encode($validator->errors()), true);
    //         return ResponseHelper::missing($message);
    //     }


    //     $startDate = trim($request->startDate);
    //     $endDate = trim($request->endDate);
    //     $userId = Auth::user()->id;


    //     $totalAmount = DB::table('orders')->select(
    //         DB::raw('SUM(amount) as totAmt'),
    //         DB::raw('COUNT(id) as totCount'),
    //         'status'
    //     )
    //         ->whereIn('status', ['processed', 'failed', 'reversed'])
    //         ->whereDate('created_at', '>=', $startDate)
    //         ->whereDate('created_at', '<=', $endDate)
    //         ->where('user_id', $userId)
    //         ->groupBy('status')
    //         ->get();

    //     return ResponseHelper::success("Data found.", $totalAmount);
    // }
}
