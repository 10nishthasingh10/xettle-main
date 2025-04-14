<?php

namespace App\Console\Commands;

use App\Models\GlobalConfig;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class PayoutServiceDown extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'payout_service:update';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Payout service enable or disable';

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
        $globalConfig = GlobalConfig::where('slug', 'payout')->first();
        if (isset($globalConfig)) {
            if ($globalConfig->attribute_1 == 1) {
                $globalConfig->attribute_1 = 0;
                $messages = 'Payout service is disabled.';
            } else {
                $globalConfig->attribute_1 = 1;
                $messages = 'Payout service is enable.';
            }
            $globalConfig->save();
        }
        $this->info($messages);
    }

}
