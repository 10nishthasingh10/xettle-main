<?php

namespace App\Console\Commands;

use App\Helpers\WebhookHelper;
use App\Jobs\PrimaryFundCredit;
use Illuminate\Console\Command;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Clients\Api\v1\UPIController;
use App\Models\UPICollect;
use App\Models\Webhook;

class UpiCollectStatusUpdate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'upi_transaction_status:update';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'UPI transaction status update';

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
        ->select('attribute_1', 'attribute_2', 'attribute_3', 'attribute_5')
        ->where(['slug' => 'upi_transaction_update'])
        ->first();
        $offset = 0;
        $limit = 50;
        $time = 1;
        $userId = 0;
        $isEnable = 0;
        $routeArray = [];
        if (isset($GlobalConfig)) {
            $isEnable = isset($GlobalConfig->attribute_1) ? $GlobalConfig->attribute_1 : 0;
            $offset = isset($GlobalConfig->attribute_2) ? $GlobalConfig->attribute_2 : 1;
            $limit = isset($GlobalConfig->attribute_3) ? $GlobalConfig->attribute_3 : 5;
            $time = isset($GlobalConfig->attribute_4) ? $GlobalConfig->attribute_4 : 30;
            if (isset($GlobalConfig->attribute_5)) {
                $routeArray = explode(',', $GlobalConfig->attribute_5);
            }
        }
        $i = 0;
        $txns = DB::table('upi_collects')
            ->select('id', 'status', 'user_id','amount', 'customer_ref_id','upi_txn_id')
            ->where(['status' => 'pending'])
            ->where('created_at', '<', Carbon::now()->subMinutes($time))
            ->limit($limit)
            ->orderBy('id','desc')
            ->get();
        if ($isEnable) {
            foreach ($txns as $txn)
            {
                $params = [
                    "APIID"=> "API1006",
                    "Token"=> "fe06f96f-95f5-4a36-8d7f-0de71bff8b54",
                    "amount"=> $txn->amount,
                    "OrderKeyId"=> $txn->upi_txn_id,
                    "MethodName"=> "CheckStatus"
                ];
                $obj = new UPIController;
                $response = $obj->UPICaller($params, 'statuscheck', $txn->user_id,'UPI_status_check','UPIstatuscheck');
                if (isset($response) && $response['status'] === "success" && !empty($response['data']['OrderStatus']) && $response['data']['OrderStatus'] == 1) {
                    UPICollect::where(['user_id' => $txn->user_id,'customer_ref_id' => $txn->customer_ref_id,'upi_txn_id' => $response['data']['OrderKeyId']])
                    ->update([  'status' => 'success',
                                'failed_message' => ''
                            ]);
                    //self::sendUPICallabck((object) $response, $txn->user_id);
                }else if (isset($response) &&  $response['status'] === "failed") {
                    UPICollect::where(['user_id' => $txn->user_id,'customer_ref_id' => $txn->customer_ref_id])
                    ->update([  'status' => 'failed',
                                'failed_message' => ''
                            ]);
                }
                $i ++;
            }
            if ($i == 0) {
                $message = "No record found";
            } else {
                $message =  $i." Records updated successfully.";
            }
        }
        else {
            $message =  " AEPS transaction status service not enable";
        }
    
            $this->info($message);
    }
}