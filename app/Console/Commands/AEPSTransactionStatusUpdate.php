<?php

namespace App\Console\Commands;

use App\Helpers\WebhookHelper;
use App\Jobs\PrimaryFundCredit;
use Illuminate\Console\Command;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Clients\Api\v1\AEPSController;
use App\Models\AepsTransaction;
use App\Models\Webhook;

class AEPSTransactionStatusUpdate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'aeps_transaction_status:update';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'AEPS transaction status update';

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
        ->where(['slug' => 'aeps_transaction_update'])
        ->first();
        $offset = 0;
        $limit = 50;
        $time = 1;
        $userId = 0;
        $isEnable = 0;
        $routeArray = [];
        if (isset($GlobalConfig)) {
            $isEnable = isset($GlobalConfig->attribute_1) ? $GlobalConfig->attribute_1 : 0;
            $offset = isset($GlobalConfig->attribute_2) ? $GlobalConfig->attribute_2 : 50;
            $limit = isset($GlobalConfig->attribute_3) ? $GlobalConfig->attribute_3 : 1;
            $time = isset($GlobalConfig->attribute_4) ? $GlobalConfig->attribute_4 : 30;
            if (isset($GlobalConfig->attribute_5)) {
                $routeArray = explode(',', $GlobalConfig->attribute_5);
            }
        }
        $i = 0;
        
        $txns = DB::table('aeps_transactions')
            ->select('id', 'status', 'user_id', 'client_ref_id')
            ->where(['status' => 'pending'])
            ->whereIn('transaction_type' , ['cw', 'ap'])
            ->whereIn('route_type', $routeArray)
            ->where('created_at', '<', Carbon::now()->subMinutes($time))
            ->get();

    if ($isEnable) {
        foreach ($txns as $txn)
        {
            $params = [
                'refernceno'  => $txn->client_ref_id
            ];
            $obj = new AEPSController;
            $response = $obj->APICaller($params, 'statuscheck', $txn->user_id);
            if (isset($response) && $response['statuscode'] === "000") {
                AepsTransaction::where(['user_id' => $txn->user_id,'client_ref_id' => $txn->client_ref_id])
                    ->update([  'status' => 'success',
                                'resp_stan_no' => @$response['data']['stanno'],
                                'rrn' => @$response['data']['rrn'],
                                'failed_message' => ''
                            ]);
                     self::sendAEPSCallabck((object) $response, $txn->user_id);
            } else if (isset($response) &&  $response['statuscode'] === "001") {
                $message = $response['statuscode'] . ': ' . $response['message'];
                AepsTransaction::where(['user_id' => $txn->user_id,'client_ref_id' => $txn->client_ref_id])
                    ->update([  'status' => 'failed',
                                'failed_message' => @$message,
                                'resp_stan_no' => @$response['data']['stanno'],
                                'rrn' => @$response['data']['rrn']
                            ]);
                 self::sendAEPSCallabck((object) $response, $txn->user_id);
            }
            $i ++;
        }
        if ($i == 0) {
            $message = "No record found";
        } else {
            $message =  $i." Records updated successfully.";
        }
    } else {
        $message =  " AEPS transaction status service not enable";
    }

        $this->info($message);
    }

    public static function sendAEPSCallabck($data, $userId)
    {
       //send callback
       $getWebhooks = Webhook::where('user_id', $userId)->first();
       if ($getWebhooks) {
           $url = $getWebhooks['webhook_url'];
           $secret = $getWebhooks['secret'];
           if (isset($getWebhooks['header_key']) && isset($getWebhooks['header_value'])) {
               $headers = [$getWebhooks['header_key'] => $getWebhooks['header_value']];
                WebhookHelper::AEPSTransaction($data, $url, $secret, $headers);
           } else {

                WebhookHelper::AEPSTransaction($data, $url, $secret);
           }
       }

    }
}
