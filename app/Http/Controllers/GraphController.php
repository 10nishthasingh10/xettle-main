<?php

namespace App\Http\Controllers;

use App\Helpers\CommonHelper;
use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class GraphController extends Controller
{

    /**
     * Upi Stack Graph
     */
    public function graphUpiStack(Request $request, $type)
    {
        try {

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


            if (Auth::user()->is_admin == '0') {
                $userId = Auth::user()->id;
            } else {
                $userId = trim($request->userId);
            }

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

                    $inwardFpay = $inwardFpay->where('is_trn_credited', '1')
                        ->where('is_trn_disputed', '0')
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

                case 'transaction-user':

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
                        )->whereIn('root_type', ['fpay', 'ibl']);

                    if (!empty($userId)) {
                        $inwardFpay->where('user_id', '=', $userId);
                    }

                    $inwardFpay = $inwardFpay->where('is_trn_credited', '1')
                        ->where('is_trn_disputed', '0')
                        ->whereDate('created_at', '>=', $startDate)
                        ->whereDate('created_at', '<=', $endDate)
                        ->groupBy('mDate', 'stamp')
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
        } catch (Exception $e) {
            return ResponseHelper::swwrong('Something Went Wrong', ['err' => $e->getMessage()]);
        }
    }



    /**
     * AEPS Graphs
     */
    public function graphAeps(Request $request, $type)
    {
        try {


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

            if (Auth::user()->is_admin == '0') {
                $userId = Auth::user()->id;
            } else {
                $userId = trim($request->userId);
            }

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
                            'aeps_transactions.bankiin',
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
                        ->groupBy('aeps_transactions.bankiin', 'aeps_transactions.status')
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
                        ->groupBy('route_type', 'status')
                        ->get();


                    $returnData['startDate'] = $startDate;
                    $returnData['endDate'] = $endDate;
                    $returnData['volumeRootData'] = $cwVolumeRootSuccess;

                    return ResponseHelper::success('success', $returnData);
                    break;


                case 'top-merchant':


                    $topMerchant = DB::connection('slave')
                        ->table('aeps_transactions')
                        ->select(
                            DB::raw('sum(aeps_transactions.transaction_amount) as totAmt'),
                            DB::raw('count(aeps_transactions.id) as totCount'),
                            DB::raw('aeps_transactions.merchant_code as merchant'),
                            DB::raw('agents.first_name'),
                            DB::raw('agents.middle_name'),
                            DB::raw('agents.last_name'),
                            DB::raw('agents.email_id as email'),
                            DB::raw('agents.mobile'),
                        )
                        ->leftJoin('agents', 'aeps_transactions.merchant_code', '=', 'agents.merchant_code')
                        ->where('aeps_transactions.transaction_type', 'cw');

                    if (!empty($userId)) {
                        $topMerchant->where('aeps_transactions.user_id', '=', $userId);
                    }

                    $topMerchant = $topMerchant->where('aeps_transactions.status', 'success')
                        ->whereDate('aeps_transactions.created_at', '>=', $startDate)
                        ->whereDate('aeps_transactions.created_at', '<=', $endDate)
                        ->groupBy('merchant')
                        ->orderBy('totAmt', 'desc')
                        ->limit(5)
                        ->get();


                    $returnData['startDate'] = $startDate;
                    $returnData['endDate'] = $endDate;
                    $returnData['topMerchant'] = $topMerchant;

                    return ResponseHelper::success('success', $returnData);
                    break;
            }

            return ResponseHelper::failed('failed');
        } catch (Exception $e) {
            return ResponseHelper::swwrong('Something Went Wrong', ['err' => $e->getMessage()]);
        }
    }



    /**
     * Payout Graph
     */
    public function graphPayout(Request $request, $type)
    {
        try {

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

            $startDate = trim($request->startDate);
            $endDate = trim($request->endDate);

            if (Auth::user()->is_admin == '0') {
                $userId = Auth::user()->id;
            } else {
                $userId = trim($request->userId);
            }

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
                            DB::raw('count(id) as totCount'),
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
        } catch (Exception $e) {
            return ResponseHelper::swwrong('Something Went Wrong', ['err' => $e->getMessage()]);
        }
    }



    /**
     * Primary Fund Flow
     */
    public function graphPrimaryFund(Request $request)
    {
        try {

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

            // $graphType = strtolower(trim($type));

            if (Auth::user()->is_admin == '0') {
                $userId = Auth::user()->id;
                $accountNumber = Auth::user()->account_number;
            } else {
                $userId = trim($request->userId);
            }

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


            //Total Inward
            $inTxn = DB::connection('slave')
                ->table('transactions')
                ->select(
                    DB::raw('sum(tr_amount) as totAmt'),
                    DB::raw('sum(tr_fee+tr_tax) as feeTax'),
                    DB::raw("DATE_FORMAT(created_at,'$stampDateFromat') as stamp"),
                    DB::raw("DATE_FORMAT(created_at,'$fullDateFormat') as mDate"),
                    DB::raw("tr_type as type"),
                )
                ->whereDate('created_at', '>=', $startDate)
                ->whereDate('created_at', '<=', $endDate);

            if (!empty($userId)) {
                $inTxn->where('user_id', '=', $userId)
                    ->where('account_number', $accountNumber);
            }

            $inTxn = $inTxn->whereIn('tr_type', ['cr', 'dr'])
                ->groupBy('stamp', 'mDate', 'tr_type')
                ->orderBy('created_at', 'ASC')
                ->get();


            $returnData['lables'] = $lables;
            $returnData['startDate'] = $startDate;
            $returnData['endDate'] = $endDate;
            $returnData['fundFlowData'] = $inTxn;

            return ResponseHelper::success('success', $returnData);
        } catch (Exception $e) {
            return ResponseHelper::swwrong('Something Went Wrong', ['err' => $e->getMessage()]);
        }
    }



    /**
     * Validation Suite
     */
    public function graphValidationSuite(Request $request)
    {
        try {

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

            // $graphType = strtolower(trim($type));

            if (Auth::user()->is_admin == '0') {
                $userId = Auth::user()->id;
            } else {
                $userId = trim($request->userId);
            }

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


            $requestCount = DB::connection('slave')
                ->table('validation_suite')
                ->select(
                    DB::raw('count(id) as totCount'),
                    DB::raw("DATE_FORMAT(created_at,'$stampDateFromat') as stamp"),
                    DB::raw("DATE_FORMAT(created_at,'$fullDateFormat') as mDate"),
                    DB::raw('validation_type as type')
                )
                ->whereDate('created_at', '>=', $startDate)
                ->whereDate('created_at', '<=', $endDate);

            if (!empty($userId)) {
                $requestCount->where('user_id', '=', $userId);
            }

            $requestCount = $requestCount->whereIn('validation_type', ['bank', 'vpa', 'ifsc'])
                ->groupBy('stamp', 'mDate', 'validation_type')
                ->orderBy('created_at', 'ASC')
                ->get();


            $returnData['lables'] = $lables;
            $returnData['startDate'] = $startDate;
            $returnData['endDate'] = $endDate;
            $returnData['requestCount'] = $requestCount;

            return ResponseHelper::success('success', $returnData);
        } catch (Exception $e) {
            return ResponseHelper::swwrong('Something Went Wrong', ['err' => $e->getMessage()]);
        }
    }



    /**
     * Virtual Account
     */
    public function graphVirtualAccount(Request $request)
    {
        try {

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

            // $graphType = strtolower(trim($type));

            if (Auth::user()->is_admin == '0') {
                $userId = Auth::user()->id;
            } else {
                $userId = trim($request->userId);
            }

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


            $requestCount = DB::connection('slave')
                ->table('upi_callbacks')
                ->select(
                    DB::raw('sum(amount) as totAmt'),
                    DB::raw("DATE_FORMAT(created_at,'$stampDateFromat') as stamp"),
                    DB::raw("DATE_FORMAT(created_at,'$fullDateFormat') as mDate"),
                )
                ->whereDate('created_at', '>=', $startDate)
                ->whereDate('created_at', '<=', $endDate)
                ->where('is_trn_credited', '1')
                ->where('is_trn_disputed', '0')
                ->where('root_type', 'ibl_tpv');

            if (!empty($userId)) {
                $requestCount->where('user_id', '=', $userId);
            }

            $requestCount = $requestCount->groupBy('stamp', 'mDate')
                ->orderBy('created_at', 'ASC')
                ->get();


            $returnData['lables'] = $lables;
            $returnData['startDate'] = $startDate;
            $returnData['endDate'] = $endDate;
            $returnData['requestCount'] = $requestCount;

            return ResponseHelper::success('success', $returnData);
        } catch (Exception $e) {
            return ResponseHelper::swwrong('Something Went Wrong', ['err' => $e->getMessage()]);
        }
    }


    /**
     * Smart Collect
     */
    public function graphSmartCollect(Request $request)
    {
        try {

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

            // $graphType = strtolower(trim($type));

            if (Auth::user()->is_admin == '0') {
                $userId = Auth::user()->id;
            } else {
                $userId = trim($request->userId);
            }

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


            $requestCount = DB::connection('slave')
                ->table('cf_merchants_fund_callbacks')
                ->select(
                    DB::raw('sum(amount) as totAmt'),
                    DB::raw("DATE_FORMAT(created_at,'$stampDateFromat') as stamp"),
                    DB::raw("DATE_FORMAT(created_at,'$fullDateFormat') as mDate"),
                    DB::raw('is_vpa as type'),
                )
                ->whereDate('created_at', '>=', $startDate)
                ->whereDate('created_at', '<=', $endDate)
                ->where('is_trn_credited', '1')
                ->where('is_trn_disputed', '0')
                ->whereIn('is_vpa', ['0', '1']);

            if (!empty($userId)) {
                $requestCount->where('user_id', '=', $userId);
            }

            $requestCount = $requestCount->groupBy('stamp', 'mDate', 'is_vpa')
                ->orderBy('created_at', 'ASC')
                ->get();


            $returnData['lables'] = $lables;
            $returnData['startDate'] = $startDate;
            $returnData['endDate'] = $endDate;
            $returnData['requestCount'] = $requestCount;

            return ResponseHelper::success('success', $returnData);
        } catch (Exception $e) {
            return ResponseHelper::swwrong('Something Went Wrong', ['err' => $e->getMessage()]);
        }
    }



    /**
     * DMT Graphs
     */
    public function graphDmt(Request $request, $type)
    {
        try {


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

            if (Auth::user()->is_admin == '0') {
                $userId = Auth::user()->id;
            } else {
                $userId = trim($request->userId);
            }

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
                        ->table('dmt_fund_transfers')
                        ->select(
                            DB::raw('sum(amount) as totAmt'),
                            DB::raw("DATE_FORMAT(created_at,'$stampDateFromat') as stamp"),
                            DB::raw("DATE_FORMAT(created_at,'$fullDateFormat') as mDate"),
                            'status'
                        );

                    if (!empty($userId)) {
                        $cwAepsSuccess->where('user_id', '=', $userId);
                    }

                    $cwAepsSuccess = $cwAepsSuccess->whereIn('status', ['processed', 'failed'])
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
                        ->table('dmt_outlets')
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
            }

            return ResponseHelper::failed('failed');
        } catch (Exception $e) {
            return ResponseHelper::swwrong('Something Went Wrong', ['err' => $e->getMessage()]);
        }
    }


    /**
     * MATM graph
     */
    public function graphMatm(Request $request, $type)
    {
        try {


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

            if (Auth::user()->is_admin == '0') {
                $userId = Auth::user()->id;
            } else {
                $userId = trim($request->userId);
            }

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
                        ->table('matm_transactions')
                        ->select(
                            DB::raw('sum(transaction_amount) as totAmt'),
                            DB::raw("DATE_FORMAT(created_at,'$stampDateFromat') as stamp"),
                            DB::raw("DATE_FORMAT(created_at,'$fullDateFormat') as mDate"),
                            'status'
                        )->where('transaction_type', 'cw');

                    if (!empty($userId)) {
                        $cwAepsSuccess->where('user_id', '=', $userId);
                    }

                    $cwAepsSuccess = $cwAepsSuccess->whereIn('status', ['processed', 'failed'])
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
                        ->table('matm_transactions')
                        ->select(
                            DB::raw('count(id) as totCount'),
                            DB::raw("DATE_FORMAT(created_at,'$stampDateFromat') as stamp"),
                            DB::raw("DATE_FORMAT(created_at,'$fullDateFormat') as mDate"),
                            'transaction_type'
                        )->whereIn('transaction_type', ['cw', 'be']);

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
            }

            return ResponseHelper::failed('failed');
        } catch (Exception $e) {
            return ResponseHelper::swwrong('Something Went Wrong', ['err' => $e->getMessage()]);
        }
    }


    /**
     * Recharge Graph
     */
    public function graphRecharge(Request $request, $type)
    {
        try {


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

            if (Auth::user()->is_admin == '0') {
                $userId = Auth::user()->id;
            } else {
                $userId = trim($request->userId);
            }

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
                        ->table('recharges')
                        ->select(
                            DB::raw('sum(amount) as totAmt'),
                            DB::raw("DATE_FORMAT(created_at,'$stampDateFromat') as stamp"),
                            DB::raw("DATE_FORMAT(created_at,'$fullDateFormat') as mDate"),
                            'status'
                        );

                    if (!empty($userId)) {
                        $cwAepsSuccess->where('user_id', '=', $userId);
                    }

                    $cwAepsSuccess = $cwAepsSuccess->whereIn('status', ['processed', 'failed'])
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



                case 'recharge-type':

                    $cwVolumeBankSuccess = DB::connection('slave')
                        ->table('recharges')
                        ->select(
                            // 'banks.bank',
                            'mst_operators.type',
                            DB::raw('sum(recharges.amount) as totAmt'),
                            'recharges.status'
                        )
                        ->join('mst_operators', 'mst_operators.id', 'recharges.operator_id');

                    if (!empty($userId)) {
                        $cwVolumeBankSuccess->where('recharges.user_id', $userId);
                    }

                    $cwVolumeBankSuccess = $cwVolumeBankSuccess->whereIn('recharges.status', ['processed', 'failed'])
                        ->whereDate('recharges.created_at', '>=', $startDate)
                        ->whereDate('recharges.created_at', '<=', $endDate)
                        ->groupBy('mst_operators.type', 'recharges.status')
                        ->get();

                    $returnData['startDate'] = $startDate;
                    $returnData['endDate'] = $endDate;
                    $returnData['rechargeTypeData'] = $cwVolumeBankSuccess;

                    return ResponseHelper::success('success', $returnData);
                    break;
            }

            return ResponseHelper::failed('failed');
        } catch (Exception $e) {
            return ResponseHelper::swwrong('Something Went Wrong', ['err' => $e->getMessage()]);
        }
    }


    /**
     * PAN Card Graph
     */
    public function graphPancard(Request $request, $type)
    {
        try {


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

            if (Auth::user()->is_admin == '0') {
                $userId = Auth::user()->id;
            } else {
                $userId = trim($request->userId);
            }

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
                        ->table('pan_txns')
                        ->select(
                            DB::raw('sum(fee) as totAmt'),
                            DB::raw("DATE_FORMAT(created_at,'$stampDateFromat') as stamp"),
                            DB::raw("DATE_FORMAT(created_at,'$fullDateFormat') as mDate"),
                            'status'
                        );

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
                        ->table('pan_agents')
                        ->select(
                            DB::raw('count(id) as totCount'),
                            DB::raw("DATE_FORMAT(created_at,'$stampDateFromat') as stamp"),
                            DB::raw("DATE_FORMAT(created_at,'$fullDateFormat') as mDate")
                        );

                    if (!empty($userId)) {
                        $merchantOnBoard->where('user_id', '=', $userId);
                    }

                    $merchantOnBoard = $merchantOnBoard->where('status', '1')
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
            }

            return ResponseHelper::failed('failed');
        } catch (Exception $e) {
            return ResponseHelper::swwrong('Something Went Wrong', ['err' => $e->getMessage()]);
        }
    }
}
