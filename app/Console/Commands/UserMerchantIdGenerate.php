<?php

namespace App\Console\Commands;

use App\Models\GlobalConfig;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class UserMerchantIdGenerate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'merchant_id:generate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'User merchant id generate';

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
        $messages = "";
        $i = 0;
        $businessInfos = DB::table('business_infos')->whereNull('merchant_id')->get();
        foreach ($businessInfos as $key => $value) {
            $user = DB::table('users')->select(DB::raw('RIGHT(account_number , 5) as acc_num'))->where('id', $value->user_id)->first();
            if (isset($user->acc_num) && !empty($user->acc_num)) {
                $randomLetter = substr(str_shuffle("ABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, 4);
                $merchantId = $randomLetter.$user->acc_num;
                $user = DB::table('business_infos')->where('user_id', $value->user_id)->update(['merchant_id' => $merchantId]);
                $i ++;
            }

        }
        if ($i == 0) {
            $messages = "No record found";
        } else {
            $messages =  $i." Records updated successfully.";
        }
        $this->info($messages);
    }

}
