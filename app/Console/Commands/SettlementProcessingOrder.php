<?php

namespace App\Console\Commands;
use Illuminate\Console\Command;
use App\Models\Order;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class SettlementProcessingOrder extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'settlement_processing_order:update';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Settlement Processing payout orders Update';

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
            $orders = DB::table('user_settlement_logs')
            ->where('status', 'processing')
            ->where('cron_status', '1')
           // ->where('created_at', '<',  $times)
            ->orderBy('id', 'asc')
            ->offset($offset)
            ->limit($limit)
            ->get();
            $i = 0;
        foreach ($orders as $order)
        {
            $i++;
            DB::table('user_settlements')
            ->where(['settlement_ref_id' => $order->settlement_ref_id,
            'user_id' => $order->user_id])
            ->update([
                'txt_2' => 1
            ]);

            dispatch(new \App\Jobs\SettlementProcessingOrder($order->settlement_txn_id, $order->user_id))->onQueue('payout_queue');
        }

        if ($i == 0) {
            $message = "No record found";
        } else {
            $message =  $i." Records updated successfully.";
        }
        $this->info($message);
    }

}
