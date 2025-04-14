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

class AEPSCommissionSettlement implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     *
     * */

    private $userId;
    public function __construct($userId)
    {
       $this->userId = $userId;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {

        try {

                $transaction = AepsTransaction::groupBy('id')
                ->where(['user_id' => $this->userId, 'is_commission_credited' => '0', 'status' => 'success'])
                ->where('commission', '>', 0)
                ->pluck('commission', 'id')->toArray();
                if (isset($transaction) && count($transaction)) {
                    $totalAmount = array_sum($transaction);
                    $idArray = array_keys($transaction);
                    $txn = CommonHelper::getRandomString('TXN', false);
                    $txnRefId = CommonHelper::getRandomString('AECOM', false);

                    $GlobalConfig = DB::table('global_config')
                        ->select('attribute_1', 'attribute_2', 'attribute_3')
                        ->where(['slug' => 'aeps_tds_and_gst_deduct'])
                        ->first();
                    $tds = isset($GlobalConfig->attribute_1) ? $GlobalConfig->attribute_1 : 10;
                    $gst = isset($GlobalConfig->attribute_2) ? $GlobalConfig->attribute_2 : 18;
                   // $tdsAmount = ($totalAmount * $tds) / 100 ;
                   // $gstAmount = ($tdsAmount * $gst) / 100 ;
                    $tdsAmount = 0;
                    $gstAmount = 0;
                    $totalTax = 0;
                    $finalAmount = $totalAmount - $totalTax;
                    $idOfAepsTransaction = implode(',', $idArray);
                    DB::select("CALL aepsCommissionCreditTransaction($this->userId,  'srv_1626077390', '".$txn."', '".$txnRefId."', 'aeps_commission_credit', $totalAmount ,$finalAmount, $tdsAmount ,$gstAmount , '$idOfAepsTransaction', @json)");
                    $results = DB::select('select @json as json');
                    $response = json_decode($results[0]->json, true);
                }

        } catch (\Exception  $e) {
            $fileName = 'public/AEPSCommision'.$this->userId.'.txt';
            Storage::disk('local')->put($fileName, $e.date('H:i:s'));
        }

    }

    public function middleware()
    {
        return [new WithoutOverlapping($this->userId)];
    }
}