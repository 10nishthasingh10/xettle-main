<?php

namespace App\Console\Commands;

use App\Models\MatmTransaction;
use Illuminate\Console\Command;
use App\Services\Matm\MATMService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class MATMStatusCheck extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'matm_processing_order:update';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'MATM Processing payout orders Update';

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
    public function handle( MATMService $service)
    {
        $GlobalConfig = DB::table('global_config')
            ->select('attribute_1', 'attribute_2', 'attribute_3', 'attribute_4')
            ->where(['slug' => 'processing_order_count'])
            ->first();
            $offset = 0;
            $limit = 50;
            $time = 30;
            $hourseOrMinutes = 1;
            if (isset($GlobalConfig)) {
                $offset = isset($GlobalConfig->attribute_1) ? $GlobalConfig->attribute_1 : 0;
                $limit = isset($GlobalConfig->attribute_2) ? $GlobalConfig->attribute_2 : 50;
                $time = isset($GlobalConfig->attribute_3) ? $GlobalConfig->attribute_3 : 30;
                $hourseOrMinutes = isset($GlobalConfig->attribute_4) ? $GlobalConfig->attribute_4 : 1;
            }
            if ($hourseOrMinutes == 1) {
                $times =  Carbon::now()->subHours($time);
            } else {
                $times =  Carbon::now()->subMinutes($time);
            }
            $orders = MatmTransaction::where('status', 'pending')
            ->select('order_ref_id', 'user_id')
            ->where('created_at', '<',  $times)
            ->orderBy('id', 'asc')
            ->offset($offset)
            ->limit($limit)
            ->get();
            $i = 0;
        foreach ($orders as $order)
        {
            $i++;
                    $body = [
                        "refernceno" =>  $order->order_ref_id
                    ];

                    $response = $service->init($body, '/statuscheck/getmatmstatus', 'statusCheck', $order->user_id, 'yes', 'matm');

                    if (isset($response['response']['response']->statuscode)) {
                        if ($response['response']['response']->statuscode == "000") {
                            MatmTransaction::updateRecord(
                                ['user_id' => $order->user_id, 'order_ref_id' => @$order->order_ref_id, 'status' => 'pending'],
                                [
                                    'status' => 'processed',
                                    'stanno' => @$response['response']['response']->stanno,
                                    'rrnno' => @$response['response']['response']->rrnno,
                                    'cardno' => @$response['response']['response']->cardno,
                                    'card_type' => @$response['response']['response']->cardtype,
                                    'bank_ref_no' => @$response['response']['response']->bankrefno,
                                ]
                            );

                        } else if ($response['response']['response']->statuscode == "001") {

                            $failedmessage = isset($response['response']['response']->bankmessage) ? $response['response']['response']->bankmessage : $response['response']['response']->message;
                            MatmTransaction::updateRecord(
                                ['user_id' => @$order->user_id, 'order_ref_id' =>  @$order->order_ref_id, 'status' => 'pending'],
                                [
                                    'status' => 'failed',
                                    'stanno' => @$response['response']['response']->stanno,
                                    'failed_message' =>  @$failedmessage,
                                    'rrnno' => @$response['response']['response']->rrnno,
                                    'cardno' => @$response['response']['response']->cardno,
                                    'card_type' => @$response['response']['response']->cardtype,
                                    'bank_ref_no' => @$response['response']['response']->bankrefno,
                                ]
                            );

                        }
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



         