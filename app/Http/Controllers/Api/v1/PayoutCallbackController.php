<?php

namespace App\Http\Controllers\Api\v1;

use App\Helpers\EasebuzzInstaCollectHelper;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Apilog;
use App\Models\BulkPayout;
use App\Models\BulkPayoutDetail;
use App\Models\Order;
use App\Helpers\TransactionHelper;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class PayoutCallbackController extends Controller
{
    public function callback(Request $post, $api)
    {
        $res = [];
        switch ($api) {
            case 'cipherpay':
                 $timestamp = date('Y-m-d H:i:s');
                    $inData = [
                        'txnid' => $post['transferId'] ?? "cipherpayout",
                        'event' => "cipher:".$post->header('Key'),
                        'response' => json_encode($post->all()),
                        'created_at' => $timestamp,
                        'updated_at' => $timestamp
                    ];
                    DB::table('cphr_payout_callbacks')->insert($inData);
                    $res['status'] = 'SUCCESS';
                    $res['message'] = "Getting $api callabck data.";
                break;
                
            case 'cashfree':

                $data = $post->all();
                if (isset($data['transferId'])) {
                    $filename = 'callbackOrder_' . $data['transferId'] . '.txt';
                    //Storage::disk('local')->put($filename, 'callback order start : ');
                    $timestamp = date('Y-m-d H:i:s');

                    $Order = DB::table('orders')
                        ->where('order_ref_id', $data['transferId'])
                        ->select('user_id', 'status')
                        ->first();
                        
                    $ordersDataStatus = isset($Order->status) ? $Order->status : " ";
                    //Storage::disk('local')->append($filename, 'callback order status error : '.$data['transferId'].' '.$data['event'].' '.$ordersDataStatus);
                    $time = Carbon::now()->addSeconds(3);
                    $dispatchstatus = false;
                    if (($Order->status == 'processing' && ($data['event'] == 'TRANSFER_SUCCESS' || $data['event'] == 'TRANSFER_FAILED')) || ($Order->status == 'processed' && $data['event'] == 'TRANSFER_REVERSED')) {
                        $dispatchstatus = true;
                    }
                    $inData = [
                        'txnid' => isset($data['transferId']) ? $data['transferId'] : "",
                        'event' => isset($data['event']) ? $data['event'] : "",
                        'response' => json_encode($data),
                        'created_at' => $timestamp,
                        'updated_at' => $timestamp
                    ];
                    DB::table('cf_payout_callbacks')->insert($inData);
                    $res['status'] = 'SUCCESS';
                    $res['message'] = "$api callback data accepted successfully.";

                    if ($dispatchstatus) {
                        $userId = DB::table('orders')
                            ->select('user_id')
                            ->where('order_ref_id', $data['transferId'])->first();
                        //  $checkCallbackOrderDispatch = dispatch(new \App\Jobs\CallbackOrderUpdateJob($data['transferId'], $data['event']))->onQueue('payout_queue')->delay($time);
                        $checkCallbackOrderDispatch = dispatch(new \App\Jobs\CallbackOrderUpdateJob($data['transferId'], $data['event'], $userId->user_id))->onQueue('payout_queue');
                     
                        $res['status'] = 'SUCCESS';
                        $res['message'] = "Order status updated using $api callback.";
                    } else {
                        $res['status'] = 'FAILURE';
                        $res['message'] = "Duplicate $api callback data.";
                    }
                } else {
                    $res['status'] = 'FAILURE';
                    $res['message'] = "Invalid $api callabck data.";
                }
                break;
            case 'razorpay':

                    $data = $post->all();
                    $signature = self::razorpayVerifyCallback($post->header(), $post->all());
                    if (isset($data['payload']['payout']['entity']['status']) && $signature) {
                        $filename = 'razcallbackOrder_' . $data['payload']['payout']['entity']['reference_id'] . '.txt';
                        Storage::disk('local')->put($filename, 'callback order start : ');
                        $timestamp = date('Y-m-d H:i:s');
                        //Storage::put($filename, print_r($post->all(), true).' \n '.print_r($post->header(), true));

                        $Order = DB::table('orders')
                            ->where('order_ref_id', $data['payload']['payout']['entity']['reference_id'])
                            ->select('user_id', 'status')
                            ->first();

                        $dispatchstatus = false;
                        $eventStatus = isset($data['payload']['payout']['entity']['status']) ? $data['payload']['payout']['entity']['status'] : "";
                        if (($Order->status == 'processing' && ($data['payload']['payout']['entity']['status'] == 'processed' || $data['payload']['payout']['entity']['status'] == 'rejected' || $data['payload']['payout']['entity']['status'] == 'cancelled')) || ($Order->status == 'processed' && $data['payload']['payout']['entity']['status'] == 'reversed')) {
                            $dispatchstatus = true;
                        }
                        if (($Order->status == 'processing' && $data['payload']['payout']['entity']['status'] == 'reversed')) {
                            $dispatchstatus = true;
                            $eventStatus = 'failed';
                        }
                        $inData = [
                            'txnid' => isset($data['payload']['payout']['entity']['reference_id']) ? $data['payload']['payout']['entity']['reference_id'] : "",
                            'event' => $eventStatus,
                            'response' => json_encode($data),
                            'created_at' => $timestamp,
                            'updated_at' => $timestamp
                        ];
                        DB::table('rp_payout_callbacks')->insert($inData);
                        $res['status'] = 'SUCCESS';
                        $res['message'] = "$api callback data accepted successfully.";
                        if ($dispatchstatus) {
                            $userId = DB::table('orders')
                                ->select('user_id')
                                ->where('order_ref_id', $data['payload']['payout']['entity']['reference_id'])->first();
                            //  $checkCallbackOrderDispatch = dispatch(new \App\Jobs\CallbackOrderUpdateJob($data['transferId'], $data['event']))->onQueue('payout_queue')->delay($time);
                            $checkCallbackOrderDispatch = dispatch(new \App\Jobs\CallbackRazorpayOrderUpdate($data['payload']['payout']['entity']['reference_id'], $eventStatus, $userId->user_id))->onQueue('payout_queue');
                            $res['status'] = 'SUCCESS';
                            $res['message'] = "Order status updated using $api callback.";
                        } else {
                            $res['status'] = 'FAILURE';
                            $res['message'] = "Duplicate $api callback data.";
                        }
                    } else {
                        $res['status'] = 'FAILURE';
                        $res['message'] = "Invalid $api callabck data.";
                    }
                    break;

                case 'ipay':

                    $data = $post->all();
                    if (isset($data['agent_id']) && isset($data['ipay_id'])) {
                            $Order = DB::table('orders')
                                ->where('order_ref_id', $data['agent_id'])
                                ->select('user_id', 'status')
                                ->first();
                                $eventStatus = isset($data['status']) ? $data['status'] : "";
                                $timestamp = date('Y-m-d H:i:s');
                                $inData = [
                                    'txnid' => $data['agent_id'],
                                    'event' => $eventStatus,
                                    'response' => json_encode($data),
                                    'created_at' => $timestamp,
                                    'updated_at' => $timestamp
                                ];
                                DB::table('payout_callbacks')->insert($inData);
                            $dispatchstatus = false;
                            if (isset($Order) && ($Order->status == 'processing' && ($eventStatus == 'SUCCESS' || $eventStatus == 'REFUND')) ) {
                                $dispatchstatus = true;
                            }
                            $res['ipay_id'] = @$data['ipay_id'];
                           // $res['status'] = 'SUCCESS';
                            $res['success'] = true;
                            $res['description'] = "$api callback data accepted successfully.";
                            if ($dispatchstatus) {
                                $userId = DB::table('orders')
                                    ->select('user_id')
                                    ->where('order_ref_id', $data['agent_id'])->first();
                                $checkCallbackOrderDispatch = dispatch(new \App\Jobs\CallbackInstantpayOrderUpdate($data['agent_id'], $eventStatus, $userId->user_id))->onQueue('payout_queue');
                                $res['status'] = 'SUCCESS';
                                $res['description'] = "Order status updated using $api callback.";
                            } else {
                                $fileName = 'public/ip_duplicate_callback.txt';
                                Storage::append($fileName, print_r($post->all(), true).' \n '.print_r($post->header(), true));
                                $res['ipay_id'] = @$data['ipay_id'];
                              //  $res['status'] = 'FAILURE';
                                $res['success'] = false;
                                $res['description'] = "Duplicate $api callback data.";
                            }
                        } else {
                            $fileName = 'public/ip_callback.txt';
                            Storage::append($fileName, print_r($post->all(), true).' \n '.print_r($post->header(), true));
                            $res['ipay_id'] = @$data['ipay_id'];
                           // $res['status'] = 'FAILURE';
                            $res['success'] = true;
                            $res['description'] = "Invalid $api callback data.";
                        }
                        break;

            case 'easebuzz':
                $data = $post->all();

                if (!empty($data['event'])) {

                    //when event is insta collect van callback
                    if ($data['event'] === 'TRANSACTION_CREDIT') {

                        $utr = @$data['data']['unique_transaction_reference'];
                        $accountId = @$data['data']['virtual_account']['id'];

                        $apilogId = DB::table('apilogs')->insertGetId([
                            'modal' => 'EasebuzzInstaCollect',
                            'txnid' => $utr,
                            'method' => 'Callback',
                            'header' => 'NA',
                            'request' => 'NA',
                            'call_back_response' => json_encode($data),
                            'created_at' => date('Y-m-d H:i:s'),
                            'updated_at' => date('Y-m-d H:i:s')
                        ]);

                        $virtualAccount = DB::table('user_van_accounts')
                            ->select('*')
                            ->where('root_type', 'eb_van')
                            ->where('account_id', $accountId)
                            ->first();

                        if (!empty($virtualAccount)) {
                            return EasebuzzInstaCollectHelper::handleVanCallbackCredit($virtualAccount, $data['data']);
                        }

                        $res['status'] = 'FAILURE';
                        $res['message'] = 'Invalid Virtual Account callback received';
                        $res['time'] = date('Y-m-d H:i:s');

                        DB::table('apilogs')->where('id', $apilogId)
                            ->update(['resp_message' => json_encode($res)]);

                        return response()->json($res);
                    } else if ($data['event'] === 'INSTA_COLLECT_VIRTUAL_ACCOUNT_KYC_APPROVAL') {
                        $apilogId = DB::table('apilogs')->insertGetId([
                            'modal' => 'EasebuzzInstaCollect',
                            'txnid' => 'KYC_STATUS',
                            'method' => 'Callback',
                            'header' => 'NA',
                            'request' => 'NA',
                            'call_back_response' => json_encode($data),
                            'created_at' => date('Y-m-d H:i:s'),
                            'updated_at' => date('Y-m-d H:i:s')
                        ]);

                        $callback['logId'] = $apilogId;
                        $callback['data'] = $data['data'];

                        return EasebuzzInstaCollectHelper::handleVanKycStatusCallback($callback);
                    } else {
                        if (isset($data['data']['unique_request_number']) && !empty($data['data']['unique_request_number'])) {
                            $timestamp = date('Y-m-d H:i:s');
                  
                            $Order = DB::table('orders')
                                ->where('order_ref_id', $data['data']['unique_request_number'])
                                ->select('user_id', 'status')
                                ->first();
    
                            $time = Carbon::now()->addSeconds(3);
                            $dispatchstatus = false;
                            
                            if(!empty($Order)){
                                $ordersDataStatus = isset($Order->status) ? $Order->status : " ";
                                //Storage::disk('local')->append($filename, 'callback order status error : '.$data['transferId'].' '.$data['event'].' '.$ordersDataStatus);
                                
                                if (($Order->status == 'processing' && ($data['data']['status'] == 'success' || $data['data']['status'] == 'failure')) || ($Order->status == 'processed' && $data['data']['status'] == 'reversed')) {
                                    $dispatchstatus = true;
                                }
                            }
                            
                            $inData = [
                                'txnid' => isset($data['data']['unique_request_number']) ? $data['data']['unique_request_number'] : "",
                                'event_name' => isset($data['event']) ? $data['event'] : "",
                                'event' => isset($data['data']['status']) ? $data['data']['status'] : "",
                                'response' => json_encode($data),
                                'created_at' => $timestamp,
                                'updated_at' => $timestamp
                            ];
                            DB::table('eb_payout_callbacks')->insert($inData);
                            $res['status'] = 'SUCCESS';
                            $res['message'] = "$api callback data accepted successfully.";
                            if ($dispatchstatus) {
                                $userId = DB::table('orders')
                                    ->select('user_id')
                                    ->where('order_ref_id', $data['data']['unique_request_number'])->first();
                                $checkCallbackOrderDispatch = dispatch(new \App\Jobs\CallbackEasebuzzOrderJob($data['data']['unique_request_number'], $data['data']['status'], $userId->user_id))->onQueue('payout_queue');
                                $res['status'] = 'SUCCESS';
                                $res['message'] = "Order status updated using $api callback.";
                            } else {
                                $res['status'] = 'FAILURE';
                                $res['message'] = "Duplicate $api callback data.";
                            }
                        } else {
                            $res['status'] = 'FAILURE';
                            $res['message'] = "Invalid $api callback data.";
                        }
                    }
                } else {

                    DB::table('apilogs')->insertGetId([
                        'modal' => 'EasebuzzInstaCollect',
                        'txnid' => 'Easebuzz',
                        'method' => 'Callback',
                        'header' => 'NA',
                        'request' => 'NA',
                        'call_back_response' => json_encode($data),
                        'created_at' => date('Y-m-d H:i:s'),
                        'updated_at' => date('Y-m-d H:i:s')
                    ]);
                    
                    $res['status'] = 'FAILURE';
                    $res['message'] = 'Event is empty';
                    $res['time'] = date('Y-m-d H:i:s');
                    return response()->json($res);
                }
                break;
            case 'huntoodpaypayout':
                $data = $post->all();
                if (!empty($data['data']['APITransactionId'])) {
                    $filename = 'callbackOrder_' . $data['data']['APITransactionId'] . '.txt';
                    //Storage::disk('local')->put($filename, 'callback order start : ');
                    $timestamp = date('Y-m-d H:i:s');

                    $Order = DB::table('orders')
                        ->where('order_ref_id', $data['data']['APITransactionId'])
                        ->select('user_id', 'status')
                        ->first();
                    $ordersDataStatus = isset($Order->status) ? $Order->status : " ";
                    // Storage::disk('local')->append($filename, 'callback order status error : '.$data['transferId'].' '.$data['event'].' '.$ordersDataStatus);
                    
                    $time = Carbon::now()->addSeconds(3);
                    $dispatchstatus = false;
                    $data['event'] = "success"; //failed, refund
                    if (($Order->status == 'processing' && ($data['event'] == 'success' || $data['event'] == 'failed')) || ($Order->status == 'processed' && $data['event'] == 'refund') || ($Order->status == 'hold' && ($data['event'] == 'success' || $data['event'] == 'failed' || $data['event'] == 'refund'))) {
                        $dispatchstatus = true;
                    }
                    
                    $inData = [
                        'txnid' => isset($data['data']['APITransactionId']) ? $data['data']['APITransactionId'] : "",
                        'event' => isset($data['event']) ? $data['event'] : "",
                        'response' => json_encode($data),
                        'created_at' => $timestamp,
                        'updated_at' => $timestamp
                    ];
                    DB::table('cf_payout_callbacks')->insert($inData);
                    $res['status'] = 'SUCCESS';
                    $res['message'] = "$api callback data accepted successfully.";
                    
                    if ($dispatchstatus) {
                        $userId = DB::table('orders')
                            ->select('user_id')
                            ->where('order_ref_id', $data['data']['APITransactionId'])->first();
                            
                        //  $checkCallbackOrderDispatch = dispatch(new \App\Jobs\CallbackOrderUpdateJob($data['transferId'], $data['event']))->onQueue('payout_queue')->delay($time);
                        $checkCallbackOrderDispatch = dispatch(new \App\Jobs\HuntoodCallbackJob($data['data']['APITransactionId'], $data['event'], $userId->user_id))->onQueue('payout_queue');

                        $res['status'] = 'SUCCESS';
                        $res['message'] = "Order status updated using $api callback.";
                    } else {
                        $res['status'] = 'FAILURE';
                        $res['message'] = "Duplicate $api callback data.";
                    }
                } else {
                    $res['status'] = 'FAILURE';
                    $res['message'] = "Invalid $api callabck data.";
                }
                break;

            default:

                $res['status'] = 'FAILURE';
                $res['message'] = 'Invalid Root';
                $res['time'] = date('Y-m-d H:i:s');
                return response()->json($res);
                break;
        }
        return response()->json($res);
    }

    public  function reversedOrder()
    {
        $apilogs =  Apilog::where('is_reversed', '0')->where('event', 'TRANSFER_REVERSED')->get();

        foreach ($apilogs as $apilog) {
            $apilogFirst =  Apilog::where('id', $apilog->id)->first();
            $order = Order::where('order_ref_id', $apilog->txnid)->first();
            $callBackResp = json_decode($apilog->call_back_response, true);
            if ($order->status == 'processed') {
                TransactionHelper::reverseTrn($order->order_ref_id);
            }
            $errorDesc = $callBackResp['reason'];
            $orderStatus = 'reversed';
            Order::where('order_id', $order->order_id)->where('order_ref_id', $order->order_ref_id)->update([
                'status' => 'reversed',
                'bank_reference' => $callBackResp['referenceId'],
            ]);
            BulkPayoutDetail::payStatusUpdate($order->batch_id, $orderStatus, $order->order_ref_id, $errorDesc, $callBackResp['referenceId']);
            BulkPayout::updateStatusByBatch($order->batch_id, array('status' => 'processed'));
            $apilogFirst->is_reversed = '1';
            $apilogFirst->save();
        }
    }

    public static function razorpayVerifyCallback($header, $payload)
    {
        if (isset($header['x-razorpay-signature'][0]) && count($payload)) {
            $key = base64_decode(env('RAZPAY_PAYOUT_WEBHOOK_SECRET'));
            $signature = $header['x-razorpay-signature'][0];
            $message = json_encode($payload);

            $generateSignature = hash_hmac('sha256', $message, $key);

            if ($signature == $generateSignature) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }
}
