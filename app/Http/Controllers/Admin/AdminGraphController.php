<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\CommonHelper;
use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class AdminGraphController extends Controller
{

    /**
     * Upi Stack Graph
     */
    public function graphUpiStack(Request $request, $type)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'startDate' => 'required|date|before_or_equal:endDate',
                'endDate' => 'required|date',
                'userId' => 'nullable|numeric|min:0'
            ]
        );

        if ($validator->fails()) {
            $message = json_decode(json_encode($validator->errors()), true);
            return ResponseHelper::missing($message);
        }

        $graphType = strtolower(trim($type));


        $userId = trim($request->userId);
        $startDate = trim($request->startDate);
        $endDate = trim($request->endDate);

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


        switch ($graphType) {
            case 'transaction':

                /**
                 * =================
                 * Transactions
                 * =================
                 */

                //success vai fpay
                $inwardFpay = DB::connection('slave')
                    ->table('upi_callbacks')
                    ->select(
                        DB::raw('sum(amount) as totAmt'),
                        DB::raw("DATE_FORMAT(created_at,'$stampDateFromat') as stamp"),
                        DB::raw("DATE_FORMAT(created_at,'$fullDateFormat') as mDate"),
                        'root_type'
                    )->whereIn('root_type', ['fpay', 'ibl']);

                if (!empty($userId)) {
                    $inwardFpay->where('user_id', '=', $userId);
                }

                $inwardFpay = $inwardFpay->where('is_trn_disputed', '0')
                    ->whereDate('created_at', '>=', $startDate)
                    ->whereDate('created_at', '<=', $endDate)
                    ->groupBy('mDate', 'stamp', 'root_type')
                    ->orderBy('stamp', 'asc')
                    ->get();


                $returnData['lables'] = $lables;
                $returnData['startDate'] = $startDate;
                $returnData['endDate'] = $endDate;
                $returnData['inwardData'] = $inwardFpay;

                return ResponseHelper::success('success', $returnData);
                break;

            case 'disputed':


                /**
                 * =================
                 * Disputed Transactions
                 * =================
                 */

                //disputed vai fpay
                $disputedFpay = DB::connection('slave')
                    ->table('upi_callbacks')
                    ->select(
                        DB::raw('sum(amount) as totAmt'),
                        DB::raw("DATE_FORMAT(created_at,'$stampDateFromat') as stamp"),
                        DB::raw("DATE_FORMAT(created_at,'$fullDateFormat') as mDate"),
                        'root_type'
                    )->whereIn('root_type', ['fpay', 'ibl']);

                if (!empty($userId)) {
                    $disputedFpay->where('user_id', '=', $userId);
                }

                $disputedFpay = $disputedFpay->where('is_trn_disputed', '1')
                    ->whereDate('created_at', '>=', $startDate)
                    ->whereDate('created_at', '<=', $endDate)
                    ->groupBy('mDate', 'stamp', 'root_type')
                    ->orderBy('stamp', 'asc')
                    ->get();


                $returnData['lables'] = $lables;
                $returnData['startDate'] = $startDate;
                $returnData['endDate'] = $endDate;
                $returnData['disputedData'] = $disputedFpay;

                return ResponseHelper::success('success', $returnData);
                break;
        }

        return ResponseHelper::failed('failed');
    }



    /**
     * AEPS Graphs
     */
    public function graphAeps(Request $request, $type)
    {

        $validator = Validator::make(
            $request->all(),
            [
                'startDate' => 'required|date|before_or_equal:endDate',
                'endDate' => 'required|date',
                'userId' => 'nullable|numeric|min:0'
            ]
        );

        if ($validator->fails()) {
            $message = json_decode(json_encode($validator->errors()), true);
            return ResponseHelper::missing($message);
        }

        $graphType = strtolower(trim($type));

        $userId = trim($request->userId);
        $startDate = trim($request->startDate);
        $endDate = trim($request->endDate);


        switch ($graphType) {
            case 'transaction':
                /**
                 * ====================================
                 * transaction cash withdraw success
                 * ====================================
                 */

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



                $cwAepsSuccess = DB::connection('slave')
                    ->table('aeps_transactions')
                    ->select(
                        DB::raw('sum(transaction_amount) as totAmt'),
                        DB::raw("DATE_FORMAT(created_at,'$stampDateFromat') as stamp"),
                        DB::raw("DATE_FORMAT(created_at,'$fullDateFormat') as mDate"),
                        'status'
                    )->where('transaction_type', 'cw');

                if (!empty($userId)) {
                    $cwAepsSuccess->where('user_id', '=', $userId);
                }

                $cwAepsSuccess = $cwAepsSuccess->whereIn('status', ['success', 'failed'])
                    ->whereDate('created_at', '>=', $startDate)
                    ->whereDate('created_at', '<=', $endDate)
                    ->groupBy('mDate', 'stamp', 'status')
                    ->orderBy('stamp', 'asc')
                    ->get();

                $returnData['lables'] = $lables;
                $returnData['startDate'] = $startDate;
                $returnData['endDate'] = $endDate;
                $returnData['cwAepsData'] = $cwAepsSuccess;

                return ResponseHelper::success('success', $returnData);
                break;

            case 'txn-counts':


                /**
                 * =========================
                 * Transaction Counts
                 * =========================
                 */


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

                //transaction CW
                $cwAepsCount = DB::connection('slave')
                    ->table('aeps_transactions')
                    ->select(
                        DB::raw('count(id) as totCount'),
                        DB::raw("DATE_FORMAT(created_at,'$stampDateFromat') as stamp"),
                        DB::raw("DATE_FORMAT(created_at,'$fullDateFormat') as mDate"),
                        'transaction_type'
                    )->whereIn('transaction_type', ['cw', 'ms', 'be']);

                if (!empty($userId)) {
                    $cwAepsCount->where('user_id', $userId);
                }

                $cwAepsCount = $cwAepsCount->where('status', 'success')
                    ->whereDate('created_at', '>=', $startDate)
                    ->whereDate('created_at', '<=', $endDate)
                    ->groupBy('mDate', 'stamp', 'transaction_type')
                    ->orderBy('stamp', 'asc')
                    ->get();


                $returnData['lables'] = $lables;
                $returnData['startDate'] = $startDate;
                $returnData['endDate'] = $endDate;
                $returnData['aepsCountData'] = $cwAepsCount;

                return ResponseHelper::success('success', $returnData);

                break;


            case 'merchant':

                /**
                 * ============================
                 * merchant on board
                 * ============================
                 */


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



                $merchantOnBoard = DB::connection('slave')
                    ->table('agents')
                    ->select(
                        DB::raw('count(id) as totCount'),
                        DB::raw("DATE_FORMAT(created_at,'$stampDateFromat') as stamp"),
                        DB::raw("DATE_FORMAT(created_at,'$fullDateFormat') as mDate")
                    );

                if (!empty($userId)) {
                    $merchantOnBoard->where('user_id', '=', $userId);
                }

                $merchantOnBoard = $merchantOnBoard->where('is_active', '1')
                    ->whereDate('created_at', '>=', $startDate)
                    ->whereDate('created_at', '<=', $endDate)
                    ->groupBy('mDate', 'stamp')
                    ->orderBy('stamp', 'asc')
                    ->get();


                $returnData['lables'] = $lables;
                $returnData['startDate'] = $startDate;
                $returnData['endDate'] = $endDate;
                $returnData['merchantOnBoardData'] = $merchantOnBoard;

                return ResponseHelper::success('success', $returnData);
                break;


            case 'bank-volume':

                /**
                 * ================================
                 * CW by bank
                 * ================================
                 */
                $cwVolumeBankSuccess = DB::connection('slave')
                    ->table('aeps_transactions')
                    ->select(
                        'banks.bank',
                        DB::raw('sum(aeps_transactions.transaction_amount) as totAmt'),
                        'aeps_transactions.status'
                    )
                    ->join('banks', 'banks.iin', 'aeps_transactions.bankiin');

                if (!empty($userId)) {
                    $cwVolumeBankSuccess->where('aeps_transactions.user_id', $userId);
                }

                $cwVolumeBankSuccess = $cwVolumeBankSuccess->where('aeps_transactions.transaction_type', 'cw')
                    ->whereIn('aeps_transactions.status', ['success', 'failed'])
                    ->whereDate('aeps_transactions.created_at', '>=', $startDate)
                    ->whereDate('aeps_transactions.created_at', '<=', $endDate)
                    ->groupBy('aeps_transactions.bankiin')
                    ->get();




                $returnData['startDate'] = $startDate;
                $returnData['endDate'] = $endDate;
                $returnData['volumeBankData'] = $cwVolumeBankSuccess;

                return ResponseHelper::success('success', $returnData);
                break;


            case 'root-volume':
                /**
                 * ===========================
                 * CW by root
                 * ============================
                 */


                $cwVolumeRootSuccess = DB::connection('slave')
                    ->table('aeps_transactions')
                    ->select(
                        DB::raw('route_type as lable'),
                        DB::raw('sum(transaction_amount) as volume'),
                        'status'
                    );

                if (!empty($userId)) {
                    $cwVolumeRootSuccess->where('user_id', $userId);
                }

                $cwVolumeRootSuccess = $cwVolumeRootSuccess->where('transaction_type', 'cw')
                    ->whereIn('status', ['success', 'failed'])
                    ->whereDate('created_at', '>=', $startDate)
                    ->whereDate('created_at', '<=', $endDate)
                    ->groupBy('route_type')
                    ->get();


                $returnData['startDate'] = $startDate;
                $returnData['endDate'] = $endDate;
                $returnData['volumeRootData'] = $cwVolumeRootSuccess;

                return ResponseHelper::success('success', $returnData);
                break;
        }

        return ResponseHelper::failed('failed');
    }



    /**
     * Payout Graph
     */
    public function graphPayout(Request $request, $type)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'startDate' => 'required|date|before_or_equal:endDate',
                'endDate' => 'required|date',
                'userId' => 'nullable|numeric|min:0'
            ]
        );

        if ($validator->fails()) {
            $message = json_decode(json_encode($validator->errors()), true);
            return ResponseHelper::missing($message);
        }

        $graphType = strtolower(trim($type));

        $userId = trim($request->userId);
        $startDate = trim($request->startDate);
        $endDate = trim($request->endDate);

        $commonHelper = new CommonHelper();


        switch ($graphType) {
            case 'transaction':

                /**
                 * =================
                 * Transactions
                 * =================
                 */


                if ($startDate === $endDate) {
                    $dateRange = $commonHelper->dateRange($startDate . " 00:00:00", $endDate . " 23:00:00", 'PT1H');

                    foreach ($dateRange as $val) {
                        $lables[] = [
                            'x' => $val->format('h A'),
                            'z' => ($val->format('YmdH'))
                        ];
                    }

                    $time['foreach_1'] = microtime(true);

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


                //payout success
                $payoutSuccess = DB::connection('slave')
                    ->table('orders')->select(
                        DB::raw('sum(amount) as totAmt'),
                        DB::raw("DATE_FORMAT(`created_at`, '$stampDateFromat') as stamp"),
                        DB::raw("DATE_FORMAT(created_at,'$fullDateFormat') as mDate"),
                        'status',
                    )
                    ->whereDate('created_at', '>=', $startDate)
                    ->whereDate('created_at', '<=', $endDate);

                if (!empty($userId)) {
                    $payoutSuccess->where('user_id', $userId);
                }

                $payoutSuccess = $payoutSuccess->whereIn('status', ['processed', 'failed', 'reversed', 'processing'])
                    ->groupBy('mDate', 'stamp', 'status')
                    ->orderBy('stamp', 'ASC')
                    ->get();



                $returnData['lables'] = $lables;
                $returnData['startDate'] = $startDate;
                $returnData['endDate'] = $endDate;
                $returnData['payoutData'] = $payoutSuccess;

                return ResponseHelper::success('success', $returnData);

                break;

            case 'mode':

                /**
                 * ====================
                 * Mode
                 * ====================
                 */

                //payout mode success
                $payoutModeSuccess = DB::connection('slave')
                    ->table('orders')->select(
                        'mode',
                        DB::raw('sum(amount) as totAmt'),
                        'status'
                    )
                    ->whereDate('created_at', '>=', $startDate)
                    ->whereDate('created_at', '<=', $endDate);

                if (!empty($userId)) {
                    $payoutModeSuccess->where('user_id', $userId);
                }

                $payoutModeSuccess = $payoutModeSuccess->whereIn('status', ['processed', 'failed'])
                    ->groupBy('mode', 'status')
                    ->get();


                $returnData['startDate'] = $startDate;
                $returnData['endDate'] = $endDate;
                $returnData['payoutModeData'] = $payoutModeSuccess;

                return ResponseHelper::success('success', $returnData);
                break;


            case 'area':

                /**
                 * =========================
                 * AREA
                 * =========================
                 */

                //payout area success
                $payoutAreaSuccess = DB::connection('slave')
                    ->table('orders')->select(
                        'area',
                        DB::raw('sum(amount) as totAmt'),
                        'status'
                    )
                    ->where('area', '<>', '22')
                    ->whereDate('created_at', '>=', $startDate)
                    ->whereDate('created_at', '<=', $endDate);

                if (!empty($userId)) {
                    $payoutAreaSuccess->where('user_id', $userId);
                }

                $payoutAreaSuccess = $payoutAreaSuccess->whereIn('status', ['processed', 'failed'])
                    ->groupBy('area', 'status')
                    ->get();

                $returnData['startDate'] = $startDate;
                $returnData['endDate'] = $endDate;
                $returnData['payoutAreaData'] = $payoutAreaSuccess;

                return ResponseHelper::success('success', $returnData);
                break;
        }

        return ResponseHelper::failed('failed');
    }
}
