<?php

namespace App\Console\Commands;

use App\Helpers\CashfreeAutoCollectHelper;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;


class CashfreeAutoCollectUpdateToken extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tokens:cashfree_auto_collect';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Cashfree Auto Collect token update';

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
        Storage::put('tokens/cashfree_auto_collect_token.txt', 'Contents');
        $tokens = "";
        $message = "";
        $Cashfree = new CashfreeAutoCollectHelper();
        $authorize = $Cashfree->authorize();

        if (isset($authorize->data)) {
            foreach ($authorize->data as $key => $val) {
                if ($key == 'token') {
                    $tokens = $val;
                }
            }
        }

        if ($tokens == "") {
            if (isset($authorize->message)) {
                $message = $authorize->message;
            } else {
                $message = $authorize;
            }
        } else {
            $message = "token generated successfully";
        }
        $this->info($message);
    }
}
