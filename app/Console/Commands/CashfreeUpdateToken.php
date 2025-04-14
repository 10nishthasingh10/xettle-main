<?php

namespace App\Console\Commands;

use App\Helpers\CashfreeHelper;
use Illuminate\Console\Command;
// use Illuminate\Support\Facades\File;
// use App\Models\Apilog;
// use Exception;
// use Storage;
// use Cashfree;
use Illuminate\Support\Facades\Storage;

class CashfreeUpdateToken extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tokens:cashfree';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Cashfree token update';

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
        Storage::disk('local')->put('tokens/cashfreeToken.txt', 'Contents');
        $tokens = "";
        $message = "";
        $Cashfree = new CashfreeHelper;
        $authorize = $Cashfree->authorized();
        if (isset($authorize['data']->data)) {
            foreach ($authorize['data']->data as $key => $val) {
                if ($key == 'token') {
                    $tokens = $val;
                }
            }
        }

        if ($tokens == "") {
            if (isset($authorize['data']->message)) {
                $message = $authorize['data']->message;
            } else {
                $message = $authorize['data'];
            }
        } else {
            $message = "token generated successfully";
        }
        $this->info($message);
    }
}
