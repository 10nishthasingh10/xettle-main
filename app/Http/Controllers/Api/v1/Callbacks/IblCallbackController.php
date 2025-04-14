<?php

namespace App\Http\Controllers\Api\v1\Callbacks;

use App\Helpers\CommonHelper;
use App\Helpers\IBLUpiHelper;
use App\Helpers\TransactionHelper;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Helpers\WebhookHelper;
use Illuminate\Support\Facades\DB;

class IblCallbackController extends Controller
{

    public function callback(Request $post, $api)
    {
        switch ($api) {
            case 'IBL':

                $data = $post->all();

                $logId = DB::table('apilogs')->insertGetId([
                    'modal' => 'ibl',
                    'txnid' => isset($data['referenceId']) ? $data['referenceId'] : "encrypted",
                    'method' => 'Callback',
                    'header' => json_encode($post->header()),
                    'request' => 'NA',
                    'encrypt_response' => json_encode($data),
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s')
                ]);


                if (isset($data['meRes']))
                    $data = json_decode($data['meRes'], 1);



                if (!empty($data)) {

                    $dataResponse = @$data['resp'];

                    $iblHelper = new IBLUpiHelper();
                    $data = $iblHelper->decryptUPIdata($dataResponse, 0);

                    $customerRefId = isset($data['text']['apiResp']['custRefNo']) ? $data['text']['apiResp']['custRefNo'] : '';

                    DB::table('apilogs')
                        ->where('id', $logId)
                        ->update([
                            'txnid' => $customerRefId,
                            'response' => json_encode($data)
                        ]);


                    $callbackStatus = isset($data['text']['apiResp']['status']) ? $data['text']['apiResp']['status'] : '';

                    if ($callbackStatus === "SUCCESS") {

                        $upiOrder = DB::table('upi_callbacks')
                            ->select('id')
                            ->where('customer_ref_id', $customerRefId)
                            ->first();

                        if (!empty($upiOrder)) {
                            $res['status'] = 'FAILURE';
                            $res['message'] = 'Duplicate callback response received.';
                            $res['time'] = date('Y-m-d H:i:s');

                            return response()->json($res);
                        }


                        $payeeVpa = isset($data['text']['apiResp']['payeeVPA']) ? $data['text']['apiResp']['payeeVPA'] : '';

                        $getUserId = DB::table('upi_merchants')
                            ->select('user_id', 'tpv_status', 'root_type', 'allowed_vpa', 'allowed_bank')
                            ->where('merchant_virtual_address', $payeeVpa)
                            ->where(function ($sql) {
                                $sql->where('root_type', 'ibl')
                                    ->orWhere('root_type', ROOT_TYPE_VA);
                            })
                            ->first();

                        if (empty($getUserId)) {
                            $res['status'] = 'FAILURE';
                            $res['message'] = 'Invalid payee VPA.';
                            $res['time'] = date('Y-m-d H:i:s');

                            return response()->json($res);
                        }

                        //calculation fee and tax
                        $amount = isset($data['text']['apiResp']['amount']) ? $data['text']['apiResp']['amount'] : 0;

                        if (empty($amount)) {
                            $res['status'] = 'FAILURE';
                            $res['message'] = 'Invalid amount received.';
                            $res['time'] = date('Y-m-d H:i:s');

                            return response()->json($res);
                        }

                        $payerVpa = isset($data['text']['apiResp']['payerVPA']) ? $data['text']['apiResp']['payerVPA'] : '';
                        $payerIfsc = isset($data['text']['apiResp']['addInfo']['addInfo4']) ? $data['text']['apiResp']['addInfo']['addInfo4'] : '';
                        $payerAccNo = isset($data['text']['apiResp']['addInfo']['addInfo2']) ? $data['text']['apiResp']['addInfo']['addInfo2'] : '';

                        //check that tvp is enabled or not
                        if ($getUserId->tpv_status == '1') {
                            $upiOrder = DB::table('upi_reverse_transactions')
                                ->select('id')
                                ->where('customer_ref_id', $customerRefId)
                                // ->where('status', 'success')
                                ->first();

                            if (!empty($upiOrder)) {
                                $res['status'] = 'FAILURE';
                                $res['message'] = 'Duplicate callback, reversed initiated.';
                                $res['time'] = date('Y-m-d H:i:s');

                                return response()->json($res);
                            }

                            $listPayerVpa = explode(',', $getUserId->allowed_vpa);
                            $payerBankInfo = explode(',', $getUserId->allowed_bank);

                            $isTxnReversed = true;
                            //check payer vpa is listed or not
                            if (in_array($payerVpa, $listPayerVpa)) {
                                $isTxnReversed = false;
                            }

                            if (count($payerBankInfo) == 2) {
                                if (($payerAccNo == $payerBankInfo[0] && $payerIfsc == $payerBankInfo[1]) && $isTxnReversed == true) {
                                    $isTxnReversed = false;
                                }
                            }


                            //check payer vpa is listed or not
                            if ($isTxnReversed === true) {
                                //call refund api

                                $upiInsertData = [
                                    'root_type' => ROOT_TYPE_VA,
                                    'user_id' => $getUserId->user_id,
                                    'payee_vpa' => $payeeVpa,
                                    'amount' => floatval($amount),
                                    'txn_note' => $data['text']['apiResp']['txnNote'],
                                    'description' => $data['text']['apiResp']['currentStatusDesc'],
                                    'type' => $data['text']['apiResp']['txnType'],
                                    'npci_txn_id' => $data['text']['apiResp']['npciTransId'],
                                    'original_order_id' => $data['text']['apiResp']['approvalNumber'],
                                    'merchant_txn_ref_id' => $data['text']['apiResp']['pspRefNo'],
                                    'bank_txn_id' => $data['text']['apiResp']['orderNo'],
                                    'code' => '0x0200',
                                    'response_code' => $data['text']['apiResp']['responseCode'],
                                    'customer_ref_id' => $data['text']['apiResp']['custRefNo'],
                                    'payer_vpa' => $payerVpa,
                                    'payer_mobile' => $data['text']['apiResp']['addInfo']['addInfo1'],
                                    'payer_acc_no' => $data['text']['apiResp']['addInfo']['addInfo2'],
                                    'payer_acc_name' => $data['text']['apiResp']['addInfo']['addInfo3'],
                                    'payer_ifsc' => $data['text']['apiResp']['addInfo']['addInfo4'],
                                    'txn_date' => $data['text']['apiResp']['txnDate'],
                                    'is_trn_reversed' => '0',
                                ];

                                //calling refund api
                                $iblHelper->refundAmount($upiInsertData);

                                $res['status'] = 'SUCCESS';
                                $res['message'] = 'Request captured successfully for ' . $customerRefId;
                                $res['time'] = date('Y-m-d H:i:s');

                                return response()->json($res);
                            }
                        }

                        //getting service ID
                        if ($getUserId->root_type == ROOT_TYPE_VA) {
                            $products = CommonHelper::getProductId('va_inward', SRV_SLUG_VA);
                            $rootType = ROOT_TYPE_VA;
                            $serviceSlug = SRV_SLUG_VA;
                        } else {
                            $products = CommonHelper::getProductId('upi_collect', 'upi_collect');
                            $rootType = 'ibl';
                            $serviceSlug = 'upi_collect';
                        }

                        //fee and tax on fee calculation
                        $taxFee = (object) TransactionHelper::getFeesAndTaxes($products->product_id, $amount, $getUserId->user_id);

                        $feeRate = $taxFee->margin;
                        $fee = round($taxFee->fee, 2);
                        $tax = round($taxFee->tax, 2);
                        $crAmount = $amount - $fee - $tax;

                        //generate Batch ID for UPI callback transaction
                        $batchId = 'IBLUPI' . $getUserId->user_id . 'ST' . (date("YmdH") . '0' . (int)((date('i') / 30)));

                        //insert new record
                        $upiInsertData = [
                            'root_type' => $rootType,
                            'batch_id' => $batchId,
                            'user_id' => $getUserId->user_id,
                            'payee_vpa' => $payeeVpa,
                            'amount' => floatval($amount),
                            'fee' => $fee,
                            'tax' => $tax,
                            'cr_amount' => $crAmount,
                            'fee_rate' => $feeRate,
                            'txn_note' => $data['text']['apiResp']['txnNote'],
                            'description' => $data['text']['apiResp']['currentStatusDesc'],
                            'type' => $data['text']['apiResp']['txnType'],
                            'npci_txn_id' => $data['text']['apiResp']['npciTransId'],
                            'original_order_id' => $data['text']['apiResp']['approvalNumber'],
                            'merchant_txn_ref_id' => $data['text']['apiResp']['pspRefNo'],
                            'bank_txn_id' => $data['text']['apiResp']['orderNo'],
                            'code' => '0x0200', //$data['text']['apiResp']['responseCode'],
                            'response_code' => $data['text']['apiResp']['responseCode'],
                            'customer_ref_id' => $data['text']['apiResp']['custRefNo'],
                            'payer_vpa' => $data['text']['apiResp']['payerVPA'],
                            'payer_mobile' => $data['text']['apiResp']['addInfo']['addInfo1'],
                            'payer_acc_no' => $data['text']['apiResp']['addInfo']['addInfo2'],
                            'payer_acc_name' => $data['text']['apiResp']['addInfo']['addInfo3'],
                            'payer_ifsc' => $data['text']['apiResp']['addInfo']['addInfo4'],
                            'txn_date' => $data['text']['apiResp']['txnDate'],
                            'is_trn_credited' => '0',
                            'is_trn_settle' => '0',
                            'created_at' => date('Y-m-d H:i:s')
                        ];

                        DB::table('upi_callbacks')->insert($upiInsertData);


                        //check service is enable or not
                        $isServiceActive = CommonHelper::checkIsServiceActive($serviceSlug, $getUserId->user_id);

                        //check callback is enable or not
                        $isCallbackActive = CommonHelper::checkIsCallbackActive($getUserId->user_id, $serviceSlug, $rootType);

                        if ($isServiceActive && $isCallbackActive) {

                            $getWebhooks = DB::table('webhooks')
                                ->where('user_id', $getUserId->user_id)
                                ->first();

                            if (!empty($getWebhooks)) {
                                $url = $getWebhooks->webhook_url;
                                $secret = $getWebhooks->secret;

                                if (isset($getWebhooks->header_key) && isset($getWebhooks->header_value)) {
                                    $headers = [$getWebhooks->header_key => $getWebhooks->header_value];
                                    WebhookHelper::UPISuccess((object) $upiInsertData, $url, $secret, $headers);
                                } else {
                                    WebhookHelper::UPISuccess((object) $upiInsertData, $url, $secret);
                                }
                            }
                        }


                        $res['status'] = 'SUCCESS';
                        $res['message'] = 'Request captured successfully';
                        $res['time'] = date('Y-m-d H:i:s');

                        return response()->json($res);
                    } else {
                        $res['status'] = 'FAILURE';
                        $res['message'] = 'Status: ' . $callbackStatus;
                        $res['time'] = date('Y-m-d H:i:s');

                        return response()->json($res);
                    }
                } else {
                    $res['status'] = 'FAILURE';
                    $res['message'] = 'Data is empty';
                    $res['time'] = date('Y-m-d H:i:s');

                    return response()->json($res);
                }

                break;

            default:
                $res['status'] = 'FAILURE';
                $res['message'] = 'Unexpected response type received';
                $res['time'] = date('Y-m-d H:i:s');

                return response()->json($res);
                break;
        }
    }
}
