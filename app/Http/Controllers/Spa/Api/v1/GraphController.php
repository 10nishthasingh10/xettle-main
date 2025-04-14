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

class GraphController extends Controller
{


    /**
     * AEPS Graphs
     */
    public function graphOCR(Request $request, $type)
    {
        // $userId = @$request->user()->id;
        $userId = Auth::user()->id;
        $accountNumber = Auth::user()->account_number;

        if (empty($userId)) {
            $message = "Unauthorized user.";
            return ResponseHelper::missing($message);
        }
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

            $graphType = strtolower(trim($type));

            $startDate = trim($request->startDate);
            $endDate = trim($request->endDate);


            switch ($graphType) {

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
                        ->table('ocrs')
                        ->select(
                            DB::raw('count(id) as totCount'),
                            DB::raw("DATE_FORMAT(created_at,'$stampDateFromat') as stamp"),
                            DB::raw("DATE_FORMAT(created_at,'$fullDateFormat') as mDate"),
                            'type'
                        )->whereIn('type', ['pan', 'aadhaar', 'driving']);

                    if (!empty($userId)) {
                        $cwAepsCount->where('user_id', $userId);
                    }

                    $cwAepsCount = $cwAepsCount->where('status', 'success')
                        ->whereDate('created_at', '>=', $startDate)
                        ->whereDate('created_at', '<=', $endDate)
                        ->groupBy('mDate', 'stamp', 'type')
                        ->orderBy('stamp', 'asc')
                        ->get();


                    $returnData['lables'] = $lables;
                    $returnData['startDate'] = $startDate;
                    $returnData['endDate'] = $endDate;
                    $returnData['countData'] = $cwAepsCount;

                    return ResponseHelper::success('success', $returnData);

                    break;

                case 'validate-txn-counts':
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
                        ->table('validations')
                        ->select(
                            DB::raw('count(id) as totCount'),
                            DB::raw("DATE_FORMAT(created_at,'$stampDateFromat') as stamp"),
                            DB::raw("DATE_FORMAT(created_at,'$fullDateFormat') as mDate"),
                            'type'
                        )->whereIn('type', ['pan', 'aadhaar', 'bank', 'upi', 'ocr_pan', 'ocr_aadhaar', 'ocr_cheque']);

                    if (!empty($userId)) {
                        $cwAepsCount->where('user_id', $userId);
                    }

                    $cwAepsCount = $cwAepsCount->where('status', 'success')
                        ->whereDate('created_at', '>=', $startDate)
                        ->whereDate('created_at', '<=', $endDate)
                        ->groupBy('mDate', 'stamp', 'type')
                        ->orderBy('stamp', 'asc')
                        ->get();


                    $returnData['lables'] = $lables;
                    $returnData['startDate'] = $startDate;
                    $returnData['endDate'] = $endDate;
                    $returnData['countData'] = $cwAepsCount;

                    return ResponseHelper::success('success', $returnData);

                    break;


                case 'ocr-wallet':
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
                    DB::enableQueryLog();
                    $transactionsIn = DB::connection('slave')->table('transactions')
                        ->select(
                            DB::raw('ABS(sum(tr_total_amount)) as amt'),
                            DB::raw("DATE_FORMAT(created_at,'$stampDateFromat') as stamp"),
                            DB::raw("DATE_FORMAT(created_at,'$fullDateFormat') as mDate")
                        )
                        ->where('tr_type', 'cr');
                    // ->whereIn('tr_identifiers', ['upi_ocr_credit']);

                    if (!empty($userId)) {
                        $transactionsIn->where('user_id', $userId)
                            ->where('account_number', $accountNumber);
                    }

                    $transactionsIn = $transactionsIn->whereDate('created_at', '>=', $startDate)
                        ->whereDate('created_at', '<=', $endDate)
                        ->groupBy('mDate', 'stamp')
                        ->orderBy('stamp', 'asc')
                        ->get();

                    //OCR Wallet Out
                    $transactionsOut = DB::connection('slave')->table('transactions')
                        ->select(
                            DB::raw('ABS(sum(tr_total_amount)) as amt'),
                            DB::raw("DATE_FORMAT(created_at,'$stampDateFromat') as stamp"),
                            DB::raw("DATE_FORMAT(created_at,'$fullDateFormat') as mDate")
                        )
                        ->where('tr_type', 'dr');
                    // ->whereIn('tr_identifiers', ['ocr_pan_debit', 'ocr_aadhaar_debit', 'ocr_cheque_debit']);

                    if (!empty($userId)) {
                        $transactionsOut->where('user_id', $userId)
                            ->where('account_number', $accountNumber);
                    }

                    $transactionsOut = $transactionsOut->whereDate('created_at', '>=', $startDate)
                        ->whereDate('created_at', '<=', $endDate)
                        ->groupBy('mDate', 'stamp')
                        ->orderBy('stamp', 'asc')
                        ->get();


                    //dd($transactionsOut,$userId,$startDate,$endDate);
                    $returnData['lables'] = $lables;
                    $returnData['startDate'] = $startDate;
                    $returnData['endDate'] = $endDate;
                    $returnData['inward'] = $transactionsIn;
                    $returnData['outward'] = $transactionsOut;

                    return ResponseHelper::success('success', $returnData);

                    break;
                case 'validate-wallet':
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
                    DB::enableQueryLog();
                    $transactionsIn = DB::connection('slave')->table('transactions')
                        ->select(
                            DB::raw('ABS(sum(tr_total_amount)) as amt'),
                            DB::raw("DATE_FORMAT(created_at,'$stampDateFromat') as stamp"),
                            DB::raw("DATE_FORMAT(created_at,'$fullDateFormat') as mDate")
                        )
                        ->whereIn('tr_identifiers', ['upi_validate_credit']);

                    if (!empty($userId)) {
                        $transactionsIn->where('user_id', $userId);
                    }

                    $transactionsIn = $transactionsIn->whereDate('created_at', '>=', $startDate)
                        ->whereDate('created_at', '<=', $endDate)
                        ->groupBy('mDate', 'stamp')
                        ->orderBy('stamp', 'asc')
                        ->get();
                    //validate Wallet Out
                    $transactionsOut = DB::connection('slave')->table('transactions')
                        ->select(
                            DB::raw('ABS(sum(tr_total_amount)) as amt'),
                            DB::raw("DATE_FORMAT(created_at,'$stampDateFromat') as stamp"),
                            DB::raw("DATE_FORMAT(created_at,'$fullDateFormat') as mDate")
                        )
                        ->whereIn('tr_identifiers', ['verification_vpa_debit', 'verification_bank_debit', 'verification_ifsc_debit', 'verification_aadhaar_debit', 'verification_pan_debit']);

                    if (!empty($userId)) {
                        $transactionsOut->where('user_id', $userId);
                    }

                    $transactionsOut = $transactionsOut->whereDate('created_at', '>=', $startDate)
                        ->whereDate('created_at', '<=', $endDate)
                        ->groupBy('mDate', 'stamp')
                        ->orderBy('stamp', 'asc')
                        ->get();


                    //dd($transactionsOut,$userId,$startDate,$endDate);
                    $returnData['lables'] = $lables;
                    $returnData['startDate'] = $startDate;
                    $returnData['endDate'] = $endDate;
                    $returnData['inward'] = $transactionsIn;
                    $returnData['outward'] = $transactionsOut;

                    return ResponseHelper::success('success', $returnData);

                    break;

                case 'recharge-transaction':
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
                    // DB::enableQueryLog();

                    //Mobile Recharge Success
                    $mobileRechargeSuccess = DB::connection('slave')->table('recharges')
                        ->select(
                            DB::raw("mst_operators.type as rechargeType"),
                            DB::raw('SUM(recharges.amount) as amt'),
                            DB::raw("DATE_FORMAT(recharges.created_at,'$stampDateFromat') as stamp"),
                            DB::raw("DATE_FORMAT(recharges.created_at,'$fullDateFormat') as mDate")
                        )
                        ->join('mst_operators', 'mst_operators.id', '=', 'recharges.operator_id')
                        // ->where('mst_operators.type', 'mobile')
                        ->where('recharges.status', 'processed');

                    if (!empty($userId)) {
                        $mobileRechargeSuccess->where('recharges.user_id', $userId);
                    }

                    $mobileRechargeSuccess = $mobileRechargeSuccess->whereDate('recharges.created_at', '>=', $startDate)
                        ->whereDate('recharges.created_at', '<=', $endDate)
                        ->groupBy('rechargeType', 'mDate', 'stamp')
                        ->orderBy('stamp', 'asc')
                        ->get();


                    //DTH Recharge Success
                    // $dthRechargeSuccess = DB::connection('slave')->table('recharges')
                    //     ->select(
                    //         DB::raw('SUM(recharges.amount) as amt'),
                    //         DB::raw("DATE_FORMAT(recharges.created_at,'$stampDateFromat') as stamp"),
                    //         DB::raw("DATE_FORMAT(recharges.created_at,'$fullDateFormat') as mDate")
                    //     )
                    //     ->join('mst_operators', 'mst_operators.id', '=', 'recharges.operator_id')
                    //     ->where('mst_operators.type', 'dth')
                    //     ->where('recharges.status', 'processed');

                    // if (!empty($userId)) {
                    //     $dthRechargeSuccess->where('recharges.user_id', $userId);
                    // }

                    // $dthRechargeSuccess = $dthRechargeSuccess->whereDate('recharges.created_at', '>=', $startDate)
                    //     ->whereDate('recharges.created_at', '<=', $endDate)
                    //     ->groupBy('mDate', 'stamp')
                    //     ->orderBy('stamp', 'asc')
                    //     ->get();


                    // $transactionsOut = DB::connection('slave')->table('recharges')
                    //     ->select(
                    //         DB::raw('SUM(amount) as amt'),
                    //         DB::raw("DATE_FORMAT(created_at,'$stampDateFromat') as stamp"),
                    //         DB::raw("DATE_FORMAT(created_at,'$fullDateFormat') as mDate")
                    //     )
                    //     ->where('status', 'failed');

                    // if (!empty($userId)) {
                    //     $transactionsOut->where('user_id', $userId);
                    // }

                    // $transactionsOut = $transactionsOut->whereDate('created_at', '>=', $startDate)
                    //     ->whereDate('created_at', '<=', $endDate)
                    //     ->groupBy('mDate', 'stamp')
                    //     ->orderBy('stamp', 'asc')
                    //     ->get();


                    //dd($transactionsOut,$userId,$startDate,$endDate);
                    $returnData['lables'] = $lables;
                    $returnData['startDate'] = $startDate;
                    $returnData['endDate'] = $endDate;
                    $returnData['success'] = $mobileRechargeSuccess;
                    // $returnData['mobileSuccess'] = $mobileRechargeSuccess;
                    // $returnData['dthSuccess'] = $dthRechargeSuccess;
                    // $returnData['failed'] = $transactionsOut;

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
                    'endDate' => 'required|date'
                    // 'userId' => 'nullable|numeric|min:0'
                ]
            );

