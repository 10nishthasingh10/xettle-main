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
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;


class CallbackInstantpayOrderUpdate implements ShouldQueue
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
        $fileName = 'public/CallbackInstantpayOrderUpdate.txt';
        try {
            //Order Success
            if ('SUCCESS' == $this->event) {
                $callBackData = DB::table('payout_callbacks')
                    ->where('event', $this->event)
                    ->where('txnid', $this->orderRefId)
                    ->where('is_reflected', '0')
                    ->first();
                if ($callBackData) {
                    $responseJson = json_decode($callBackData->response, true);
                    $message = 'Order processed successfully.';
                    $statusCode = 200;
                    $bank_reference = $responseJson['opr_id'];
                    $Order = DB::table('orders')
                        ->where('order_ref_id', $this->orderRefId)
                        ->select('user_id','order_ref_id', 'batch_id')
                        ->first();
                    DB::select("CALL OrderStatusProcessedUpdate('".$this->orderRefId."', $Order->user_id, 'processed', '".$message."', '".$statusCode."', '".$bank_reference."', @json)");
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
            if ('REFUND' == $this->event) {
                $callBackData = DB::table('payout_callbacks')
                    ->where('event', $this->event)
                    ->where('txnid', $this->orderRefId)
                    ->where('is_refunded', '0')
                    ->first();
                if ($callBackData) {
                    $responseJson = json_decode($callBackData->response, true);
                    $Order = DB::table('orders')
                        ->where('order_ref_id', $this->orderRefId)
                        ->select('user_id', 'order_ref_id', 'batch_id')
                        ->first();
                    $errorDesc = isset($responseJson['res_msg']) ? $responseJson['res_msg'] : "Order failed";
                    $statusCode = isset($responseJson['res_code']) ? $responseJson['res_code'] : "";
                    $utr = '';
                    $getServicePkId = DB::table('user_services')->select('id')->where('user_id', $Order->user_id )->where('service_id', PAYOUT_SERVICE_ID)->first();
                    dispatch(new \App\Jobs\PayoutBalanceDebitAndStatusUpdateJob($Order->order_ref_id, $Order->user_id, 'failed_order', $getServicePkId->id, $errorDesc, $statusCode, $utr, 'failed'))
                    ->delay(rand(1, 5))
                    ->onQueue('payout_update_queue');

                }
            }

        } catch (\Exception  $e) {
            Storage::disk('local')->append($fileName, $e);
        }

    }

}