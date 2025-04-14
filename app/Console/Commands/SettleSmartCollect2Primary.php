<?php

namespace App\Console\Commands;

use App\Helpers\CommonHelper;
use App\Jobs\PrimaryFundCredit;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class SettleSmartCollect2Primary extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'funds:smart_collect_settle_to_primary';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Settle Funds from Smart Collect Wallet to Primary Wallet';

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

        try {

            //check service is enable or ont
            $globalConfig = DB::table('global_config')
                ->select('attribute_1', 'attribute_2')
                ->where('slug', 'smart_collect')
                ->first();

            $isServiceActive = false;
            $usersIdsArr = null;

            if (!empty($globalConfig)) {
                if ($globalConfig->attribute_1 == '1') {
                    $isServiceActive = true;
                } else if (!empty($globalConfig->attribute_2)) {
                    $usersIdsArr = explode(",", trim($globalConfig->attribute_2));
                    $isServiceActive = true;
                }
            }

            if ($isServiceActive) {

                $timestamp = date('Y-m-d H') . ':00:00';

                $timestampBefore30Min = date('Y-m-d H:i:s', strtotime("-30 minute", strtotime($timestamp)));
                $str2TimestampBefore30Min = strtotime($timestampBefore30Min);

                $timestampBefore4Hours = date('Y-m-d H:i:s', strtotime("-9 hour", $str2TimestampBefore30Min));
                $str2TimestampBefore4Hours = strtotime($timestampBefore4Hours);

                // Storage::prepend('funds_cron_sc_' . date('Y_m_d') . '.txt', 'CRON at: ' . date('Y-m-d H:i:s') . ", Timestamp: " . $timestamp . " - " . $timestampBefore30Min . " - " . $timestampBefore4Hours);

                $callbacks = DB::table('cf_merchants_fund_callbacks')
                    ->select(
                        'user_id',
                        'batch_id',
                        DB::raw('SUM(amount) AS total_amount'),
                        DB::raw('SUM(fee) AS total_fee'),
                        DB::raw('SUM(tax) AS total_tax'),
                        DB::raw('SUM(cr_amount) AS total_cr_amount'),
                        // DB::raw('GROUP_CONCAT(id SEPARATOR ",") AS ids'),
                        DB::raw('COUNT(id) AS counts')
                    );

                if (!empty($usersIdsArr)) {
                    $callbacks->whereIn('user_id', $usersIdsArr);
                }

                $callbacks = $callbacks->whereNotNull('batch_id')
                    ->where('is_trn_credited', '0')
                    ->where('is_trn_settle', '0')
                    ->where('is_vpa', '1')
                    ->whereRaw('UNIX_TIMESTAMP(`created_at`) < ' . $str2TimestampBefore30Min)
                    ->whereRaw('UNIX_TIMESTAMP(`created_at`) >= ' . $str2TimestampBefore4Hours)
                    ->orderBy('id', 'ASC')
                    ->groupBy('batch_id', 'user_id')
                    ->get();

                if (!$callbacks->isEmpty()) {
                    foreach ($callbacks as $row) {

                        //if service is active, settlement credit
                        $isSettlementActive = CommonHelper::checkIsSettlementActive($row->user_id, 'smart_collect', 'upi');

                        if ($isSettlementActive) {
                            $txnId = CommonHelper::getRandomString('txn', false);

                            DB::table('cf_merchants_fund_callbacks')
                                //->whereIn('id', explode(',', $row->ids))
                                ->where('batch_id', $row->batch_id)
                                ->where('is_trn_settle', '0')
                                ->where('is_trn_credited', '0')
                                ->where('is_vpa', '1')
                                ->whereRaw('UNIX_TIMESTAMP(`created_at`) < ' . $str2TimestampBefore30Min)
                                ->whereRaw('UNIX_TIMESTAMP(`created_at`) >= ' . $str2TimestampBefore4Hours)
                                ->update([
                                    'txn_id' => $txnId,
                                    'is_trn_settle' => '2',
                                    'trn_settled_at' => $timestamp
                                ]);

                            $row->timestamp = $timestamp;
                            $row->txn_id = $txnId;
                            $row->frequency = 'hourly';

                            PrimaryFundCredit::dispatch($row, 'smart_collect_upi_settle_to_primary')->onQueue('primary_fund_queue');
                        }
                    }

                    $this->info("Jobs Assigned");
                } else {
                    $this->info("No Balance found for settlement");
                }
            } else {
                $this->info("Service is Disabled");
            }
        } catch (Exception $e) {
            $this->error($e->getMessage());
        }
    }
}
