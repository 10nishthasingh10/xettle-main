<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Order;
use DB;
use App\Events\OrderSuccess;
use App\Events\OrderFailed;
use App\Events\OrderReversed;
class CallbackOrderUpdate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'callbackorder:update';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Cashfree callback Update payout orders';

    /**
     * Create a new command instance.
     *
     * @return void
     */

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $messages = '';
        $orderCount = 0;
        $log = [];
        // Success status callback
        $successPayouts = DB::table('cf_payout_callbacks')
            ->where('event', 'TRANSFER_SUCCESS')
            ->where('is_reflected', '0')
            ->get();
        foreach ($successPayouts as $successPayout) {
            $orderCount++;
            $errorDesc = "";
            $order = Order::where('status', 'processing')
            ->select('order_ref_id', 'user_id', 'batch_id', 'status')
                ->where('order_ref_id', $successPayout->txnid)
                ->where('cron_status', '1')
                ->first();
            if (isset($order) && $order->status == 'processing') {
                $responseJson = json_decode($successPayout->response, true);
                event(new OrderSuccess($order->user_id, $order->order_ref_id, $responseJson['utr'], '', '', 'Order successfully.', ''));
            }
            DB::table('cf_payout_callbacks')
                ->where('id', $successPayout->id)
                ->update(['is_reflected' => '1']);
        }
        // Failed status callback
        $failedPayouts = DB::table('cf_payout_callbacks')
            ->where('event', 'TRANSFER_FAILED')
            ->where('is_refunded', '0')
            ->get();
        foreach ($failedPayouts as $failedPayout) {
            $orderCount++;
            $errorDesc = "";
            $order = Order::where('status', 'processing')
            ->select('order_ref_id', 'user_id', 'batch_id', 'status', 'service_id', 'amount', 'fee', 'tax')
                ->where('order_ref_id', $failedPayout->txnid)
                ->where('cron_status', '1')
                ->first();
            if (isset($order) && $order->status == 'processing') {
                $responseJson = json_decode($failedPayout->response, true);
                $errorDesc = isset($responseJson['reason']) ? $responseJson['reason'] : "";
                $Order = DB::table('orders')->where('order_ref_id', $failedPayout->txnid)->first();
                event(new OrderFailed($Order->user_id, $Order->order_ref_id, '', $errorDesc, ''));
            }
            DB::table('cf_payout_callbacks')
                ->where('id', $failedPayout->id)
                ->update(['is_refunded' => '1']);
        }
        // Failed status callback
        $reversedPayouts = DB::table('cf_payout_callbacks')
        ->where('event', 'TRANSFER_REVERSED')
        ->where('is_refunded', '0')
        ->get();
        foreach ($reversedPayouts as $reversedPayout) {
        $orderCount++;
        $errorDesc = "";
        $order = Order::where('status', 'processed')
        ->select('order_ref_id', 'user_id', 'batch_id', 'service_id', 'status', 'amount', 'fee', 'tax')
            ->where('order_ref_id', $reversedPayout->txnid)
            ->where('cron_status', '1')
            ->first();
        if (isset($order) && $order->status == 'processed') {
            $responseJson = json_decode($reversedPayout->response, true);
            $errorDesc = isset($responseJson['reason']) ? $responseJson['reason'] : "";
            $bank_reference = isset($responseJson['referenceId']) ? $responseJson['referenceId'] : "";
            event(new OrderReversed($order->user_id, $order->order_ref_id, '', $errorDesc, $bank_reference));
        }
        DB::table('cf_payout_callbacks')
            ->where('id', $reversedPayout->id)
            ->update(['is_reversed' => '1']);
        }

        if ($orderCount == 0) {
            $message = ' No records found.';
        } else {
            $message = $orderCount;
        }
        $this->info($message);
    }
}