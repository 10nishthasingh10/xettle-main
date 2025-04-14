<?php

namespace App\Http\Controllers\Api\v1\Callbacks;

use App\Helpers\CashfreeAutoCollectHelper;
use App\Helpers\CommonHelper;
use App\Helpers\TransactionHelper;
use App\Http\Controllers\Controller;
use App\Jobs\PrimaryFundCredit;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FundCallbackController extends Controller
{
    public function callback(Request $request, $api)
    {
        try {

            $data = $request->all();


            DB::table('apilogs')->insert([
                'modal' => 'CashfreeAutoCollect',
                'txnid' => isset($data['utr']) ? $data['utr'] : "",
                'method' => 'Callback',
                'header' => 'NA',
                'request' => 'NA',
                'call_back_response' => json_encode($data),
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ]);


            if (empty($data['event'])) {
                $res['status'] = 'FAILURE';
                $res['message'] = 'Event is empty.';
                $res['time'] = date('Y-m-d H:i:s');
                // Storage::append('van_log_' . date('Y_m_d') . '.txt', print_r($res, true));
                return response()->json($res);
            }


            $txnAmount = !empty($data['amount']) ? $data['amount'] : 0;

            if ($txnAmount < 0) {
                $res['status'] = 'FAILURE';
                $res['message'] = 'Invalid Transaction Amount.';
                $res['time'] = date('Y-m-d H:i:s');
                return response()->json($res);
            }


            switch ($api) {

                case 'cashfree-van':
                    /**
                     * handle CASHFREE VAN API callback
                     */


                    //validate signature
                    $cacHelper = new CashfreeAutoCollectHelper();
                    $signMatch = $cacHelper->verifySignature($data);

                    if ($signMatch) {

                        //if webhook event is AMOUNT_COLLECTED
                        //insert record into the table fund_receive_callbacks
                        if ($data['event'] === 'AMOUNT_COLLECTED') {


                            $dataUtr = isset($data['utr']) ? $data['utr'] : '';
                            $vAccountId = isset($data['vAccountId']) ? $data['vAccountId'] : '';


                            //check that fund added by VAN API
                            //fetching user Id on the behalf of vAccountId
                            $cfMerchants = DB::table('cf_merchants')->select('id', 'user_id')
                                ->where('v_account_id', $vAccountId)
                                ->first();



                            if (!empty($cfMerchants)) {
                                //handel callback fund added by VAN API user
                                return CashfreeAutoCollectHelper::handleCfAutoCollectCredit($cfMerchants, $data);
                            }


                            //check for already transaction by UTR
                            $checkUTR = DB::table('fund_receive_callbacks')
                                ->where('utr', $dataUtr)
                                //->where('v_account_id', $vAccountId)
                                ->first();


                            if (!empty($checkUTR)) {
                                $res['status'] = 'FAILURE';
                                $res['message'] = 'Transaction Already Credited.';
                                $res['time'] = date('Y-m-d H:i:s');
                                // Storage::append('van_log_' . date('Y_m_d') . '.txt', print_r($res, true));
                                return response()->json($res);
                            }



                            //fetching user Id on the behalf of vAccountId
                            $businessInfo = DB::table('business_infos')->select('id', 'user_id')
                                ->where('van_acc_id', $vAccountId)
                                ->first();


                            if (empty($businessInfo)) {
                                $res['status'] = 'FAILURE';
                                $res['message'] = 'Invalid VAN or User ID';
                                $res['time'] = date('Y-m-d H:i:s');
                                // Storage::append('van_log_' . date('Y_m_d') . '.txt', print_r($res, true));
                                return response()->json($res);
                            }

                            $userId = $businessInfo->user_id;
                            $refId = 'BT_' . $data['utr'];
                            $trnType = 'van_collect';

                            //getting Product ID
                            $products = CommonHelper::getProductId($trnType, $trnType);

                            //fee and tax on fee calculation
                            $taxFee = (object) TransactionHelper::getFeesAndTaxes($products->product_id, $data['amount'], $userId);
                            $feeRate = $taxFee->margin;
                            $fee = round($taxFee->fee, 2);
                            $tax = round($taxFee->tax, 2);
                            $crAmount = $data['amount'] - $fee - $tax;

                            //store callback response
                            $vanData = [
                                'root_type' => 'cf_van',
                                'user_id' => $userId,
                                'amount' => $data['amount'],
                                'fee' => $fee,
                                'tax' => $tax,
                                'cr_amount' => $crAmount,
                                'fee_rate' => $feeRate,
                                'ref_no' => $refId,
                                'utr' => $data['utr'],
                                'v_account_id' => $data['vAccountId'],
                                'v_account_number' => (empty($data['vAccountNumber']) ? '' : $data['vAccountNumber']),
                                'reference_id' => $data['referenceId'],
                                'email' => $data['email'],
                                'phone' => $data['phone'],
                                'credit_ref_no' => $data['creditRefNo'],
                                'remitter_account' => $data['remitterAccount'],
                                'remitter_ifsc' => (empty($data['remitterIfsc']) ? '' : $data['remitterIfsc']),
                                'remitter_vpa' => (empty($data['remitterVpa']) ? '' : $data['remitterVpa']),
                                'remitter_name' => $data['remitterName'],
                                'transfer_type' => (empty($data['transferType']) ? '' : $data['transferType']),
                                'remarks' => (empty($data['remarks']) ? '' : $data['remarks']),
                                'payment_time' => $data['paymentTime'],
                                'is_trn_credited' => '0',
                                'created_at' => date('Y-m-d H:i:s')
                            ];

                            $vanData['rowId'] = DB::table('fund_receive_callbacks')->insertGetId($vanData);

                            $vanData['trnType'] = $trnType;


                            //check service is enable or not
                            $isServiceActive = CommonHelper::checkIsServiceActive('cf_partner_van', $userId);

                            if ($isServiceActive) {

                                try {
                                    PrimaryFundCredit::dispatch((object) $vanData, 'partner_van')->onQueue('primary_fund_queue');
                                } catch (Exception $e) {
                                    //inward credit error
                                    $res['status'] = 'FAILURE';
                                    $res['message'] = 'Inward Credit Error: ' . $e->getMessage();
                                    $res['time'] = date('Y-m-d H:i:s');
                                    // Storage::put('van_' . date('Y_m_d') . '.txt', print_r($e->getMessage(), true));
                                    return response()->json($res);
                                }
                            }
                        }



                        /**
                         * TRANSFER_REJECTED Event
                         */
                        else if ($data['event'] === 'TRANSFER_REJECTED') {


                            $check = DB::table('van_txn_logs')->select('utr')->where('utr', $data['utr'])
                                ->count();

                            if ($check === 0) {

                                $userId = null;

                                $vAccountId = !empty($data['vAccountId']) ? ($data['vAccountId']) : '';
                                $businessInfo = DB::table('business_infos')->select('id', 'user_id')
                                    ->where('van_acc_id', $vAccountId)
                                    ->first();


                                if (!empty($businessInfo)) {
                                    $userId = $businessInfo->user_id;
                                }


                                $vanData = [
                                    'type' => '0',
                                    'user_id' => $userId,
                                    'phone' => $data['phone'],
                                    'amount' => $data['amount'],
                                    'v_acc_id' => $data['vAccountId'],
                                    'txn_id' => $data['rejectId'],
                                    'utr' => $data['utr'],
                                    'remitter_acc' => $data['remitterAccount'],
                                    'transfer_time' => $data['transferTime'],
                                    'reason' => $data['reason'],
                                    'created_at' => date('Y-m-d H:i:s'),
                                ];

                                DB::table('van_txn_logs')->insert($vanData);
                            }
                        }


                        /**
                         * AMOUNT_SETTLED Event
                         */
                        else if ($data['event'] === 'AMOUNT_SETTLED') {


                            $check = DB::table('van_txn_logs')->select('utr')->where('utr', $data['utr'])
                                ->count();

                            if ($check === 0) {

                                $vanData = [
                                    'type' => '1',
                                    'amount' => $data['amount'],
                                    'settle_count' => $data['count'],
                                    'utr' => $data['utr'],
                                    'txn_id' => $data['settlementId'],
                                    'settlement_amount' => $data['settlementAmount'],
                                    'adjustment' => $data['adjustment'],
                                    'created_at' => date('Y-m-d H:i:s'),
                                ];

                                DB::table('van_txn_logs')->insert($vanData);
                            }
                        }


                        $res['status'] = true;
                        $res['message'] = 'Request captured successfully';
                        $res['data'] = $data;

                        return response()->json($res);
                    }

                    //when signature is not matched
                    else {
                        // Reject this call
                        $res['status'] = 'FAILURE';
                        $res['message'] = 'Invalid Signature';
                        $res['time'] = date('Y-m-d H:i:s');
                        // Storage::append('van_log_' . date('Y_m_d') . '.txt', print_r($res, true));
                        return response()->json($res);
                    }

                    break;


                default:
                    $res['status'] = 'FAILURE';
                    $res['message'] = 'Unexpected response received';
                    $res['time'] = date('Y-m-d H:i:s');
                    // Storage::append('van_log_' . date('Y_m_d') . '.txt', print_r($res, true));
                    return response()->json($res);
                    break;
            }
        } catch (Exception $e) {
            $res['status'] = 'FAILURE';
            $res['message'] = 'Error: ' . $e->getMessage();
            $res['time'] = date('Y-m-d H:i:s');
            // Storage::append('van_log_' . date('Y_m_d') . '.txt', print_r($res, true));
            return response()->json($res);
        }
    }
}