            if ($validator->fails()) {
                $message = json_decode(json_encode($validator->errors()), true);
                return ResponseHelper::missing($message);
            }

            $userId = Auth::user()->id;

            $graphType = strtolower(trim($type));


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

                    $cwAepsSuccess = $cwAepsSuccess->whereIn('status', ['success', 'pending'])
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

                case 'transaction-ap':
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
                        )->where('transaction_type', 'ap');

                    if (!empty($userId)) {
                        $cwAepsSuccess->where('user_id', '=', $userId);
                    }

                    $cwAepsSuccess = $cwAepsSuccess->whereIn('status', ['success', 'pending'])
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
     * MATM Graphs
     */
    public function graphMatm(Request $request, $type)
    {
        try {


            $validator = Validator::make(
                $request->all(),
                [
                    'startDate' => 'required|date|before_or_equal:endDate',
                    'endDate' => 'required|date'
                    // 'userId' => 'nullable|numeric|min:0'
                ]
            );

            if ($validator->fails()) {
                $message = json_decode(json_encode($validator->errors()), true);
                return ResponseHelper::missing($message);
            }

            $userId = Auth::user()->id;

            $graphType = strtolower(trim($type));


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
                        )->where('transaction_type', 'cw')
                        ->where('user_id', '=', $userId)
                        ->where('status', 'processed')
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
                        )->whereIn('transaction_type', ['cw', 'be'])
                        ->where('user_id', $userId)
                        ->where('status', 'processed')
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
            }

            return ResponseHelper::failed('failed');
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
                    'endDate' => 'required|date'
                    // 'userId' => 'nullable|numeric|min:0'
                ]
            );

            if ($validator->fails()) {
                $message = json_decode(json_encode($validator->errors()), true);
                return ResponseHelper::missing($message);
            }

            $userId = Auth::user()->id;

            $graphType = strtolower(trim($type));


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

                    $cwAepsSuccess = $cwAepsSuccess->whereIn('status', ['processed', 'processing', 'queued'])
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
            }

            return ResponseHelper::failed('failed');
        } catch (Exception $e) {
            return ResponseHelper::swwrong('Something Went Wrong', ['err' => $e->getMessage()]);
        }
    }

    public function graphPanCard(Request $request, $type)
    {
        try {
            $userId = Auth::user()->id;

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

            $graphType = strtolower(trim($type));
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


            switch ($type) {
                case 'agentOnboard':
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

                case 'transaction':

                    $cwAepsSuccess = DB::connection('slave')
                        ->table('pan_txns')
                        ->select(
                            DB::raw('sum(fee) as totAmt'),
                            DB::raw("DATE_FORMAT(created_at,'$stampDateFromat') as stamp"),
                            DB::raw("DATE_FORMAT(created_at,'$fullDateFormat') as mDate"),
                            'status'
                        )
                        ->where('user_id', '=', $userId)
                        ->where('status', 'success')
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
                    $cwAepsCount = DB::connection('slave')
                        ->table('pan_txns')
                        ->select(
                            DB::raw('count(id) as totCount'),
                            DB::raw("DATE_FORMAT(created_at,'$stampDateFromat') as stamp"),
                            DB::raw("DATE_FORMAT(created_at,'$fullDateFormat') as mDate"),
                            'txn_type','coupon_type'
                        )->whereIn('txn_type', ['nsdl', 'uti'])
                        ->where('user_id', $userId)
                        ->where('status', 'success')
                        ->whereDate('created_at', '>=', $startDate)
                        ->whereDate('created_at', '<=', $endDate)
                        ->groupBy('mDate', 'stamp', 'txn_type','coupon_type')
                        ->orderBy('stamp', 'asc')
                        ->get();


                    $returnData['lables'] = $lables;
                    $returnData['startDate'] = $startDate;
                    $returnData['endDate'] = $endDate;
                    $returnData['aepsCountData'] = $cwAepsCount;

                    return ResponseHelper::success('success', $returnData);


            }
            return ResponseHelper::failed('failed');
        } catch (Exception $e) {
            return ResponseHelper::swwrong('Something Went Wrong', ['err' => $e->getMessage()]);
        }
    }
}