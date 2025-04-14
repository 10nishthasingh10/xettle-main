<?php

namespace App\Console\Commands;

use App\Helpers\CommonHelper;
use App\Helpers\TransactionHelper;
use App\Jobs\AEPSCommissionSettlement;
use App\Models\AepsTransaction;
use App\Models\GlobalConfig;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class AEPSSettleToWallet extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'aeps_commission_settle:update';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'AEPS service commission settle to wallet.';

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
        ->select('attribute_1', 'attribute_2', 'attribute_3', 'attribute_5')
        ->where(['slug' => 'aeps_commission_update'])
        ->first();
        $offset = 0;
        $limit = 50;
        $time = 1;
        $userId = 0;
        if (isset($GlobalConfig)) {
            $offset = isset($GlobalConfig->attribute_1) ? $GlobalConfig->attribute_1 : 0;
            $limit = isset($GlobalConfig->attribute_2) ? $GlobalConfig->attribute_2 : 50;
            $time = isset($GlobalConfig->attribute_3) ? $GlobalConfig->attribute_3 : 1;
            $userId = isset($GlobalConfig->attribute_5) ? $GlobalConfig->attribute_5 : 0;
        }

        $messages = '';
        $count = 0;
        $transactions = AepsTransaction::select('user_id')
            ->where([ 'is_commission_credited' => '0', 'status' => 'success', 'transaction_type' => 'cw'])
            ->where('commission', '>', 0)
            ->where('is_commission_credited', '=', '0')
            ->whereNull('commission_credited_at')
            ->groupBy('user_id')
            ->whereIn('user_id', [$userId])
            ->get();
        foreach ($transactions as $transaction) {
            $count++;
            dispatch(new \App\Jobs\AEPSCommissionSettlement($transaction->user_id));
        }
        if ($count == 0) {
            $messages = "No record found";
        } else {
            $messages =  $count." Records updated successfully.";
        }
        $this->info($messages);
    }

}
