<?php
namespace App\Console\Commands;

use App\Models\Order;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Console\Command;
use DB;
class ExtraPayoutTransactionDelete extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'extra_payout_transaction_delete:update';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Extra payout transaction delete.';

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
        $messages = '';
        $count = 0;
        $GlobalConfig = DB::table('global_config')
            ->select('attribute_1', 'attribute_2', 'attribute_3')
            ->where(['slug' => 'extra_payout_transaction_delete'])
            ->first();
        $offset = 0;
        $limit = 50;
        $time = 1;
        if (isset($GlobalConfig)) {
            $offset = isset($GlobalConfig->attribute_1) ? $GlobalConfig->attribute_1 : 0;
            $limit = isset($GlobalConfig->attribute_2) ? $GlobalConfig->attribute_2 : 50;
            $time = isset($GlobalConfig->attribute_3) ? $GlobalConfig->attribute_3 : 1;
        }
        $transactions = Transaction::select('order_id')
            ->where('service_id', 'srv_1626077095')
            ->where('order_id', '!=', NULL)
           // ->where('order_id', 'ord_e6b71626933186')
            ->groupBy('order_id')
            ->having(DB::raw('count(order_id)'), '>', 2)
            ->offset($offset)
            ->limit($limit)
            ->get();
        foreach ($transactions as $transaction) {
            $count++;
            $firstTxn = Transaction::where('service_id', 'srv_1626077095')
                ->select('id','trans_id', 'opening_balance', 'closing_balance')
                ->where('order_id', $transaction->order_id)
                ->orderBy('id', 'asc')
                ->first();
            $lastTxn = Transaction::where('service_id', 'srv_1626077095')
                ->select('id', 'opening_balance', 'closing_balance')
                ->where('order_id', $transaction->order_id)
                ->orderBy('id', 'desc')
                ->first();
                // amount
            $amountTxn = Transaction::where('service_id', 'srv_1626077095')
                ->select('tr_amount')
                ->where(['order_id' => $transaction->order_id, 'tr_identifiers' => 'payout_disbursement'])
                ->orderBy('id', 'asc')
                ->first();
            $feeTxn = Transaction::where('service_id', 'srv_1626077095')
                ->select('tr_fee', 'tr_amount')
                ->where(['order_id' => $transaction->order_id, 'tr_identifiers' => 'payout_fee'])
                ->orderBy('id', 'asc')
                ->first();
            $taxTxn = Transaction::where('service_id', 'srv_1626077095')
                ->select('tr_tax', 'tr_amount')
                ->where(['order_id' => $transaction->order_id, 'tr_identifiers' => 'payout_fee_tax'])
                ->orderBy('id', 'asc')
                ->first();
            $total = $amountTxn->tr_amount + $feeTxn->tr_amount + $taxTxn->tr_amount;
            $tr_narration = "$total debited against $transaction->order_id";
            $ordersData = DB::table('orders')
                ->where(['order_id' => $transaction->order_id])
                ->select('order_ref_id')
                ->first();
            $orderRefId = @$ordersData->order_ref_id;
            $txnUpdate = DB::table('transactions')
                ->where('id', $firstTxn->id)
                ->update(
                    ['tr_narration' => $tr_narration, 'closing_balance' => $lastTxn->closing_balance,
                    'tr_fee' => $feeTxn->tr_amount, 'tr_tax' => $taxTxn->tr_amount,
                    'udf1' => $transaction->order_id, 'txn_id' => $firstTxn->trans_id,
                    'txn_ref_id' => $orderRefId, 'tr_total_amount' => '-'.$total,
                ]);
            $delete = DB::table('transactions')->where('id', '!=', $firstTxn->id)
                ->where(['order_id' => $transaction->order_id])->delete();
        }
        if ($count == 0) {
            $messages = "No records found";
        } else {
            $messages =  $count." Records updated successfully.";
        }
        $this->info($messages);
    }

}
