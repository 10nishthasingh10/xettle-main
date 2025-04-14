<?php

namespace App\Console\Commands;

use App\Helpers\InstantPayHelper;
use App\Http\Controllers\Clients\Api\v1\DMTController;
use Illuminate\Console\Command;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\Models\DMTFundTransfer;

class DMTStatusCheck extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dmt_transaction_status:update';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'DMT transaction status update';

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
        $GlobalConfig = DB::table('global_config')
            ->select('attribute_1', 'attribute_2', 'attribute_3', 'attribute_4', 'attribute_5')
            ->where(['slug' => 'dmt_transaction_update'])
            ->first();
        $offset = 0;
        $limit = 50;
        $time = 1;
        $userId = 0;
        $isEnable = 0;
        if (isset($GlobalConfig)) {
            $isEnable = isset($GlobalConfig->attribute_1) ? $GlobalConfig->attribute_1 : 0;
            $offset = isset($GlobalConfig->attribute_2) ? $GlobalConfig->attribute_2 : 50;
            $limit = isset($GlobalConfig->attribute_3) ? $GlobalConfig->attribute_3 : 30;
            $time = isset($GlobalConfig->attribute_4) ? $GlobalConfig->attribute_4 : 1;
            $hourseOrMinutes = isset($GlobalConfig->attribute_5) ? $GlobalConfig->attribute_5 : 1;
            $i = 0;

            if ($hourseOrMinutes == 1) {
                $times =  Carbon::now()->subHours($time);
            } else {
                $times =  Carbon::now()->subMinutes($time);
            }

            $txns = DB::table('dmt_fund_transfers')
                ->select('id', 'status', 'user_id', 'client_ref_id', 'order_ref_id', 'cron_date')
                ->where(['status' => 'processing'])
                ->where('created_at', '<', $times)
                ->offset($offset)
                ->limit($limit)
                ->get();

            if ($isEnable) {
                foreach ($txns as $order) {
                    $orderId = @$order->order_ref_id;
                    $userId = @$order->user_id;
                    if (isset($order->status) && $order->status == 'processing') {

                        $instantPay = new InstantPayHelper;

                        if (isset($order->cron_date) && !empty($order->cron_date)) {

                            $cronDate = date('Y-m-d', strtotime($order->cron_date));
                            $requestTransfer = $instantPay->instantpayTransferStatus($orderId, $cronDate, $order->user_id);
                            if (isset($requestTransfer['data']) && $requestTransfer['data'] != null) {

                                $errorDesc  = isset($requestTransfer['data']->data->transactionStatus) ? $requestTransfer['data']->data->transactionStatus : @$requestTransfer['data']->status;

                                $bank_reference = "";
                                $failedArray = ['IAN', 'FAB', 'TRP'];
                                if (
                                    isset($requestTransfer['data']->data) &&
                                    ($requestTransfer['data']->data->transactionStatusCode == 'TXN' && $requestTransfer['data']->data->transactionReferenceId != '00')
                                ) {

                                    $bank_reference  = isset($requestTransfer['data']->data->transactionReferenceId) ? $requestTransfer['data']->data->transactionReferenceId : "";
                                    DMTFundTransfer::updateRecord(
                                        ['user_id' => $userId, 'order_ref_id' => $orderId],
                                        ['status' => 'processed', 'utr' => $bank_reference]
                                    );

                                    DMTFundTransfer::cashbackCredit($userId, $orderId);
                                    DMTController::sendCallback($userId, $orderId, 'processed');
                                } else if (isset($requestTransfer['data']
                                    ->data->transactionStatusCode) && in_array($requestTransfer['data']
                                    ->data->transactionStatusCode, $failedArray)) {

                                    $statusCode = $requestTransfer['data']->data->transactionStatusCode;
                                    DMTFundTransfer::fundRefunded($userId, $orderId, @$errorDesc, 'dmt_fund_refunded', @$statusCode);
                                } else if (isset($requestTransfer['data']
                                    ->data->transactionStatusCode) && in_array($requestTransfer['data']
                                    ->data->transactionStatusCode, ['ERR']) && ($requestTransfer['data']
                                    ->data->transactionReferenceId == null || $requestTransfer['data']
                                    ->data->transactionReferenceId == '00')) {

                                    $statusCode = $requestTransfer['data']->data->transactionStatusCode;
                                    DMTFundTransfer::fundRefunded($userId, $orderId, @$errorDesc, 'dmt_fund_refunded', @$statusCode);
                                    DMTController::sendCallback($userId, $orderId, 'failed');
                                }
                            }
                        }
                    }

                    $i++;
                }
                if ($i == 0) {
                    $message = "No record found";
                } else {
                    $message =  $i . " Records updated successfully.";
                }
            } else {
                $message =  " DMT transaction status service not enable";
            }

            $this->info($message);
        }
    }
}
