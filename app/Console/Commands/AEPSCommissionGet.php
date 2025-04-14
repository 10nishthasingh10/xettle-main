<?php

namespace App\Console\Commands;

use App\Helpers\CommonHelper;
use App\Helpers\TransactionHelper;
use App\Models\AepsTransaction;
use App\Models\GlobalConfig;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class AEPSCommissionGet extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'aeps_commission:update';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'AEPS service commission get and update.';

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
        $aepsTransactions = AepsTransaction::where('commission', 0)
            ->whereIn('transaction_type', ['cw','ms'])
            ->where('status', '=', 'success')
            ->where('created_at', '<', Carbon::now()->subMinutes($time))
            ->offset($offset)
            ->limit($limit)
            ->whereIn('user_id', [$userId])
            ->get();

        foreach ($aepsTransactions as $aepsTransaction) {
            $count++;
            $requestType = $aepsTransaction->transaction_type;
            $slug = '';
            $transactionAmount = 0;
            if ($requestType  == 'ms') {
                $slug = 'aeps_ms';
                $transactionAmount = 1;
            } elseif ($requestType  == 'cw') {
                $slug = 'aeps_cw';
                $transactionAmount = $aepsTransaction->transaction_amount;
            } elseif ($requestType  == 'ap') {
                $slug = 'aeps_ap';
                $transactionAmount = $aepsTransaction->transaction_amount;
            }
            $getProductId = CommonHelper::getProductId($slug, 'aeps');
            $productId = isset($getProductId->product_id) ? $getProductId->product_id : "";
            $getFeesAndTaxes = TransactionHelper::getFeesAndTaxes($productId, $transactionAmount, $aepsTransaction->user_id);
            $commission = isset($getFeesAndTaxes['fee']) ? $getFeesAndTaxes['fee'] : 0;
            $tds = isset($getFeesAndTaxes['tax']) ? $getFeesAndTaxes['tax'] : 0;
            $margin = isset($getFeesAndTaxes['margin']) ? $getFeesAndTaxes['margin'] : "";
            AepsTransaction::where(['id' => $aepsTransaction->id])
                ->update(['commission' => $commission, 'tds' => $tds, 'margin' => $margin]);
        }
        if ($count == 0) {
            $messages = "No record found";
        } else {
            $messages =  $count." Records updated successfully.";
        }
        $this->info($messages);
    }

}
