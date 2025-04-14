<?php
namespace App\Console\Commands;

use App\Models\MatmTransaction;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class MatmTransactionAmountSettle extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'matm_amount_settle:update';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Matm service amount settle to main wallet.';

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
            ->select('attribute_3')
            ->where(['slug' => 'matm_credit_transaction'])
            ->first();
        $time = 1;
        if (isset($GlobalConfig)) {
            $time = isset($GlobalConfig->attribute_3) ? $GlobalConfig->attribute_3 : 1;
        }
        $transactions = MatmTransaction::select('user_id')
            ->where([ 'is_trn_credited' => '0', 'status' => 'processed'])
            ->where('transaction_amount', '>', 0)
            ->whereNull('trn_credited_at')
            ->where('created_at', '<', Carbon::now()->subHours($time))
            ->where('transaction_type', 'cw')
            ->groupBy('user_id')
            ->get();

        foreach ($transactions as $transaction) {
            $count++;
            dispatch(new \App\Jobs\MatmTransactionAmountJob($transaction->user_id, $time));
        }

        if ($count == 0) {
            $messages = "No record found";
        } else {
            $messages =  $count." Records updated successfully.";
        }
        $this->info($messages);
    }
}
