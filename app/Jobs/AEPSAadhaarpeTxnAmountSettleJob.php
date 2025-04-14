<?php

namespace App\Jobs;

use App\Helpers\CommonHelper;
use App\Models\AepsTransaction;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Jobs\SendTransactionEmailJob;

class AEPSAadhaarpeTxnAmountSettleJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     *
     * */

    private $userId, $time;
    public function __construct($userId, $time)
    {
       $this->userId = $userId;
       $this->time = $time;
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
                        ->where(['user_id' => $this->userId, 'is_trn_credited' => '0',
                        'status' => 'success'])
                        ->whereNull('trn_credited_at')
                        ->where('transaction_amount', '>', 0)
                        ->where('created_at', '<', Carbon::now()->subHours($this->time))
                        ->where('transaction_type', 'ap')
                        ->pluck('transaction_amount', 'id')->toArray();

                $fee = AepsTransaction::groupBy('id')
                        ->where(['user_id' => $this->userId, 'is_trn_credited' => '0',
                        'status' => 'success'])
                        ->whereNull('trn_credited_at')
                        ->where('fee', '>', 0)
                        ->where('created_at', '<', Carbon::now()->subHours($this->time))
                        ->where('transaction_type', 'ap')
                        ->pluck('fee', 'id')->toArray();

                $tax = AepsTransaction::groupBy('id')
                        ->where(['user_id' => $this->userId, 'is_trn_credited' => '0',
                        'status' => 'success'])
                        ->whereNull('trn_credited_at')
                        ->where('tax', '>', 0)
                        ->where('created_at', '<', Carbon::now()->subHours($this->time))
                        ->where('transaction_type', 'ap')
                        ->pluck('tax', 'id')->toArray();
                if (isset($transaction) && count($transaction)) {

                    $totalAmount = array_sum($transaction);
                    $feeAmount = array_sum($fee);
                    $taxAmount = array_sum($tax);

                    $idArray = array_keys($transaction);

                    $txn = CommonHelper::getRandomString('TXN', false);
                    $txnRefId = CommonHelper::getRandomString('AAST', false);
                    $idOfAepsTransaction = implode(',', $idArray);

                    $fee = $feeAmount;
                    $tax = $taxAmount;
                    $finalAmount = $totalAmount - ($fee + $tax);

                    DB::select("CALL aepsCreditAmountTransaction($this->userId,  'srv_1626077390', '".$txn."', '".$txnRefId."', 'aeps_apinward_credit',$fee, $tax, $totalAmount, $finalAmount , '$idOfAepsTransaction', @json)");
                    $results = DB::select('select @json as json');
                    $response = json_decode($results[0]->json, true);

                    if($response['status'] == '1') {
                        $user = DB::table('users')
                            ->select('email', 'name', 'account_number')
                            ->where('id', $this->userId)
                            ->first();
                        if (!empty($user->email)) {
                            try {
                                $mailParms = [
                                    'email' => $user->email,
                                    'name' => $user->name,
                                    'amount' => $totalAmount,
                                    'transfer_date' => date('Y-m-d H:i:s'),
                                    'acc_number' => $user->account_number,
                                    'ref_number' => $txnRefId
                                ];
                                dispatch(new SendTransactionEmailJob((object) $mailParms, 'sendAEPSCreditTxn'));
                            } catch (\Exception $e) {
                                //mail not send
                                Storage::put('aepsCredit.txt', print_r(['date' => date('Y-m-d H:i:s'), 'msg' => $e->getMessage(), 'line' => $e->getLine()], true));
                            }
                        }
                    }
                }

        } catch (\Exception  $e) {
            $fileName = 'public/AEPSTransaction'.$this->userId.'.txt';
            Storage::disk('local')->put($fileName, $e.date('H:i:s'));
        }

    }

    public function middleware()
    {
        return [new WithoutOverlapping($this->userId)];
    }
}