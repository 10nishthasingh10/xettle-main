<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Helpers\CommonHelper;
use App\Helpers\TransactionHelper;
use App\Models\BulkPayout;
use App\Models\BulkPayoutDetail;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;


class CallbackEasebuzzOrderJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     *
     * */

    private $orderRefId, $event, $userId;
    public function __construct($orderRefId, $event, $userId)
    {
       $this->orderRefId = $orderRefId;
       $this->event = $event;
       $this->userId = $userId;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        Storage::disk('local')->put('public/easeorderJobs.txt', 'callback order start');
            //Scheduled S0036 //Queued E0520
        try {
            //Order Success
            if ('success' == $this->event) {
                $callBackData = DB::table('eb_payout_callbacks')
                    ->where('event', $this->event)
                    ->where('txnid', $this->orderRefId)
                    ->where('is_reflected', '0')
                    ->first();
                if ($callBackData) {
                    $responseJson = json_decode($callBackData->response, true);
                    $message = 'Order processed successfully.';
                    $statusCode = 200;
                    $bank_reference = $responseJson['data']['unique_transaction_reference'];
                    $Order = DB::table('orders')
                        ->where('order_ref_id', $responseJson['data']['unique_request_number'])
                        ->select('user_id', 'order_ref_id', 'batch_id')
                        ->first();
                    DB::select("CALL OrderStatusProcessedUpdate('".$responseJson['data']['unique_request_number']."', $Order->user_id, 'processed', '".$message."', '".$statusCode."', '".$bank_reference."', @json)");
                    $results = DB::select('select @json as json');
                    $response = json_decode($results[0]->json, true);
                    if($response['status'] == '1') {
                        BulkPayoutDetail::payStatusUpdate($Order->batch_id, 'processed', $Order->order_ref_id, $message, $bank_reference);
                        BulkPayout::updateStatusByBatch($Order->batch_id, array('status' => 'processed'));
                        TransactionHelper::sendCallback($Order->user_id, $Order->order_ref_id, 'processed');
                    }
                }
            }

            // Order Failed
            if ('failure' == $this->event) {
                $callBackData = DB::table('eb_payout_callbacks')
                    ->where('event', $this->event)
                    ->where('txnid', $this->orderRefId)
                    ->where('is_refunded', '0')
                    ->first();
                if ($callBackData) {
                    $responseJson = json_decode($callBackData->response, true);
                    $Order = DB::table('orders')
                        ->where('order_ref_id', $responseJson['data']['unique_request_number'])
                        ->select('user_id', 'order_ref_id', 'batch_id')
                        ->first();
                    $errorDesc = isset($responseJson['data']['failure_reason']) ? $responseJson['data']['failure_reason'] : "Order failed";
                    $statusCode = '';
                    $referenceId = isset($responseJson['data']['unique_transaction_reference']) ? $responseJson['data']['unique_transaction_reference'] : " ";
                    $utr = isset($responseJson['utr']) ? $responseJson['utr'] : $referenceId;
                    $txn = CommonHelper::getRandomString('txn', false);
                    $getServicePkId = DB::table('user_services')
                        ->select('id')->where('user_id', $Order->user_id )
                        ->where('service_id', PAYOUT_SERVICE_ID)
                        ->first();
                    dispatch(new \App\Jobs\PayoutBalanceDebitAndStatusUpdateJob($Order->order_ref_id, $Order->user_id, 'failed_order',  $getServicePkId->id, $errorDesc, $statusCode, $utr, 'failed'))
                    ->onQueue('payout_update_queue');
                }
            }

              // Order Reversed
              if ('reversed' == $this->event) {
                $callBackData = DB::table('eb_payout_callbacks')
                    ->where('event', $this->event)
                    ->where('txnid', $this->orderRefId)
                    ->where('is_reversed', '0')
                    ->first();
                if ($callBackData) {
                    $responseJson = json_decode($callBackData->response, true);
                    $Order = DB::table('orders')
                        ->where('order_ref_id', $responseJson['data']['unique_request_number'])
                        ->select('user_id', 'order_ref_id', 'batch_id')
                        ->first();
                    $userConfig = DB::table('user_config')
                        ->select('id')
                        ->where(['user_id' => $Order->user_id, 'is_payout_reversal' => '1'])
                        ->first();
                    if (isset($userConfig) && !empty($userConfig)) {
                        $errorDesc = isset($responseJson['data']['failure_reason']) ? $responseJson['data']['failure_reason'] : "Order reversed";
                        $statusCode = '';
                        $referenceId = isset($responseJson['data']['unique_transaction_reference']) ? $responseJson['data']['unique_transaction_reference'] : " ";
                        $idReference = isset($responseJson['data']['id']) ? $responseJson['data']['id'] : " ";
                        $userServices = DB::table('user_services')
                            ->select('id')
                            ->where(['service_id' => PAYOUT_SERVICE_ID, 'user_id' => $Order->user_id])
                            ->first();
                        $utr = isset($referenceId) ? $referenceId : $idReference;
                        dispatch(new \App\Jobs\PayoutBalanceDebitAndStatusUpdateJob($Order->order_ref_id, $Order->user_id, 'failed_order',  $getServicePkId->id, $errorDesc, $statusCode, $utr, 'reversed'))
                        ->onQueue('payout_update_queue');
                    }
                }
            }

               //code...
        } catch (\Exception  $e) {
            Storage::disk('local')->append('public/easeorderJobs.txt', $e);
        }

    }


    public function middleware()
    {
        $time = [5, 10, 15, 20];
        return [(new WithoutOverlapping($this->userId))->releaseAfter($time[rand(0, 3)])];
    }
}