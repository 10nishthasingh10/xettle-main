<?php

namespace App\Console\Commands;

use App\Models\Order;
use App\Models\Webhook;
use Illuminate\Console\Command;
use App\Models\WebhookLog;
use Illuminate\Support\Facades\DB;

class WebhookRetry extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'webhook_retry:update';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Webhook retry';

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
        ->select('attribute_1', 'attribute_2', 'attribute_3', 'attribute_4')
        ->where(['slug' => 'webhook_retry'])
        ->first();
        $offset = 0;
        $limit = 50;
        $time = 1;
        $secret = "";
        if (isset($GlobalConfig)) {
            $offset = isset($GlobalConfig->attribute_1) ? $GlobalConfig->attribute_1 : 0;
            $limit = isset($GlobalConfig->attribute_2) ? $GlobalConfig->attribute_2 : 50;
            $time = isset($GlobalConfig->attribute_3) ? $GlobalConfig->attribute_3 : 1;
        }

        $webhooksLogs = WebhookLog::select('*')
            ->where('response', '!=', 'success')
            ->where('errorType',  'LIKE', "%GuzzleHttp%")
           // ->where('created_at', '<', Carbon::now()->subHours($time))
           // ->where('uuid', '90bbd2f9-4341-4e17-a64c-12f3be766f62')
            ->where('payload',  'LIKE', '%payout%')
            ->orderBy('webhook_logs.id', 'asc')
            ->groupBy('headers')
            ->offset($offset)
            ->limit($limit)
            ->get();

            $i = 0;
        foreach ($webhooksLogs as $webhooksLog)
        {
                $i++;
                $headers = json_decode($webhooksLog->headers, true);
                $url = json_decode($webhooksLog->webhookUrl);
                $arrayPayLoad = json_decode($webhooksLog->payload, true);
                $orderData = Order::select('user_id')->where('order_ref_id', $arrayPayLoad['data']['orderRefId'])->first();
                $getWebhooks = Webhook::where('user_id', $orderData->user_id)->first();
                $secret = $getWebhooks['secret'];
                $url = $getWebhooks['webhook_url'];
                if ($webhooksLog->headers) {
                    $data = \Spatie\WebhookServer\WebhookCall::create()
                        ->url($url)
                        ->payload($arrayPayLoad)
                        ->doNotSign()
                        ->withHeaders($headers)
                      //  ->timeoutInSeconds(5)
                      //  ->maximumTries(5)
                        ->dispatch();
                } else {
                    $data = \Spatie\WebhookServer\WebhookCall::create()
                        ->url($url)
                        ->payload($arrayPayLoad)
                        ->useSecret($secret)
                       // ->timeoutInSeconds(5)
                       // ->maximumTries(5)
                        ->dispatch();
                       // dd($data);
                }
        }

        if ($i == 0) {
            $message = "No record found";
        } else {
            $message =  $i." Records updated successfully.";
        }
        $this->info($message);
    }

}
