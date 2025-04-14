<?php

namespace App\Jobs;

use App\Helpers\CommonHelper;
use App\Models\AepsTransaction;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class AEPSFundTransferToPayoutJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     *
     * */

    private $userId, $amount;
    public function __construct($userId, $amount)
    {
       $this->userId = $userId;
       $this->amount = $amount;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {

        try {

            $txnDr = CommonHelper::getRandomString('txn', false);
            $txnCr = CommonHelper::getRandomString('txn', false);
            $remarks =  '';
            DB::select("CALL aepsInternalTransfer($this->userId, '".AEPS_SERVICE_ID."', '".PAYOUT_SERVICE_ID."', $this->amount, '".$txnDr."', '".$txnCr."', '".$remarks."', @json)");
            $results = DB::select('select @json as json');
            $response = json_decode($results[0]->json, true);

        } catch (\Exception  $e) {
            $fileName = 'public/AEPSFundTransToPayout'.$this->userId.'.txt';
            Storage::disk('local')->append($fileName, $e.date('H:i:s'));
        }

    }

    public function middleware()
    {
        return [new WithoutOverlapping($this->userId)];
    }
}