<?php

namespace App\Jobs;

use App\Helpers\CommonHelper;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class CallbackAepsTransactionJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     *
     * */

    private $clientRefId, $reqType, $userId, $tds, $commission, $margin;
    public function __construct($clientRefId, $type, $userId, $commission, $tds, $margin)
    {
       $this->clientRefId = $clientRefId;
       $this->reqType = $type;
       $this->userId = $userId;
       $this->commission = $commission;
       $this->tds = $tds;
       $this->margin = $margin;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $fileName = 'public/ddd'.$this->clientRefId.'.txt';
        //Storage::disk('local')->put($fileName, 'start 1'.date('H:i:s'));
        try {
            $txn = CommonHelper::getRandomString('txn', false);
            if ($this->reqType == 'cw') {
                $finalCommission = $this->commission - $this->tds;
                DB::select("CALL aepsCreditTransaction('".$this->clientRefId."', $this->userId,  'srv_1626077390', '".$txn."', $this->commission, $this->tds, $finalCommission, 'cw', '".$this->margin."', @json)");
                $results = DB::select('select @json as json');
                $response = json_decode($results[0]->json, true);
            } elseif ($this->reqType == 'ms') {
                $finalCommission = $this->commission - $this->tds;
                Storage::disk('local')->append($fileName, 'ms'.date('H:i:s'));
                DB::select("CALL aepsCreditTransaction('".$this->clientRefId."', $this->userId,  'srv_1626077390', '".$txn."', $this->commission, $this->tds, $finalCommission,'ms', '".$this->margin."', @json)");
                $results = DB::select('select @json as json');
                $response = json_decode($results[0]->json, true);
            } /*elseif ($this->reqType == 'be') {
                $finalCommission = $this->commission - $this->tds;
                Storage::disk('local')->append($fileName, 'be'.date('H:i:s'));
                DB::select("CALL aepsCreditTransaction('".$this->clientRefId."', $this->userId, 'srv_1626077390', '".$txn."', $this->commission, $this->tds, $finalCommission,'be', '".$this->margin."', @json)");
                $results = DB::select('select @json as json');
                $response = json_decode($results[0]->json, true);
            } */
        } catch (\Exception  $e) {
            Storage::disk('local')->append($fileName, $e.date('H:i:s'));
        }

    }

    public function middleware()
    {
        return [new WithoutOverlapping($this->userId)];
    }
}