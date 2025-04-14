<?php

namespace App\Jobs;

use App\Helpers\AEPSHelper;
use App\Helpers\AutoSettlementHelper;
use App\Helpers\CashfreeAutoCollectHelper;
use App\Helpers\EasebuzzInstaCollectHelper;
use App\Helpers\LeanMarkHelper;
use App\Helpers\RazorPaySmartCollectHelper;
use App\Helpers\TransactionHelper;
use App\Helpers\UpiCollectHelper;
use App\Helpers\ValidationSuiteHelper;
use App\Services\OpenBank\OBApiService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class PrimaryFundCredit implements ShouldQueue
{

    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 400;

    /**
     * The number of seconds the job can run before timing out.
     *
     * @var int
     */
    public $timeout = 14400;

    /**
     * Indicate if the job should be marked as failed on timeout.
     *
     * @var bool
     */
    public $failOnTimeout = true;

    public $jobData;
    public $route;
    public $userId;

    //job name: primary_fund_queue

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($data, $route = '')
    {
        $this->jobData = $data;
        $this->userId = $data->user_id;
        $this->route = $route;
        
        
    }


    public function middleware()
    {
        // $timeRA = [3, 5, 7, 10, 12, 15, 17, 20, 22, 25]; $timeRA[rand(0, 9)];
        return [(new WithoutOverlapping($this->userId))->releaseAfter(rand(3, 30))]; //->expireAfter(30)
    }


    public function retryUntil()
    {
        return now()->addHours(10);
    }


    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        \Log::info('Dispatching UpiCollectHelper job with data: ' . json_encode($this->route));
        switch ($this->route) {
            case 'lean_mark_balance_by_admin':
                (new LeanMarkHelper())->handleLeanBalance($this->jobData);
                break;

            case 'upi_stack_settle_to_primary':
                //dd("Test");
                \Log::info('Dispatching UpiCollectHelper job with data: ' . json_encode($this->jobData));
                UpiCollectHelper::upiStackSettle2Primary($this->jobData);
                \Log::info('UpiCollectHelper job dispatched successfully.');
                break;
            case 'upi_stack_credit':
                UpiCollectHelper::upiFundCredit($this->jobData);
                break;
            case 'upi_stack_creation_fee':
                UpiCollectHelper::upiStackDebitTxn($this->jobData);
                break;
            case 'upi_stack_verify_fee':
                UpiCollectHelper::upiStackVpaVerifyDebitTxn($this->jobData);
                break;
            case 'upi_stack_dispute':
                UpiCollectHelper::upiStackDisputedTxn($this->jobData);
                break;

            case 'validate_suite_fee':
                ValidationSuiteHelper::chargeValidationFee($this->jobData);
                break;

            case 'partner_van':
                CashfreeAutoCollectHelper::vanCreditTxn($this->jobData);
                break;
            case 'smart_collect_credit':
                CashfreeAutoCollectHelper::autoCollectApiCreditTxn($this->jobData);
                break;
            case 'smart_collect_upi_settle_to_primary':
                CashfreeAutoCollectHelper::smartCollectSettle2Primary($this->jobData);
                break;
            case 'smart_collect_fee':
                CashfreeAutoCollectHelper::autoCollectApiDebitTxn($this->jobData);
                break;
            case 'load_money_fee':
                TransactionHelper::txnForLoadMoneyRequest($this->jobData);
                break;
            case 'partner_van_eb_credit':
                EasebuzzInstaCollectHelper::ebInstaCollectCreditTxnJob($this->jobData);
            case 'partner_van_ob_credit':
                OBApiService::creditFundJobHandler($this->jobData);
                break;
            case 'partner_van_raz_credit':
                RazorPaySmartCollectHelper::razInstaCollectCreditTxnJob($this->jobData);
                break;
            case 'smart_collect_dispute':
                CashfreeAutoCollectHelper::smartCollectDisputedTxn($this->jobData);
                break;
            case 'aeps_txn_dispute':
                AEPSHelper::aepsDisputedTxn($this->jobData);
                break;
            case 'auto_settlement_order':
                $AutoSettlementHelper = new AutoSettlementHelper($this->userId);
                $AutoSettlementHelper->autoSettlement();
                break;
            default:
                break;
        }
    }
}
