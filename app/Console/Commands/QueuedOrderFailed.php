<?php

namespace App\Console\Commands;

use App\Models\BulkPayout;
use App\Models\BulkPayoutDetail;
use App\Models\MApiLog;
use Illuminate\Console\Command;
use App\Models\Order;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class QueuedOrderFailed extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'queued_order:update';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Queued payout orders Update';

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
        ->where(['slug' => 'queued_order_count'])
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
        $orders = Order::where('orders.status', 'queued')
            ->select('orders.order_ref_id', 'orders.order_id', 'batch_id', 'user_id')
            ->where('orders.integration_id',  '=', NULL)
            ->where('created_at', '<', $times)
            ->orderBy('orders.id', 'asc')
            ->offset($offset)
            ->limit($limit)
            ->get();
            $i = 0;
        foreach ($orders as $order)
        {
            $apiLog = MApiLog::where(["txnid" => $order->order_ref_id])
            ->select('txnid')->where('txnid', $order->order_ref_id)
            ->count();

            $transactions = DB::table('transactions')
                ->select('txn_ref_id')
                ->where('txn_ref_id', $order->order_ref_id)
                ->count();
            if ($apiLog == 0 && $transactions == 0) {
                $i++;
                Order::where('orders.status', 'queued')
                    ->where('orders.integration_id',  '=', NULL)
                    ->where('orders.order_ref_id', $order->order_ref_id)
                    ->update(['status' => 'failed',
                    'failed_status_code' => '0x0205', 'failed_message' =>
                    'Some thing went wrong.', 'txt_1' => '1']);

                if (!empty($order->batch_id)) {
                    $OrderCancelledCount = Order::where(['batch_id' => $order->batch_id, 'status' => 'failed'])->count();
                    $OrderTotalCount = Order::where(['batch_id' => $order->batch_id])->count();

                    BulkPayoutDetail::payStatusUpdate($order->batch_id,'failed', $order->order_ref_id ,'Order failed' ,'');
                    if ($OrderTotalCount == ($OrderCancelledCount + 1)){
                        BulkPayout::updateStatusByBatch($order->batch_id, array('status'=>'failed'));
                    }else{
                        BulkPayout::updateStatusByBatch($order->batch_id, array());
                    }
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
