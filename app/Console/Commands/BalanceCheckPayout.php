<?php

namespace App\Console\Commands;

use App\Helpers\InstantPayHelper;
use App\Models\GlobalConfig;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class BalanceCheckPayout extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'partner_account_balance:update';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Partner account balance check update';

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
        $GlobalConfig = GlobalConfig::select('attribute_1', 'attribute_2', 'attribute_3', 'attribute_4')
            ->where(['slug' => 'partner_account_balance'])
            ->first();
            $status = 0;
            $route = '';
            if (isset($GlobalConfig)) {
                $status = isset($GlobalConfig->attribute_1) ? $GlobalConfig->attribute_1 : 0;
                $route = isset($GlobalConfig->attribute_2) ? $GlobalConfig->attribute_2 : 'ipay';
            }

       if ($status == 1 && !empty($route)) {
            if ($route == 'ipay') {
                $ipay = new InstantPayHelper;
                $rand = 'be'.rand(1, 99999999);
                $ipay = $ipay->instantpayBalanceCheck($rand);
 
                if (isset($ipay['data']) && $ipay['data'] != null) {
                    if (isset($ipay['data']->statuscode) && $ipay['data']->statuscode == 'TXN') {
                        if ($ipay['data']->data->balance->available > 0) {
                            DB::table('global_config')
                            ->where('slug', 'partner_account_balance')
                            ->update(['attribute_3' => $ipay['data']->data->balance->available, 'updated_at' => date('Y-m-d H:i:s')]);
                        }
                    }
                }
            }
            $message =  "Balance enquiry updated";
       } else {
            $message =  "Balance enquiry is disable";
       }
       $this->info($message);
    }


}
