<?php

namespace App\Jobs;

use App\Models\DMTFundTransfer;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Illuminate\Queue\SerializesModels;
use App\Helpers\ResponseHelper as Response;
use App\Http\Controllers\Clients\Api\v1\DMTController;
use Illuminate\Support\Facades\Storage;

class DMTFundTransferJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     *
     * */


     
    private $userId, $service, $body, $clientRefId, $orderRefId;
    public function __construct($userId, $service, $body, $clientRefId, $orderRefId)
    {
       $this->userId = $userId;
       $this->service = $service;
       $this->body = $body;
       $this->clientRefId = $clientRefId;
       $this->orderRefId = $orderRefId;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {

        try {
            $debit = DMTFundTransfer::moveOrderToProcessingByOrderId($this->userId, $this->orderRefId);

                if ($debit['status']) {
                $response = $this->service->init($this->body, '/fi/remit/out/domestic' . '/fundTransfer', 'fundTransfer', $this->userId, 'yes', 'dmt', 'POST');
                DMTFundTransfer::updateRecord(
                    ['user_id' => $this->userId, 'client_ref_id' => $this->clientRefId],
                    ['is_api_call' => '1', 'cron_date' => date('Y-m-d H:i:s')]
                );

                if (isset($response['response']['response']->statuscode) && $response['response']['response']->statuscode == 'TXN') {

                    DMTFundTransfer::updateRecord(
                        ['user_id' => $this->userId, 'client_ref_id' => $this->clientRefId],
                        [
                            'status' => 'processed',
                            'beni_name' => @$response['response']['response']->data->beneficiaryName,
                            'bank_ifsc' => @$response['response']['response']->data->beneficiaryIfsc,
                            'bank_account' => @$response['response']['response']->data->beneficiaryAccount,
                        'utr' => $response['response']['response']->data->txnReferenceId]
                    );

                    DMTFundTransfer::cashbackCredit($this->userId, $this->orderRefId);

                    $resp = DMTController::responseFormat('fundTransfer', $response['response']['response']->data, $this->clientRefId);
                    DMTController::sendCallback($this->userId, $this->orderRefId, 'processed');


                    return Response::success($response['response']['response']->status, $resp);
                } else if (isset($response['response']['response']->statuscode) && in_array($response['response']['response']->statuscode, ['TUP'])) {

                    DMTFundTransfer::updateRecord(
                        ['user_id' => $this->userId, 'client_ref_id' => $this->clientRefId],
                        [
                            'beni_name' => @$response['response']['response']->data->beneficiaryName,
                            'bank_ifsc' => @$response['response']['response']->data->beneficiaryIfsc,
                            'bank_account' => @$response['response']['response']->data->beneficiaryAccount]);

                    return Response::pending($response['response']['response']->status, $response['response']['response']->data);
                } else {


                    DMTFundTransfer::updateRecord(
                        ['user_id' => $this->userId, 'client_ref_id' => $this->clientRefId],
                        [
                            'beni_name' => @$response['response']['response']->data->beneficiaryName,
                            'bank_ifsc' => @$response['response']['response']->data->beneficiaryIfsc,
                            'bank_account' => @$response['response']['response']->data->beneficiaryAccount]);

                    DMTFundTransfer::fundRefunded($this->userId, $this->orderRefId, @$response['response']['response']->status, 'dmt_fund_refunded', @$response['response']['response']->statuscode);
                    DMTController::sendCallback($this->userId, $this->orderRefId, 'failed');

                    return Response::failed(@$response['response']['response']->status);
                }
            }

        } catch (\Exception  $e) {
            $fileName = 'public/DMTFundTransafer'.$this->userId.'.txt';
            Storage::disk('local')->put($fileName, $e.date('H:i:s'));
        }

    }

    public function middleware()
    {
        return [new WithoutOverlapping($this->userId)];
    }
}