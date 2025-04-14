<?php

namespace App\Console\Commands;

use App\Jobs\PrimaryFundCredit;
use Illuminate\Console\Command;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AutoSettlement extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'autosettlement:process';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Auto settlement process';

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


        $obj = DB::table('user_config')
            ->select('user_id')
            ->where('is_auto_settlement', '1')
            ->get();
            $i = 0;
        $GlobalConfig = DB::table('global_config')
            ->select('attribute_1', 'attribute_2')
            ->where(['slug' => 'auto_settlement_order_create'])
            ->where(['attribute_1' => '1'])
            ->first();
        $time = 30;
        if (isset($GlobalConfig) && !empty($GlobalConfig)) {
            $time = isset($GlobalConfig->attribute_2) ? $GlobalConfig->attribute_2 : 30;
            foreach ($obj as $objs)
            {
                $times =  Carbon::now()->subMinutes($time);
                $checkAlreadyExists = DB::table('user_settlements')
                    ->where(['user_id' => $objs->user_id])
                    ->where('created_at', '>', $times)
                    ->count();
                if ($checkAlreadyExists == 0) {
                    $i ++;
                    PrimaryFundCredit::dispatch($objs, 'auto_settlement_order')->onQueue('primary_fund_queue');
                }
            }
            if ($i == 0) {
                $message = "No record found";
            } else {
                $message =  $i." Records updated successfully.";
            }
        } else {
            $message =  $i." Records updated successfully.";
        }
        $this->info($message);
    }

}
