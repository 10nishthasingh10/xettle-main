<?php

namespace App\Http\Controllers\Api\v1;

use App\Helpers\CommonHelper;
use App\Helpers\TransactionHelper;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Helpers\WebhookHelper;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class CallbackController extends Controller
{
    public function callback(Request $post, $api)
    {
        // try {
        switch ($api) {
            case 'fidypay':

                $data = $post->all();

                $resType = isset($data['type']) ? $data['type'] : 'NA';
                $customerRefId = isset($data['customerRefId']) ? $data['customerRefId'] : '';
                $responseCode = isset($data['responseCode']) ? $data['responseCode'] : '';

                DB::table('apilogs')->insert([
                    'modal' => 'fidypay',
                    'method' => 'Callback',
                    'response' => json_encode($data),
                    'txnid' => $customerRefId,
                    'resp_type' => $resType,
                    'resp_code' => isset($data['responseCode']) ? $data['responseCode'] : 'NA',
                    'resp_message' => isset($data['description']) ? $data['description'] : 'NA',
                    'created_at' => date('Y-m-d H:i:s')
                ]);


                switch ($resType) {
                    case 'PAYMENT_RECV':

                        if ($responseCode != "0x0200") {
                            $res['status'] = 'SUCCESS';
                            $res['message'] = 'Request captured successfully ' . $customerRefId;
                            $res['time'] = date('Y-m-d H:i:s');

                            return response()->json($res);
                        }

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

                        $getUserId = DB::table('upi_merchants')
                            ->select('user_id')
                            ->where('merchant_virtual_address', $data['payeeVPA'])
                            ->first();

                        $userId = isset($getUserId->user_id) ? $getUserId->user_id : '46';

                        //calculation fee and tax
                        $amount = !empty($data['amount']) ? $data['amount'] : 0;

                        if (empty($amount)) {
                            $res['status'] = 'FAILURE';
                            $res['message'] = 'Invalid amount received.';
                            $res['time'] = date('Y-m-d H:i:s');

                            return response()->json($res);
                        }

                        //getting service ID
                        $products = CommonHelper::getProductId('upi_collect', 'upi_collect');

                        //fee and tax on fee calculation
                        $taxFee = (object) TransactionHelper::getFeesAndTaxes($products->product_id, $amount, $userId);

                        $feeRate = $taxFee->margin;
                        $fee = round($taxFee->fee, 2);
                        $tax = round($taxFee->tax, 2);
                        $crAmount = $amount - $fee - $tax;

                        //generate Batch ID for UPI callback transaction
                        $batchId = 'YBLUPI' . $userId . 'ST' . (date("YmdH") . '0' . (int)((date('i') / 30)));

                        //insert new record
                        $upiInsertData = [
                            'root_type' => 'fpay',
                            'batch_id' => $batchId,
                            'user_id' => $userId,
                            'payee_vpa' => $data['payeeVPA'],
                            'amount' => $amount,
                            'fee' => $fee,
                            'tax' => $tax,
                            'cr_amount' => $crAmount,
                            'fee_rate' => $feeRate,
                            'txn_note' => $data['trxnNote'],
                            'description' => $data['description'],
                            'type' => $data['type'],
                            'npci_txn_id' => $data['npciTrxnId'],
                            'original_order_id' => $data['originalOrderId'],
                            'merchant_txn_ref_id' => $data['merchantTrxnRefId'],
                            'bank_txn_id' => $data['bankTrxnId'],
                            'code' => $data['code'],
                            'response_code' => $data['responseCode'],
                            'customer_ref_id' => $data['customerRefId'],
                            'payer_vpa' => $data["payerVPA"],
                            'payer_acc_name' => $data['payerAccName'],
                            'payer_mobile' => $data['payerMobileNO'],
                            'payer_acc_no' => $data['payerAccNo'],
                            'payer_ifsc' => $data['payerIfsc'],
                            'txn_date' => $data['date'],
                            'is_trn_credited' => '0',
                            'is_trn_settle' => '0',
                            'created_at' => date('Y-m-d H:i:s')
                        ];

                        DB::table('upi_callbacks')->insert($upiInsertData);

                        //check service is enable or not
                        $isServiceActive = CommonHelper::checkIsServiceActive('upi_collect', $userId);

                        //check callback is enable or not
                        $isCallbackActive = CommonHelper::checkIsCallbackActive($userId, 'upi_stack', 'fpay');

                        if ($isServiceActive && $isCallbackActive) {

                            $getWebhooks = DB::table('webhooks')
                                ->where('user_id', $userId)
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
                        // $res['data'] = $upiInsertData;
                        $res['message'] = 'Request captured successfully for ' . $customerRefId;
                        $res['time'] = date('Y-m-d H:i:s');

                        return response()->json($res);
                        break;

                    default:
                        $res['status'] = 'FAILURE';
                        $res['message'] = 'Unexpected response type received';
                        $res['time'] = date('Y-m-d H:i:s');

                        return response()->json($res);
                        break;
                }

                break;
            case 'mahab':
                $data = $post->all();
                $status = 0;
                if (isset($data) && !empty($data)) {
                    DB::table('apilogs')
                        ->insert([
                            'user_id' => 1, 'url' => 'url', 'txnid' => time(),
                            'method' => 'callbacks', 'created_at' => date('Y-m-d H:i:s'),
                            'modal' => 'mahab', 'call_back_response' => json_encode($data)
                        ]);
                    $status = 1;
                }
                if ($status == 1) {
                    $res = ['status' => 200, 'message' => 'Callback data accepected.'];
                } elseif ($status == 2) {
                    $res = ['status' => 202, 'message' => 'Callback data already exits.'];
                } elseif ($status == 0) {
                    $res = ['status' => 202, 'message' => 'Please send callback data.'];
                } else {
                    $res = ['status' => 201, 'message' => 'Callback data is invalid.'];
                }
                return json_encode($res);
                break;
            case 'baas':
                    $data = $post->all();
                    $status = 0;
                    if (isset($data) && !empty($data)) {
                        DB::table('apilogs')
                            ->insert([
                                'user_id' => 1, 'url' => 'url', 'txnid' => time(),
                                'method' => 'callbacks', 'created_at' => date('Y-m-d H:i:s'),
                                'modal' => 'baas', 'call_back_response' => json_encode($data)
                            ]);
                        $status = 1;
                    }
                    if ($status == 1) {
                        $res = ['status' => 200, 'message' => 'Callback data accepected.'];
                    } elseif ($status == 2) {
                        $res = ['status' => 202, 'message' => 'Callback data already exits.'];
                    } elseif ($status == 0) {
                        $res = ['status' => 202, 'message' => 'Please send callback data.'];
                    } else {
                        $res = ['status' => 201, 'message' => 'Callback data is invalid.'];
                    }
                    return json_encode($res);
                break;
                case 'uniqueebazaar':
                    $data = $post->all();
                    $status = 0;
                   /* if (isset($data) && !empty($data)) {*/
                        DB::table('apilogs')
                            ->insert([
                                'user_id' => 1, 'url' => 'url', 'txnid' => time(),
                                'method' => 'callbacks', 'created_at' => date('Y-m-d H:i:s'),
                                'modal' => 'uniqueebazaar', 'call_back_response' => json_encode($data)
                            ]);
                        $status = 1;
                   /* }*/
                    if ($status == 1) {
                        $res = ['status' => 200, 'message' => 'Callback data accepected.'];
                    } elseif ($status == 2) {
                        $res = ['status' => 202, 'message' => 'Callback data already exits.'];
                    } elseif ($status == 0) {
                        $res = ['status' => 202, 'message' => 'Please send callback data.'];
                    } else {
                        $res = ['status' => 201, 'message' => 'Callback data is invalid.'];
                    }
                    return json_encode($res);
                break;
            case 'ibrpay':
                \DB::table('callbacklogs')->insert(['response'=>json_encode($post->all())]);
                //dd($post->data);
                $report = \DB::table('upi_collects')->where('status','pending')->where('upi_txn_id',$post->data['OrderKeyId'])->first();
                if($report){
                    if($post->code=="TXN" && $post->data['OrderPaymentStatusText']=="Paid" && $post->data['OrderPaymentStatusText']=='Paid'){
                        
                            
                        $products = CommonHelper::getProductId('upi_collect', 'upi_collect');

                        //fee and tax on fee calculation
                        $amount = !empty($post->data['amount']) ? $post->data['amount'] : 0;
                        $userId = isset($report->user_id) ? $report->user_id : '46';
                        $taxFee = (object) TransactionHelper::getFeesAndTaxes($products->product_id, $amount, $userId);

                        $feeRate = $taxFee->margin;
                        $fee = round($taxFee->fee, 2);
                        $tax = round($taxFee->tax, 2);
                        $crAmount = $amount - $fee - $tax;


                        //generate Batch ID for UPI callback transaction
                        $batchId = 'YBLUPI' . $userId . 'ST' . (date("YmdH") . '0' . (int)((date('i') / 30)));
                       
                        //insert new record
                        $upiInsertData = [
                            "npci_txn_id"=> $post->data['OrderKeyId'],
                            "payer_vpa"=>$post->data['PaymentAccount'],
                            "amount"=>$amount,
                            "originalOrderId"=>$report->original_order_id,
                            "merchantTxnRefId"=>$report->merchant_txn_ref_id,
                            "customerRefId"=>$report->merchant_txn_ref_id,
                            "description"=>'Order Updated',
                            'bank_txn_id'=>$post->data['PaymentTransactionId'],
                            "code"=>$post->status
                            ];
                            
                        \DB::table('upi_collects')
                                ->where('status','pending')->where('upi_txn_id',$post->data['OrderKeyId'])
                                ->update([
                                    //"npci_txn_id"=> $post->data['upi_txn_id'],
                                    'payer_vpa' => $post->data['PaymentAccount'],
                                    'description' => 'Order Updated',
                                    'resp_code'=>$post->data['OrderPaymentStatusText'],
                                    'bank_txn_id'=>$post->data['PaymentTransactionId'],
                                    'status'=>'success'
                                ]);    

                       // \DB::table('upi_collects')->where('status','pending')->where('upi_txn_id',$post->data['id'])->update($upiInsertData);


                        //check service is enable or not
                        $isServiceActive = CommonHelper::checkIsServiceActive('upi_collect', $userId);

                        //check callback is enable or not
                        $isCallbackActive = CommonHelper::checkIsCallbackActive($userId, 'upi_stack', 'fpay');
                        //dd([$isServiceActive,$isCallbackActive]);
                        if ($isServiceActive && $isCallbackActive) {

                            $getWebhooks = DB::table('webhooks')
                                ->where('user_id', $userId)
                                ->first();
                            //dd($getWebhooks);
                            
                                $url = $getWebhooks->webhook_url;
                                $secret = $getWebhooks->secret;

                                if (isset($getWebhooks->header_key) && isset($getWebhooks->header_value)) {
                                   // dd("7878787887");
                                    $headers = [$getWebhooks->header_key => $getWebhooks->header_value];
                                    WebhookHelper::UPISuccess((object) $upiInsertData, $url, $secret, $headers);
                                } else {
                                   // dd("89897887");
                                    WebhookHelper::UPISuccess((object) $upiInsertData, $url, $secret);
                                }
                            
                        }

                        $res['status'] = 'SUCCESS';
                        $res['data'] = $upiInsertData;
                        $res['message'] = 'Request captured successfully for ' . $report->customer_ref_id;
                        $res['time'] = date('Y-m-d H:i:s');

                        return response()->json($res);
                    }
                }
               // dd($repport);
                $res = ['status' => 200, 'message' => 'Callback data accepected.'];
                 return json_encode($res);
            break;
            case 'huntoodpaypayout':
                \DB::table('callbacklogs')->insert(['response'=>json_encode($post->all())]);
                
                $report = \DB::table('upi_collects')->where('status','pending')->where('upi_txn_id',$post->data['ApiUserReferenceId'])->first();
                // dd($report);
                if($report){
                    if($post->data['TxnStatus'] == "SUCCESS") {
                        if($post->data['PayerAmount'] == $report->amount){
                            $products = CommonHelper::getProductId('upi_collect', 'upi_collect');
                            //fee and tax on fee calculation
                            $amount = !empty($post->data['PayerAmount']) ? $post->data['PayerAmount'] : 0;
                            $userId = isset($report->user_id) ? $report->user_id : '46';
                            $taxFee = (object) TransactionHelper::getFeesAndTaxes($products->product_id, $amount, $userId);

                            $feeRate = $taxFee->margin;
                            $fee = round($taxFee->fee, 2);
                            $tax = round($taxFee->tax, 2);
                            $crAmount = $amount - $fee - $tax;

                            //generate Batch ID for UPI callback transaction
                            $batchId = 'YBLUPI' . $userId . 'ST' . (date("YmdH") . '0' . (int)((date('i') / 30)));
                        
                            //insert new record
                            $upiInsertData = [
                                "npci_txn_id"=> $post->data['ApiUserReferenceId'],
                                "payer_vpa"=>$post->data['PayerVA'],
                                "amount"=>$amount,
                                "originalOrderId"=>$report->original_order_id,
                                "merchantTxnRefId"=>$report->merchant_txn_ref_id,
                                "customerRefId"=>$report->merchant_txn_ref_id,
                                "description"=>'Order Updated',
                                'bank_txn_id'=>$post->data['WalletTransactionId'],
                                "code"=>$post->status
                                ];
                                
                            \DB::table('upi_collects')
                                    ->where('status','pending')->where('upi_txn_id',$post->data['ApiUserReferenceId'])
                                    ->update([
                                        //"npci_txn_id"=> $post->data['upi_txn_id'],
                                        'payer_vpa' => $post->data['PayerVA'],
                                        'description' => 'Order Updated',
                                        'resp_code'=>$post->data['TxnStatus'],
                                        // 'bank_txn_id'=>$post->data['WalletTransactionId'],
                                        'status'=>'success'
                                    ]);    

                            //check service is enable or not
                            $isServiceActive = CommonHelper::checkIsServiceActive('upi_collect', $userId);

                            //check callback is enable or not
                            $isCallbackActive = CommonHelper::checkIsCallbackActive($userId, 'upi_stack', 'fpay');
                            if ($isServiceActive && $isCallbackActive) {

                                $getWebhooks = DB::table('webhooks')
                                    ->where('user_id', $userId)
                                    ->first();
                                
                                    $url = $getWebhooks->webhook_url;
                                    $secret = $getWebhooks->secret;

                                    if (isset($getWebhooks->header_key) && isset($getWebhooks->header_value)) {
                                    // dd("7878787887");
                                        $headers = [$getWebhooks->header_key => $getWebhooks->header_value];
                                        WebhookHelper::UPISuccess((object) $upiInsertData, $url, $secret, $headers);
                                    } else {
                                    // dd("89897887");
                                        WebhookHelper::UPISuccess((object) $upiInsertData, $url, $secret);
                                    }
                                
                            }
                            $res['status'] = 'SUCCESS';
                            $res['data'] = $upiInsertData;
                            $res['message'] = 'Request captured successfully for ' . $report->customer_ref_id;
                            $res['time'] = date('Y-m-d H:i:s');

                            return response()->json($res);
                        }
                    }
                
                }
                $res = ['status' => 200, 'message' => 'Callback data accepected.'];
                return json_encode($res);
                break;
            case 'ibrpayout':
                $res = ['status' => 200, 'message' => 'Callback data accepected.'];
                 return json_encode($res);
                break; 
            
            case 'indicpaypayout':
                 \DB::table('callbacklogs')->insert(['response'=>'indicpaypayout'.json_encode($post->all())]);
                $res = ['status' => 200, 'message' => 'Callback data accepected.'];
                 return json_encode($res);
                break;
                
            case 'indicpayupi':
                 \DB::table('callbacklogs')->insert(['response'=>'indicpayupi'.json_encode($post->all())]);
                $res = ['status' => 200, 'message' => 'Callback data accepected.'];
                 return json_encode($res);
                break;
            case 'aadharatm':
                    \DB::table('callbacklogs')->insert(['response'=>json_encode($post->all())]);
                    //dd($post);
                    $report = \DB::table('upi_collects')->where('status','pending')->where('upi_txn_id',$post->txnid)->first();
                    //dd($report);
                    if($report){
                        if($post->statuscode=="TXN" && $post->status=="success"){
                            
                                
                            $products = CommonHelper::getProductId('upi_collect', 'upi_collect');
    
                            //fee and tax on fee calculation
                            $amount = !empty($post->data['amount']) ? $post->data['amount'] : ($report->amount);
                            $userId = isset($report->user_id) ? $report->user_id : '46';
                            $taxFee = (object) TransactionHelper::getFeesAndTaxes($products->product_id, $amount, $userId);
    
                            $feeRate = $taxFee->margin;
                            $fee = round($taxFee->fee, 2);
                            $tax = round($taxFee->tax, 2);
                            $crAmount = $amount - $fee - $tax;
    
    
                            //generate Batch ID for UPI callback transaction
                            $batchId = 'YBLUPI' . $userId . 'ST' . (date("YmdH") . '0' . (int)((date('i') / 30)));
                           
                            //insert new record
                            $upiInsertData = [
                                "npci_txn_id"=> $post->txnid,
                                "payer_vpa"=>'',
                                "amount"=>$amount,
                                "originalOrderId"=>$report->original_order_id,
                                "merchantTxnRefId"=>$report->merchant_txn_ref_id,
                                "customerRefId"=>$report->customer_ref_id,
                                "description"=>'Order Updated',
                                'bank_txn_id'=>$post->utr,
                                "code"=>$post->status
                                ];
                                
                            \DB::table('upi_collects')
                                    ->where('status','pending')->where('upi_txn_id',$post->txnid)
                                    ->update([
                                        //"npci_txn_id"=> $post->data['upi_txn_id'],
                                        'payer_vpa' => '',
                                        'description' => 'Order Updated',
                                        'resp_code'=>$post->statuscode,
                                        'bank_txn_id'=>$post->utr,
                                        'status'=>'success'
                                    ]);    
    
                           // \DB::table('upi_collects')->where('status','pending')->where('upi_txn_id',$post->data['id'])->update($upiInsertData);
    
    
                            //check service is enable or not
                            $isServiceActive = CommonHelper::checkIsServiceActive('upi_collect', $userId);
    
                            //check callback is enable or not
                            $isCallbackActive = CommonHelper::checkIsCallbackActive($userId, 'upi_stack', 'fpay');
                            //dd([$isServiceActive,$isCallbackActive]);
                            if ($isServiceActive && $isCallbackActive) {
    
                                $getWebhooks = DB::table('webhooks')
                                    ->where('user_id', $userId)
                                    ->first();
                                //dd($getWebhooks);
                                
                                    $url = $getWebhooks->webhook_url;
                                    $secret = $getWebhooks->secret;
    
                                    if (isset($getWebhooks->header_key) && isset($getWebhooks->header_value)) {
                                       // dd("7878787887");
                                        $headers = [$getWebhooks->header_key => $getWebhooks->header_value];
                                        WebhookHelper::UPISuccess((object) $upiInsertData, $url, $secret, $headers);
                                    } else {
                                       // dd("89897887");
                                        WebhookHelper::UPISuccess((object) $upiInsertData, $url, $secret);
                                    }
                                
                            }
    
                            $res['status'] = 'SUCCESS';
                            $res['data'] = $upiInsertData;
                            $res['message'] = 'Request captured successfully for ' . $report->customer_ref_id;
                            $res['time'] = date('Y-m-d H:i:s');
    
                            return response()->json($res);
                        }
                    }
                   // dd($repport);
                    $res = ['status' => 200, 'message' => 'Callback data accepected.'];
                     return json_encode($res);
                    break;
            default:
                Storage::put('default' . time() . '.txt', print_r($post->all(), true));

                $res['status'] = 'FAILURE';
                $res['message'] = 'Unexpected response received';
                $res['time'] = date('Y-m-d H:i:s');

                return response()->json($res);
                break;
        }
        // } catch (Exception $e) {
        //     $res['status'] = 'FAILURE';
        //     $res['message'] = 'Error: ' . $e->getMessage();
        //     $res['time'] = date('Y-m-d H:i:s');

        //     return response()->json($res);
        // }
    }
}
