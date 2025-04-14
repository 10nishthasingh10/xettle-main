<?php

namespace App\Jobs;

use App\Helpers\BankopenHelper;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\Order;
use App\Helpers\CashfreeHelper;
use App\Helpers\CommonHelper;
use App\Helpers\EaseBuzzHelper;
use App\Helpers\InstantPayHelper;
use App\Helpers\RazorpayHelper;
use App\Helpers\SafeXPayHelper;
use App\Helpers\TransactionHelper;
use App\Models\BulkPayout;
use App\Models\BulkPayoutDetail;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ProcessingOrderUpdateJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;


    /**
     * Create a new job instance.
     *
     *
     * */
  /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 350;

    /**
     * The number of seconds the job can run before timing out.
     *
     * @var int
     */
    public $timeout = 14400;

    /**
     * Indicate if the job should be marked as failed on timeout.
     *
     * @var bool
     */
    public $failOnTimeout = true;

    /**
     * Create a new job instance.
     *
     *
     * */
    private $orderRefId, $userId, $fileName;
    public function __construct($orderRefId, $userId)
    {
       $this->orderRefId = $orderRefId;
       $this->userId = $userId;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {

        try {
            $fileName = 'easeOrderProcessingJobs'.$this->orderRefId.'.txt';
           // Storage::disk('local')->put($fileName, 'processing order : '.$this->orderRefId);
            $order = Order::select('*')->where('status', 'processing')
                ->where('orders.cron_status', '1')
                //->where('orders.area', '11')
                ->where('orders.order_id', '=', NULL)
                ->where('orders.user_id', $this->userId)
                ->where('orders.order_ref_id', $this->orderRefId)
                ->first();
                if(isset($order)) {
                  //  Storage::disk('local')->append($fileName, ' order data : '.$order->integration_id.' '.$order->area);

                    $route = DB::table('integrations')
                        ->select('slug')
                        ->where('integration_id', $order->integration_id)
                        ->first();
                    $types = isset($route->slug) ? $route->slug : "NA";

                  //  Storage::disk('local')->append($fileName, 'type : '.$types);
                    switch ($types)
                    {
                        case 'cashfree':
                            $successArray = array('200');
                            $failedArray = array('404');
                            $Cashfree = new CashfreeHelper;
                            $requestTransfer = $Cashfree->getDirectTransferStatus($order->order_ref_id);
                            if (isset($requestTransfer['data'])) {
                                if (isset($requestTransfer['data']
                                ->subCode))
                                {
                                    Storage::disk('local')->append($fileName, 'cashfree status : '.$requestTransfer['data']->message);
                                    $errorDesc = $requestTransfer['data']->message;
                                    $bank_reference = "";
                                    $status = "pending";
                                    if (in_array($requestTransfer['data']->subCode, $successArray) && $requestTransfer['data']->data->transfer->status == 'SUCCESS') {
                                        $message = $requestTransfer['data']->message;
                                        $statusCode = $requestTransfer['data']->subCode;
                                        $status = "processed";
                                        $bank_reference  = isset($requestTransfer['data']->data->utr) ? $requestTransfer['data']->data->utr : "";
                                        DB::select("CALL OrderStatusProcessedUpdate('".$order->order_ref_id."', $order->user_id, 'processed', '".$message."', '".$statusCode."', '".$bank_reference."', @json)");
                                        $results = DB::select('select @json as json');
                                        $response = json_decode($results[0]->json, true);
                                        if($response['status'] == '1' && $order->area == '11') {
                                            TransactionHelper::sendCallback($order->user_id, $order->order_ref_id, 'processed');
                                        }
                                    }else if(in_array($requestTransfer['data']->subCode, $successArray) && $requestTransfer['data']->data->transfer->status == 'FAILED') {
                                        $status = "failed";
                                        $errorDesc = isset($requestTransfer['data']->data->transfer->reason) ? $requestTransfer['data']->data->transfer->reason : $errorDesc;
                                        $statusCode = $requestTransfer['data']->subCode;
                                        $txn = CommonHelper::getRandomString('txn', false);
                                        $utr = $bank_reference = isset($requestTransfer['data']->data->transfer->referenceId) ? $requestTransfer['data']->data->transfer->referenceId : " ";
                                        $getServicePkId = DB::table('user_services')->select('id')->where('user_id', $order->user_id )->where('service_id', PAYOUT_SERVICE_ID)->first();
                                        DB::select("CALL OrderStatusUpdate('".$order->order_ref_id."', $order->user_id, $getServicePkId->id, 'failed', '".$txn."', '".$errorDesc."', '".$statusCode."','".$utr."', @json)");
                                        $results = DB::select('select @json as json');
                                        $response = json_decode($results[0]->json, true);
                                        if($response['status'] == '1' && $order->area == '11') {
                                            TransactionHelper::sendCallback($order->user_id, $order->order_ref_id, 'failed');
                                        }
                                    } elseif (in_array($requestTransfer['data']->subCode, $failedArray)) {
                                        $status = "failed";
                                        $errorDesc = isset($requestTransfer['data']->data->transfer->reason) ? $requestTransfer['data']->data->transfer->reason : $errorDesc;
                                        $statusCode = $requestTransfer['data']->subCode;
                                        $txn = CommonHelper::getRandomString('txn', false);
                                        $utr = $bank_reference = isset($requestTransfer['data']->data->transfer->referenceId) ? $requestTransfer['data']->data->transfer->referenceId : " ";
                                        $getServicePkId = DB::table('user_services')->select('id')->where('user_id', $order->user_id )->where('service_id', PAYOUT_SERVICE_ID)->first();
                                        DB::select("CALL OrderStatusUpdate('".$order->order_ref_id."', $order->user_id, $getServicePkId->id, 'failed', '".$txn."', '".$errorDesc."', '".$statusCode."','".$utr."', @json)");
                                        $results = DB::select('select @json as json');
                                        $response = json_decode($results[0]->json, true);
                                        if($response['status'] == '1' && $order->area == '11') {
                                            TransactionHelper::sendCallback($order->user_id, $order->order_ref_id, 'failed');
                                        }
                                    }
                                    if ($order->area == '00') {
                                        BulkPayoutDetail::payStatusUpdate($order->batch_id, $status, $order->order_ref_id, $errorDesc, $bank_reference);
                                        BulkPayout::updateStatusByBatch($order->batch_id, array('status' => 'processed'));
                                    }
                                }
                            }
                        break;
                        case 'bankopen':
                            $successArray = array('15', '103', '4');
                            $pendingArray = array('350');
                            $failedArray = array('37', '353', '343', '10');
                            $Cashfree = new BankopenHelper;
                            $requestTransfer = $Cashfree->bankopenStatus($order->order_ref_id, $order->user_id);
                            if (isset($requestTransfer['data']) && $requestTransfer['data'] != null) {
                                if (isset($requestTransfer['data']
                                    ->data->transaction_status_id)) {

                                    $errorDesc = isset($requestTransfer['data']->data->bank_error_message) ? $requestTransfer['data']->data->bank_error_message : "";
                                    if (in_array($requestTransfer['data']->data->transaction_status_id, $successArray)) {
                                        $message = $requestTransfer['data']->data->bank_error_message;
                                        $statusCode = $requestTransfer['data']->data->transaction_status_id;
                                        $bank_reference = isset($requestTransfer['data']->data->open_transaction_ref_id) ? $requestTransfer['data']->data->open_transaction_ref_id : "";
                                        DB::select("CALL OrderStatusProcessedUpdate('" . $order->order_ref_id . "', $order->user_id, 'processed', '" . $message . "', '" . $statusCode . "', '" . $bank_reference . "', @json)");
                                        $results = DB::select('select @json as json');
                                        $response = json_decode($results[0]->json, true);
                                        if ($response['status'] == '1') {
                                            TransactionHelper::sendCallback($order->user_id, $order->order_ref_id, 'processed');
                                        }
                                    }
                                    if (in_array($requestTransfer['data']->data->transaction_status_id, $failedArray)) {

                                        $errorDesc = isset($requestTransfer['data']->data->bank_error_message) ? $requestTransfer['data']->data->bank_error_message : $errorDesc;
                                        $statusCode = $requestTransfer['data']->data->transaction_status_id;
                                        if (str_contains($errorDesc, 'Not enough available')) {
                                            $errorDesc = 'Something went wrong, please try after some time';
                                        }
                                        if (!str_contains($requestTransfer['data']->data->bank_error_message, 'Internal server error')) {

                                            $utr = isset($requestTransfer['data']->data->open_transaction_ref_id) ? $requestTransfer['data']->data->open_transaction_ref_id : " ";

                                            if ($requestTransfer['data']->data->transaction_status_id == 10) {
                                                $orderStatus = "reversed";
                                            } else {
                                                $orderStatus = "failed";
                                            }
                                            $getServicePkId = DB::table('user_services')
                                                ->select('id')->where('user_id', $order->user_id)->where('service_id', PAYOUT_SERVICE_ID)->first();
                                            dispatch(new \App\Jobs\PayoutBalanceDebitAndStatusUpdateJob($order->order_ref_id, $order->user_id, 'failed_order', $getServicePkId->id, $errorDesc, $statusCode, $utr, $orderStatus))
                                            ->onQueue('payout_update_queue');
                                        }
                                    }
                                }
                            }
                        break;
                        case 'safexpay':

                                    $successArray = array('0000', 'S0026', 'S0033', 'S0035');
                                    $pendingArray = array('0001', 'E0499', 'E0513');
                                    $failedArray = array('0002', '00002', 'B', 'E0005', 'E0010', 'E0011', 'E0021', 'E0027', 'E0030', 'E0039', 'E0046', 'E0049', 'E0055', 'E0058', 'E0092', 'E0151', 'E0152', 'E0153', 'E0154', 'E0155', 'E0156', 'E0158', 'E0160', 'E0161', 'E0165', 'E0187', 'E0197', 'E0198', 'E0211', 'E0212', 'E0213', 'E0214', 'E0221', 'E0237', 'E0249', 'E0279', 'E0359', 'E0389', 'E0404', 'E0405', 'E0406', 'E0407', 'E0409', 'E0429', 'E0435', 'E0452', 'E0478', 'E0480', 'E0492', 'E0494', 'E0495', 'E0497', 'E0498', 'E0506', 'E0510', 'E0511', 'E0521', 'E0522', 'E0523', 'E0530', 'E0531', 'E0532', 'E0541', 'E0507', 'E0542', 'E0543', 'E0544', 'E0545', 'E0546', 'E0547', 'E0553', 'E0552', 'E0554', 'E0558', 'F', 'IP002', 'L0032', 'M', 'N', 'O');
                                    $safeXPay = new SafeXPayHelper;
                                    $checkStatus = $safeXPay->payoutStatusCheckByOrderRefId($order->order_ref_id, $order->user_id);
                                    $response = json_decode($checkStatus);
                                    $status = 'pending';
                                    $bank_reference = '';
                                    $errorDesc = $response->response->description;
                                    if (in_array($response->response->code, $successArray)  && $response->payOutBean->statusCode) {
                                        Storage::disk('local')->append($fileName, 'safex status : '.$response->payOutBean->statusCode);
                                        $statusCode = isset($response->payOutBean->statusCode) ? $response->payOutBean->statusCode : $response->response->code;
                                        if (in_array($statusCode, $successArray)) {
                                            $status = 'processed';
                                            $statusCode = isset($response->payOutBean->statusCode) ? $response->payOutBean->statusCode : $response->response->code;
                                            $errorDesc = $message = isset($response->payOutBean->statusDesc) ? $response->payOutBean->statusDesc : $errorDesc;
                                            $bank_reference = isset($response->payOutBean->bankRefNo) ? $response->payOutBean->bankRefNo : "";
                                            DB::select("CALL OrderStatusProcessedUpdate('".$order->order_ref_id."', $order->user_id, 'processed', '".$message."', '".$statusCode."', '".$bank_reference."', @json)");
                                            $results = DB::select('select @json as json');
                                            $response = json_decode($results[0]->json, true);
                                            if($response['status'] == '1' && $order->area == '11') {
                                                TransactionHelper::sendCallback($order->user_id, $order->order_ref_id, 'processed');
                                            }
                                        } else if (in_array($statusCode, $pendingArray)) {
                                            Order::where('order_ref_id', $order->order_ref_id)
                                            ->update([
                                                'status' => 'processing',
                                                'txt_1' => isset($response->payOutBean->spkRefNo) ? $response->payOutBean->spkRefNo : "",
                                                'cron_status' => '1',
                                                'status_code' => $statusCode,
                                                'status_response' => $response->response->description,
                                                'payout_id' => $response->payOutBean->payoutId
                                            ]);
                                        } else if (in_array($statusCode, $failedArray)) {
                                            $status = 'failed';
                                            $errorDesc = isset($response->payOutBean->statusDesc) ? $response->payOutBean->statusDesc : $errorDesc;
                                            $txn = CommonHelper::getRandomString('txn', false);
                                            $utr = $bank_reference = isset($response->payOutBean->bankRefNo) ? $response->payOutBean->bankRefNo : "";
                                            $getServicePkId = DB::table('user_services')->select('id')->where('user_id', $order->user_id )->where('service_id', PAYOUT_SERVICE_ID)->first();
                                            DB::select("CALL OrderStatusUpdate('".$order->order_ref_id."', $order->user_id, $getServicePkId->id, 'failed', '".$txn."', '".$errorDesc."', '".$statusCode."','".$utr."', @json)");
                                            $results = DB::select('select @json as json');
                                            $response = json_decode($results[0]->json, true);
                                            if($response['status'] == '1' && $order->area == '11') {
                                                TransactionHelper::sendCallback($order->user_id, $order->order_ref_id, 'failed');
                                            }

                                        }
                                    } else if (in_array($response->response->code, $pendingArray)) {
                                        Order::where('order_ref_id', $order->order_ref_id)
                                        ->update([
                                            'status' => 'processing',
                                            'txt_1' => isset($response->payOutBean->spkRefNo) ? $response->payOutBean->spkRefNo : "",
                                            'cron_status' => '1',
                                            'status_code' => isset($response->payOutBean->statusCode) ? $response->payOutBean->statusCode : $response->response->code,
                                            'status_response' => $response->response->description,
                                            'payout_id' => $response->payOutBean->payoutId
                                        ]);
                                    } else if (in_array($response->response->code, $failedArray)) {
                                        $status = 'failed';
                                        $statusCode = isset($response->payOutBean->statusCode) ? $response->payOutBean->statusCode : $response->response->code;
                                        $errorDesc = isset($response->payOutBean->statusDesc) ? $response->payOutBean->statusDesc : $errorDesc;
                                        $txn = CommonHelper::getRandomString('txn', false);
                                        $utr = $bank_reference = isset($response->payOutBean->bankRefNo) ? $response->payOutBean->bankRefNo : "";
                                        $getServicePkId = DB::table('user_services')->select('id')->where('user_id', $order->user_id )->where('service_id', PAYOUT_SERVICE_ID)->first();
                                        DB::select("CALL OrderStatusUpdate('".$order->order_ref_id."', $order->user_id, $getServicePkId->id, 'failed', '".$txn."', '".$errorDesc."', '".$statusCode."','".$utr."', @json)");
                                        $results = DB::select('select @json as json');
                                        $response = json_decode($results[0]->json, true);
                                        if($response['status'] == '1' && $order->area == '11') {
                                            TransactionHelper::sendCallback($order->user_id, $order->order_ref_id, 'failed');
                                        }
                                    }
                                    if ($order->area == '00') {
                                        BulkPayoutDetail::payStatusUpdate($order->batch_id, $status, $order->order_ref_id, $errorDesc, $bank_reference);
                                        BulkPayout::updateStatusByBatch($order->batch_id, array('status' => 'processed'));
                                    }
                        break;

                        case 'easebuzz':
                            $easeBuzz = new EaseBuzzHelper;
                            $requestTransfer = $easeBuzz->quickTransferStatus($order->order_ref_id, $order->user_id);
                            if (isset($requestTransfer['data'])) {
                                if (isset($requestTransfer['data']
                                ->success))
                                {
                                    $errorDesc = isset($requestTransfer['data']->message) ? $requestTransfer['data']->message : $requestTransfer['data']
                                    ->success;
                                 //   Storage::disk('local')->append($fileName, 'easebuzz status : '.$errorDesc);
                                    $bank_reference = "";
                                    $status = "pending";
                                    if ($requestTransfer['data']->success == true && $requestTransfer['data']->data->transfer_request->status == 'success') {
                                        $message = $requestTransfer['data']->success;
                                        $statusCode = $requestTransfer['data']->success;
                                        $status = "processed";
                                        $bank_reference  = isset($requestTransfer['data']->data->transfer_request->unique_transaction_reference) ? $requestTransfer['data']->data->transfer_request->unique_transaction_reference : "";
                                        DB::select("CALL OrderStatusProcessedUpdate('".$order->order_ref_id."', $order->user_id, 'processed', '".$message."', '".$statusCode."', '".$bank_reference."', @json)");
                                        $results = DB::select('select @json as json');
                                        $response = json_decode($results[0]->json, true);
                                        if($response['status'] == '1' && $order->area == '11') {
                                            TransactionHelper::sendCallback($order->user_id, $order->order_ref_id, 'processed');
                                        }
                                    }else if($requestTransfer['data']->success == true && ( $requestTransfer['data']->data->transfer_request->status == 'failure' ||  $requestTransfer['data']->data->transfer_request->status == 'rejected')) {
                                        $status = "failed";
                                        $errorDesc = isset($requestTransfer['data']->data->transfer_request->failure_reason) ? $requestTransfer['data']->data->transfer_request->failure_reason : $errorDesc;
                                        $statusCode = $requestTransfer['data']->success;
                                        $txn = CommonHelper::getRandomString('txn', false);
                                        $utr = $bank_reference = isset($requestTransfer['data']->data->transfer->referenceId) ? $requestTransfer['data']->data->transfer->referenceId : " ";
                                        $getServicePkId = DB::table('user_services')->select('id')->where('user_id', $order->user_id )->where('service_id', PAYOUT_SERVICE_ID)->first();
                                        DB::select("CALL OrderStatusUpdate('".$order->order_ref_id."', $order->user_id, $getServicePkId->id, 'failed', '".$txn."', '".$errorDesc."', '".$statusCode."','".$utr."', @json)");
                                        $results = DB::select('select @json as json');
                                        $response = json_decode($results[0]->json, true);
                                        if($response['status'] == '1' && $order->area == '11') {
                                            TransactionHelper::sendCallback($order->user_id, $order->order_ref_id, 'failed');
                                        }
                                    } elseif ($requestTransfer['data']->success == false) {
                                        $status = "failed";
                                        $statusCode = $requestTransfer['data']->success;
                                        $txn = CommonHelper::getRandomString('txn', false);
                                        $utr = $bank_reference = " ";
                                        $getServicePkId = DB::table('user_services')->select('id')->where('user_id', $order->user_id )->where('service_id', PAYOUT_SERVICE_ID)->first();
                                        DB::select("CALL OrderStatusUpdate('".$order->order_ref_id."', $order->user_id, $getServicePkId->id, 'failed', '".$txn."', '".$errorDesc."', '".$statusCode."','".$utr."', @json)");
                                        $results = DB::select('select @json as json');
                                        $response = json_decode($results[0]->json, true);
                                        if($response['status'] == '1' && $order->area == '11') {
                                            TransactionHelper::sendCallback($order->user_id, $order->order_ref_id, 'failed');
                                        }
                                    }
                                    if ($order->area == '00') {
                                        BulkPayoutDetail::payStatusUpdate($order->batch_id, $status, $order->order_ref_id, $errorDesc, $bank_reference);
                                        BulkPayout::updateStatusByBatch($order->batch_id, array('status' => 'processed'));
                                    }
                                }
                            }
                        break;
                        case 'instantpay':
                            $instantPay = new InstantPayHelper;
                            if (isset($order->cron_date) && !empty($order->cron_date)) {
                                $requestTransfer = $instantPay->instantpayTransferStatus($order->order_ref_id, $order->cron_date, $order->user_id);
                                if (isset($requestTransfer['data']) && $requestTransfer['data'] != null) {
                                    $errorDesc = isset($requestTransfer['data']->status) ? $requestTransfer['data']->status : "";
                                //   Storage::disk('local')->append($fileName, 'easebuzz status : '.$errorDesc);
                                    $bank_reference = "";
                                    $status = "pending";

                                    $failedArray =['IAN', 'FAB', 'TRP'];
                                    if (isset($requestTransfer['data']->data) &&
                                                ($requestTransfer['data']->data->transactionStatusCode == 'TXN' && $requestTransfer['data']->data->transactionReferenceId != '00'))
                                            {
                                            $message = @$requestTransfer['data']->data->transactionStatus;
                                            $statusCode = 200;
                                            $status = "processed";
                                            $bank_reference  = isset($requestTransfer['data']->data->transactionReferenceId) ? $requestTransfer['data']->data->transactionReferenceId : "";
                                            DB::select("CALL OrderStatusProcessedUpdate('".$order->order_ref_id."', $order->user_id, 'processed', '".$message."', '".$statusCode."', '".$bank_reference."', @json)");
                                            $results = DB::select('select @json as json');
                                            $response = json_decode($results[0]->json, true);
                                            if($response['status'] == '1' && $order->area == '11') {
                                                TransactionHelper::sendCallback($order->user_id, $order->order_ref_id, 'processed');
                                            }
                                        }else if(isset($requestTransfer['data']
                                        ->data->transactionStatusCode) && in_array($requestTransfer['data']
                                        ->data->transactionStatusCode, $failedArray)) {
                                            $status = "failed";
                                            $errorDesc = isset($requestTransfer['data']->status) ? $requestTransfer['data']->status : $errorDesc;
                                            $statusCode = $requestTransfer['data']->statuscode;
                                            $txn = CommonHelper::getRandomString('txn', false);
                                            $utr = $bank_reference = isset($requestTransfer['data']->data->transactionReferenceId) ? $requestTransfer['data']->data->transactionReferenceId : " ";
                                            $getServicePkId = DB::table('user_services')->select('id')->where('user_id', $order->user_id )->where('service_id', PAYOUT_SERVICE_ID)->first();
                                            DB::select("CALL OrderStatusUpdate('".$order->order_ref_id."', $order->user_id, $getServicePkId->id, 'failed', '".$txn."', '".$errorDesc."', '".$statusCode."','".$utr."', @json)");
                                            $results = DB::select('select @json as json');
                                            $response = json_decode($results[0]->json, true);
                                            if($response['status'] == '1' && $order->area == '11') {
                                                TransactionHelper::sendCallback($order->user_id, $order->order_ref_id, 'failed');
                                            }
                                        } else if(isset($requestTransfer['data']
                                                ->data->transactionStatusCode) && in_array($requestTransfer['data']
                                                ->data->transactionStatusCode, ['ERR']) && ($requestTransfer['data']
                                                ->data->transactionReferenceId == null || $requestTransfer['data']
                                                ->data->transactionReferenceId == '00')) {
                                                    $status = "failed";
                                                    $errorDesc = isset($requestTransfer['data']->data->transactionStatus) ? $requestTransfer['data']->data->transactionStatus : $errorDesc;
                                                    $statusCode = $requestTransfer['data']->data->transactionStatusCode;
                                                    $txn = CommonHelper::getRandomString('txn', false);
                                                    $utr = $bank_reference = isset($requestTransfer['data']->data->transactionReferenceId) ? $requestTransfer['data']->data->transactionReferenceId : " ";
                                                    $getServicePkId = DB::table('user_services')->select('id')->where('user_id', $order->user_id )->where('service_id', PAYOUT_SERVICE_ID)->first();
                                                    DB::select("CALL OrderStatusUpdate('".$order->order_ref_id."', $order->user_id, $getServicePkId->id, 'failed', '".$txn."', '".$errorDesc."', '".$statusCode."','".$utr."', @json)");
                                                    $results = DB::select('select @json as json');
                                                    $response = json_decode($results[0]->json, true);
                                                    if($response['status'] == '1' && $order->area == '11') {
                                                        TransactionHelper::sendCallback($order->user_id, $order->order_ref_id, 'failed');
                                                    }
                                                }
                                        if ($order->area == '00') {
                                            BulkPayoutDetail::payStatusUpdate($order->batch_id, $status, $order->order_ref_id, $errorDesc, $bank_reference);
                                            BulkPayout::updateStatusByBatch($order->batch_id, array('status' => 'processed'));
                                        }
                                }
                            }
                        break;
                        case 'razorpay':
                            $razorpay = new RazorpayHelper;
                            if (isset($order->payout_id) && !empty($order->payout_id)) {
                                $requestTransfer = $razorpay->razorpayGetPayoutById($order->payout_id);
                                if (isset($requestTransfer['data'])) {
                                    $successArray = array('processed');
                                    $pendingArray = array('pending', 'processing');
                                    $failedArray = array('rejected', 'cancelled', 'failed', 'failure');
                                    $reversedArray = array('reversed');
                                    if (isset($requestTransfer['data']
                                    ->status))
                                    {
                                        $errorDesc = isset($requestTransfer['data']->failure_reason) ? $requestTransfer['data']->failure_reason : $requestTransfer['data']
                                        ->status;
                                        $bank_reference = isset($requestTransfer['data']->utr) ? $requestTransfer['data']->utr : "";
                                        $status = "pending";
                                        if (in_array($requestTransfer['data']
                                            ->status, $successArray)) {
                                            $message = $requestTransfer['data']->status;
                                            $statusCode = $requestTransfer['data']->status;
                                            $status = "processed";
                                            DB::select("CALL OrderStatusProcessedUpdate('".$order->order_ref_id."', $order->user_id, 'processed', '".$message."', '".$statusCode."', '".$bank_reference."', @json)");
                                            $results = DB::select('select @json as json');
                                            $response = json_decode($results[0]->json, true);
                                            if($response['status'] == '1' && $order->area == '11') {
                                                TransactionHelper::sendCallback($order->user_id, $order->order_ref_id, 'processed');
                                            }
                                        } else if (in_array($requestTransfer['data']
                                        ->status, $failedArray)) {
                                            $status = "failed";
                                            $statusCode = $requestTransfer['data']->status;
                                            $txn = CommonHelper::getRandomString('txn', false);
                                            $utr = $bank_reference = isset($requestTransfer['data']->utr) ? $requestTransfer['data']->utr : " ";
                                            $getServicePkId = DB::table('user_services')->select('id')->where('user_id', $order->user_id )->where('service_id', PAYOUT_SERVICE_ID)->first();
                                            DB::select("CALL OrderStatusUpdate('".$order->order_ref_id."', $order->user_id, $getServicePkId->id, 'failed', '".$txn."', '".$errorDesc."', '".$statusCode."','".$utr."', @json)");
                                            $results = DB::select('select @json as json');
                                            $response = json_decode($results[0]->json, true);
                                            if($response['status'] == '1' && $order->area == '11') {
                                                TransactionHelper::sendCallback($order->user_id, $order->order_ref_id, 'failed');
                                            }
                                        } else if (in_array($requestTransfer['data']
                                        ->status, $reversedArray)) {
                                            $status = "failed";
                                            $statusCode = $requestTransfer['data']->status;
                                            $txn = CommonHelper::getRandomString('txn', false);
                                            $utr = $bank_reference = isset($requestTransfer['data']->utr) ? $requestTransfer['data']->utr : " ";
                                            $getServicePkId = DB::table('user_services')->select('id')->where('user_id', $order->user_id )->where('service_id', PAYOUT_SERVICE_ID)->first();
                                            DB::select("CALL OrderStatusUpdate('".$order->order_ref_id."', $order->user_id, $getServicePkId->id, 'failed', '".$txn."', '".$errorDesc."', '".$statusCode."','".$utr."', @json)");
                                            $results = DB::select('select @json as json');
                                            $response = json_decode($results[0]->json, true);
                                            if($response['status'] == '1' && $order->area == '11') {
                                                TransactionHelper::sendCallback($order->user_id, $order->order_ref_id, 'failed');
                                            }
                                        }

                                        if ($order->area == '00') {
                                            BulkPayoutDetail::payStatusUpdate($order->batch_id, $status, $order->order_ref_id, $errorDesc, $bank_reference);
                                            BulkPayout::updateStatusByBatch($order->batch_id, array('status' => 'processed'));
                                        }
                                    }
                                }
                            } else {
                                $message = "payout id not be null";
                            }

                        break;
                }
            }
               //code...
        } catch (\Exception  $e) {
            $fileName = 'public/OrderProcessingJobs'.$this->orderRefId.'.txt';
            Storage::disk('local')->put('errorlog_'.$fileName, $e.date('H:i:s'));
        }

    }

    public function middleware()
    {
        //$time = [5];
        return [(new WithoutOverlapping($this->userId))->releaseAfter(rand(1, 30))];
    }

    public function retryUntil()
    {
        return now()->addHours(8);
    }
}