<?php

namespace App\Console\Commands;
use Illuminate\Console\Command;
use App\Models\Order;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class SafexpayJobOrderStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'safexpayjoborderstatus:update';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Safexpay Processing payout orders Update';

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
            ->select('attribute_3')
            ->where(['slug' => 'processing_order_count'])
            ->first();

        $time = 5;
        if (isset($GlobalConfig)) {
            $time = isset($GlobalConfig->attribute_3) ? $GlobalConfig->attribute_3 : 5;
        }
            $orders = Order::where('orders.status', 'processing')
            ->where('orders.cron_status', '1')
            //->where('orders.area', '11')
            ->where('orders.order_id', '=', NULL)
            ->where('orders.integration_id', 'int_1632375562')
            ->where('orders.payout_id', '!=', NULL)
            ->where('created_at', '<', Carbon::now()->subMinutes($time))
            ->orderBy('orders.id', 'asc')
            ->limit(100)
            ->get();
            $i = 0;
        foreach ($orders as $order)
        {
            $i++;
            dispatch(new \App\Jobs\ProcessingOrderUpdateJob($order->order_ref_id, $order->user_id))->onQueue('payout_queue');
        }
        if ($i == 0) {
            $message = "No record found";
        } else {
            $message =  $i." Records updated successfully.";
        }
        $this->info($message);
    }

}
