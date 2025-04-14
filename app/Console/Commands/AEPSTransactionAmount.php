<?php
namespace App\Console\Commands;

use App\Models\AepsTransaction;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class AEPSTransactionAmount extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'aeps_amount_settle:update';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'AEPS service amount settle to main wallet.';

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
            ->where(['slug' => 'aeps_credit_transaction'])
            ->first();
        $offset = 0;
        $limit = 50;
        $time = 1;
        if (isset($GlobalConfig)) {
            $offset = isset($GlobalConfig->attribute_1) ? $GlobalConfig->attribute_1 : 0;
            $limit = isset($GlobalConfig->attribute_2) ? $GlobalConfig->attribute_2 : 50;
            $time = isset($GlobalConfig->attribute_3) ? $GlobalConfig->attribute_3 : 1;
        }
        $transactions = AepsTransaction::select('user_id')
            ->where([ 'is_trn_credited' => '0', 'status' => 'success'])
            ->where('transaction_amount', '>', 0)
            ->whereNull('trn_credited_at')
            ->where('created_at', '<', Carbon::now()->subHours($time))
            ->where('transaction_type', 'cw')
            ->groupBy('user_id')
            ->get();
        foreach ($transactions as $transaction) {
            $count++;
            dispatch(new \App\Jobs\AEPSTransactionAmountSettlement($transaction->user_id, $time));
        }

        $transactionsAp = AepsTransaction::select('user_id')
            ->where([ 'is_trn_credited' => '0', 'status' => 'success'])
            ->where('transaction_amount', '>', 0)
            ->whereNull('trn_credited_at')
            ->where('created_at', '<', Carbon::now()->subHours($time))
            ->where('transaction_type', 'ap')
            ->groupBy('user_id')
            ->get();
        foreach ($transactionsAp as $transaction) {

            dispatch(new \App\Jobs\AEPSAadhaarpeTxnAmountSettleJob($transaction->user_id, $time));
        }

        if ($count == 0) {
            $messages = "No records found";
        } else {
            $messages =  $count." Records updated successfully.";
        }
        $this->info($messages);
    }

}
