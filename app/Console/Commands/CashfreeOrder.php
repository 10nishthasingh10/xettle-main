<?php

namespace App\Console\Commands;

use App\Helpers\CashfreeHelper;
use App\Helpers\CommonHelper;
use App\Helpers\PaytmHelper;
use App\Helpers\SafeXPayHelper;
use App\Helpers\TransactionHelper;
use Illuminate\Console\Command;
use App\Models\Contact;
use App\Models\Transaction;
use App\Models\Integration;
use App\Models\Order;
use App\Models\PortalSetting;
use App\Models\BulkPayoutDetail;
use App\Models\Product;
use App\Models\UserService;
use App\Models\GlobalConfig;
use App\Models\User;
use App\Models\BulkPayout;
use App\Models\Webhook;
use App\Helpers\WebhookHelper;
use DateTime;

class CashfreeOrder extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cashfree:update';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Cashfree Update payout orders';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $messages = '';
        $orderCount = 0;
        $log = [];
        $date = new DateTime;
        $date->modify('-30 seconds');
        $formatted_date = $date->format('Y-m-d H:i:s');
            $orders = Order::where('orders.status', 'processing')
            ->where('orders.cron_status', '1')
            ->where('orders.area', '00')
            ->where('orders.updated_at', '<=', $formatted_date)
            ->orderBy('orders.id', 'asc')
            ->limit(100)
            ->get();

        foreach ($orders as $order)
        {
            $orderCount++;
            $route = CommonHelper::routeNameByIntegrationId($order->integration_id, $order->area);
            $types = $route['slug'];

           if ($route['slug'] == 'cashfree') {
                $successArray = array('200');
                $pendingArray = array('201');
                $failedArray = array('403', '412', '404', '520');

                $Cashfree = new CashfreeHelper;
                $requestTransfer = $Cashfree->getTransferStatus($order->order_ref_id);
                $orderStatus = "processing";


                if (isset($requestTransfer['data'])) {
                    $errorDesc = "";
                    $bank_reference = "";
                    if ($requestTransfer['data']->status == 'SUCCESS')
                    {
                        if(isset($requestTransfer['data']->data->transfers[0]->failureReason) && !empty($requestTransfer['data']->data->transfers[0]->failureReason)) {
                            $status = $orderStatus = 'failed';
                            $errorDesc = $requestTransfer['data']->data->transfers[0]->failureReason;
                        } else {
                            $status = $orderStatus = isset($requestTransfer['data']->data->transfers[0]->status) ? strtolower($requestTransfer['data']->data->transfers[0]->status) : 'processing';
                        }
                        $message = $requestTransfer['data']->message;
                        $totalAmount = ($order->amount + $order->fee + $order->tax);
                        $bank_reference = isset($requestTransfer['data']->data->transfers[0]->utr) ? $requestTransfer['data']->data->transfers[0]->utr : null ;
                        if (in_array($status, array('failed','FAILED'))) {
                            $orderStatus = 'failed';
                            $errorDesc = isset($requestTransfer['data']->data->transfer->reason) ? $requestTransfer['data']->data->transfer->reason : $errorDesc;
                            Order::where('order_ref_id', $order->order_ref_id)->update([
                                'status' => 'failed',
                                'status_response' => $message,
                                'failed_message' => $errorDesc,
                                'bank_reference' => $bank_reference,
                            ]);
                            TransactionHelper::refundLockedAmount($order->order_id, $order->user_id, $order->service_id, $totalAmount);
                         //send callback
                         TransactionHelper::sendCallback ($order->user_id,  $order->order_ref_id, 'failed');
                         // end
                        } else if (in_array($status, array('success','SUCCESS'))) {
                            Order::where('order_id', $order->order_id)->where('order_ref_id', $order->order_ref_id)->update([
                                'status' => 'processed',
                                'status_response' => $message,
                                'bank_reference' => $bank_reference,
                            ]);
                            $orderStatus = 'success';
                            TransactionHelper::insertTransaction($order->order_id, $order->order_ref_id);
                            TransactionHelper::sendCallback ($order->user_id,  $order->order_ref_id, 'processed');
                   
                        } else if (in_array($status, array('reversed','REVERSED'))) {
                           /* $order = Order::where('order_ref_id', $order->order_ref_id)->first();
                            if ($order->status == 'processed') {
                                TransactionHelper::reverseTrn($order->order_id, $order->order_ref_id);
                            } else if($order->status == 'processing'){
                                TransactionHelper::insertTransaction($order->order_id, $order->order_ref_id);
                                TransactionHelper::reverseTrn($order->order_id, $order->order_ref_id);
                            }
                            $errorDesc = $requestTransfer['data']->data->transfer->reason;
                            $orderStatus = 'reversed';
                            Order::where('order_id', $order->order_id)->where('order_ref_id', $order->order_ref_id)->update([
                                'status' => 'reversed',
                                'status_response' => $message,
                                'bank_reference' => $bank_reference,
                            ]);
                            */
                        }
                       // BulkPayoutDetail::payStatusUpdate($order->batch_id, $orderStatus, $order->order_ref_id, $errorDesc, $bank_reference);
                       // BulkPayout::updateStatusByBatch($order->batch_id, array('status'=>'processed')); 
                    } else if($requestTransfer['data']->status == 'ERROR' && $requestTransfer['data']->subCode == '404'){
                        $totalAmount = ($order->amount + $order->fee + $order->tax);
                        Order::where('order_id', $order->order_id)->where('order_ref_id', $order->order_ref_id)->update([
                            'status' => 'cancelled',
                            'status_code' => 404,
                            'cancelled_at' => date('Y-m-d H:i:s'),
                            'cancellation_reason' => 'Order failed unexpectedly',
                        ]);
                        BulkPayoutDetail::payStatusUpdate($order->batch_id, 'cancelled', $order->order_ref_id, 'Order failed unexpectedly', '');
                        BulkPayout::updateStatusByBatch($order->batch_id, array('status'=>'processed'));
                        TransactionHelper::refundLockedAmount($order->order_id, $order->user_id, $order->service_id, $totalAmount);
                    } else {
                        BulkPayoutDetail::payStatusUpdate($order->batch_id, $orderStatus, $order->order_ref_id, $errorDesc, $bank_reference);
                        BulkPayout::updateStatusByBatch($order->batch_id, array('status'=>'processed'));
                    }
                }
            }
        }
        if ($orderCount == 0) {
            $message = ' No records found.';
        } else {
            $message = $orderCount;
        }
        $this->info($message);
    }

    public static function orderUpdate($order_ref_id, $ord_id, $status = '', $statusCode = '', $txnStatus = '', $txnDesc = '', $spkReferenceNo = '',  $bankReference = '')
    {
        $response['status'] = false;
        $response['message'] = "No record update";
        $orderStatus = Order::select('status')->where('order_id', $ord_id)->where('order_ref_id', $order_ref_id)->first();
        if (!empty($status) && $orderStatus->status == 'processing') {
            if ($status == 'processed') {
                $orderStatusCount = Order::select('status')->where('status', 'processed')
                    ->where('order_id', $ord_id)->where('order_ref_id', $order_ref_id)->count();
                if ($orderStatusCount == 0) {
                    Order::where('order_id', $ord_id)->where('order_ref_id', $order_ref_id)->update(array('status' => $status, 'status_code' => $txnStatus, 'status_response' => $txnDesc, 'txt_1' => $spkReferenceNo, 'bank_reference' => $bankReference));
                    $response['status'] = true;
                    $response['message'] = "Record updated Successfull";
                } else {
                    $response['status'] = false;
                    $response['message'] = "No record update";
                }
            } else if ($status == 'pending') {
                $orderStatusCount = Order::select('status')->where('status', 'processing')
                    ->where('order_id', $ord_id)->where('order_ref_id', $order_ref_id)->count();
                if ($orderStatusCount == 0) {
                    Order::where('order_id', $ord_id)->where('order_ref_id', $order_ref_id)->update(array('status' => $status, 'status_code' => $txnStatus, 'status_response' => $txnDesc, 'txt_1' => $spkReferenceNo, 'bank_reference' => $bankReference));
                    $response['status'] = true;
                    $response['message'] = "Record updated Successfull";
                } else {
                    $response['status'] = false;
                    $response['message'] = "No record update";
                }
            } else if ($status == 'failed') {
                $orderStatusCount = Order::select('status')->where('status', 'failed')->where('order_ref_id', $order_ref_id)->where('order_id', $ord_id)->count();
                if ($orderStatusCount == 0) {
                    Order::where('order_id', $ord_id)->where('order_ref_id', $order_ref_id)->update(array('status' => $status, 'failed_status_code' => $statusCode, 'failed_message' => $txnDesc, 'status_code' => null, 'status_response' => null, 'txt_1' => $spkReferenceNo, 'bank_reference' => $bankReference));
                    $response['status'] = true;
                    $response['message'] = "Record updated Successfull";
                } else {
                    $response['status'] = false;
                    $response['message'] = "No record update";
                }
            }
        } else {
            $response['status'] = false;
            $response['message'] = "No record update";
        }

        return $response;
    }


}
