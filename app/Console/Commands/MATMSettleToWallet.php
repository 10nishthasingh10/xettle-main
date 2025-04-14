<?php

namespace App\Console\Commands;

use App\Models\MatmTransaction;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class MATMSettleToWallet extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'matm_commission_settle:update';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Matm service commission settle to wallet.';

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
            ->select( 'attribute_5')
            ->where(['slug' => 'matm_commission_update'])
            ->first();

        $userId = 0;
        if (isset($GlobalConfig)) {
            $userId = isset($GlobalConfig->attribute_5) ? $GlobalConfig->attribute_5 : 0;
        }

        $messages = '';
        $count = 0;
        $transactions = MatmTransaction::select('user_id')
            ->where([ 'is_commission_credited' => '0', 'status' => 'processed', 'transaction_type' => 'cw'])
            ->where('commission', '>', 0)
            ->where('is_commission_credited', '=', '0')
            ->where('is_trn_credited', '=', '1')
            ->whereNull('commission_credited_at')
            ->groupBy('user_id')
            ->whereIn('user_id', [$userId])
            ->get();
        foreach ($transactions as $transaction) {
            $count++;
            dispatch(new \App\Jobs\MatmCommissionSettlement($transaction->user_id));
        }
        if ($count == 0) {
            $messages = "No record found";
        } else {
            $messages =  $count." Records updated successfully.";
        }
        $this->info($messages);
    }

}
