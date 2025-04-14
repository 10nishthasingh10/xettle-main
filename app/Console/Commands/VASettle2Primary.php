<?php

namespace App\Console\Commands;

use App\Helpers\CommonHelper;
use App\Jobs\PrimaryFundCredit;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class VASettle2Primary extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'funds:va_settle_to_primary';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Settle Funds from VA Wallet to Primary Wallet';

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

            $jobCounts = 0;

            //check service is enable or not
            $globalConfig = DB::table('global_config')
                ->select('attribute_1', 'attribute_2', 'attribute_4')
                ->where('slug', SRV_SLUG_VA)
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

            // dd($isServiceActive, $usersIdsArr);

            if ($isServiceActive) {

                $timestamp = date('Y-m-d H') . ':00:00';
                $time = date('H') . ':00:00';

                $timestampBefore30Min = date('Y-m-d H:i:s', strtotime("-30 minute", strtotime($timestamp)));
                $str2TimestampBefore30Min = strtotime($timestampBefore30Min);

                if ($time === "06:00:00") {
                    $timestampBefore4Hours = date('Y-m-d H:i:s', strtotime("-9 hour", $str2TimestampBefore30Min));
                } else {
                    $timestampBefore4Hours = date('Y-m-d H:i:s', strtotime("-2 hour", $str2TimestampBefore30Min));
                }

                $str2TimestampBefore4Hours = strtotime($timestampBefore4Hours);


                $activeSettleUsers = DB::table('user_config')
                    ->select(
                        'user_id',
                        'va_settlements',
                        'va_settlement_frequency',
                        // DB::raw('GROUP_CONCAT(id SEPARATOR ",") AS ids')
                    )
                    ->where('va_settlement_frequency', 'hourly')
                    ->whereNotNull('va_settlements');

                if (!empty($usersIdsArr)) {
                    $activeSettleUsers->whereIn('user_id', $usersIdsArr);
                }

                $activeSettleUsers = $activeSettleUsers->get();

                // dd($timestamp, $activeSettleUsers);

                if ($activeSettleUsers->isNotEmpty()) {
                    foreach ($activeSettleUsers as $row) {
                        $roots = explode(',', $row->va_settlements);

                        $upiCallbacks = DB::table('upi_callbacks')
                            ->select(
                                'root_type',
                                'user_id',
                                'batch_id',
                                DB::raw('SUM(amount) AS total_amount'),
                                DB::raw('SUM(fee) AS total_fee'),
                                DB::raw('SUM(tax) AS total_tax'),
                                DB::raw('SUM(cr_amount) AS total_cr_amount'),
                                // DB::raw('GROUP_CONCAT(id SEPARATOR ",") AS ids'),
                                DB::raw('COUNT(id) AS counts')
                            )
                            ->whereNotNull('batch_id')
                            ->where('is_trn_credited', '0')
                            ->where('is_trn_settle', '0')
                            ->where('user_id', $row->user_id)
                            ->whereIn('root_type', $roots)
                            ->whereRaw('UNIX_TIMESTAMP(`created_at`) < ' . $str2TimestampBefore30Min)
                            ->whereRaw('UNIX_TIMESTAMP(`created_at`) >= ' . $str2TimestampBefore4Hours)
                            ->orderBy('id', 'ASC')
                            ->groupBy('batch_id', 'user_id')
                            ->get();

                        // print_r($upiCallbacks);
                        // dd($roots, $upiCallbacks);

                        if ($upiCallbacks->isNotEmpty()) {
                            foreach ($upiCallbacks as $callbackRow) {
                                $txnId = CommonHelper::getRandomString('txn', false);

                                DB::table('upi_callbacks')
                                    //->whereIn('id', explode(',', $row->ids))
                                    ->where('batch_id', $callbackRow->batch_id)
                                    ->where('user_id', $callbackRow->user_id)
                                    ->where('is_trn_settle', '0')
                                    ->where('is_trn_credited', '0')
                                    // ->whereRaw('UNIX_TIMESTAMP(`created_at`) < ' . $str2TimestampBefore30Min)
                                    // ->whereRaw('UNIX_TIMESTAMP(`created_at`) >= ' . $str2TimestampBefore4Hours)
                                    ->update([
                                        'txn_id' => $txnId,
                                        'is_trn_settle' => '2',
                                        'trn_settled_at' => $timestamp
                                    ]);

                                $callbackRow->timestamp = $timestamp;
                                $callbackRow->txn_id = $txnId;
                                $callbackRow->frequency = $row->va_settlement_frequency;

                                PrimaryFundCredit::dispatch($callbackRow, 'upi_stack_settle_to_primary')->onQueue('primary_fund_queue');
                                $jobCounts++;
                            }
                        }
                    }
                }
            }


            if ($jobCounts > 0) {
                $this->info("$jobCounts Jobs Assigned");
            } else if ($isServiceActive == false) {
                $this->info("Service is Disabled");
            } else {
                $this->info("No Job Assigned");
            }
        } catch (Exception $e) {
            Storage::prepend("upi_stack_settle_hourly.txt", "CRON at: " . date('Y-m-d H:i:s') . " | Error: " . $e->getMessage());
            $this->error($e->getMessage());
        }
    }
}
