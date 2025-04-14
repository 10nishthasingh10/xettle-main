<?php

namespace App\Console\Commands;

use App\Helpers\CommonHelper;
use App\Helpers\TransactionHelper;
use App\Jobs\PrimaryFundCredit;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class SettleUpiStack2Primary extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'funds:upi_stack_settle_to_primary';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Settle Funds from UPI Stack Wallet to Primary Wallet';

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

        try {

            //check service is enable or not
            $globalConfig = DB::table('global_config')
                ->select('attribute_1', 'attribute_2')
                ->where('slug', 'upi_collect')
                ->first();

            $isServiceActive = false;
            $usersIdsArr = null;

            if (!empty($globalConfig)) {
                if ($globalConfig->attribute_1 == '1') {
                    $isServiceActive = true;
                } else if (!empty($globalConfig->attribute_2)) {
                    $usersIdsArr = explode(",", trim($globalConfig->attribute_2));
                    $isServiceActive = true;
                }
            }


            if ($isServiceActive) {

                $timestamp = date('Y-m-d H:i:s');

                $timestampBefore30Min = date('Y-m-d H:i:s', strtotime("-30 minute", strtotime($timestamp)));
                $str2TimestampBefore30Min = strtotime($timestampBefore30Min);

                $timestampBefore4Hours = date('Y-m-d H:i:s', strtotime("-9 hour", $str2TimestampBefore30Min));
                $str2TimestampBefore4Hours = strtotime($timestampBefore4Hours);

                // Storage::prepend('funds_cron_' . date('Y_m_d') . '.txt', 'CRON at: ' . date('Y-m-d H:i:s') . ", Timestamp: " . $timestamp . " - " . $timestampBefore30Min . " - " . $timestampBefore4Hours);

                $upiCallbacks = DB::table('upi_collects')
                    ->select(
                        'user_id',
                        DB::raw('SUM(amount) AS total_amount'),
                        // DB::raw('GROUP_CONCAT(id SEPARATOR ",") AS ids'),
                        DB::raw('COUNT(id) AS counts'),
                        'merchant_txn_ref_id'
                    );

                if (!empty($usersIdsArr)) {
                    $upiCallbacks->whereIn('user_id', $usersIdsArr);
                }

                $upiCallbacks = $upiCallbacks->where('is_trn_credited', '0')
                    ->where('status','success')
                    ->orderBy('id', 'ASC')
                    ->groupBy('user_id')
                    ->get();
                
                if (!$upiCallbacks->isEmpty()) {
                    foreach ($upiCallbacks as $row) {
                        
                        //check credit is enable or not
                        $isSettlementActive = CommonHelper::checkIsSettlementActive($row->user_id, 'upi_stack');

                        if ($isSettlementActive) {

                            $txnId = CommonHelper::getRandomString('txn', false);

                            DB::table('upi_collects')
                                //->whereIn('id', explode(',', $row->ids))
                                ->where('is_trn_credited', '0')
                                ->where('status','success')
                                ->update([
                                    'txn_id' => $txnId,
                                    'is_trn_credited' => '1',
                                    'trn_credited_at' => $timestamp
                                ]);

                            $row->timestamp = $timestamp;
                            $row->txn_id = $txnId;
                            $row->frequency = 'hourly';
                            
                            //PrimaryFundCredit::dispatch($row, 'upi_stack_settle_to_primary')->onQueue('primary_fund_queue');
                           
                            //check for transaction entry, if customer_ref_id exist
                            //$row = $row;
                            //print_r($row);
                            
                            /*$isTransactions = DB::table('transactions')
                                ->where('tr_reference', $row->timestamp)
                                ->count();
                           
                            if ($isTransactions > 0) {
                                return "Amounts already settled";
                            }*/
                             
                            // $rowId = $row->id;
                             
                            $txnId = $row->txn_id; //CommonHelper::getRandomString('txn', false);
                            $txnReferenceId = $row->txn_id;
                    
                            //getting service ID
                            $products = CommonHelper::getProductId('upi_collect', 'upi_collect');
                            $serviceId = $products->service_id;
                            
                            //fee and tax on fee calculation
                             $taxFee = (object) TransactionHelper::getFeesAndTaxes($products->product_id, $row->total_amount, $row->user_id);
                             
                             $feeRate = $taxFee->margin;
                    
                             $fee = round($taxFee->fee, 2);
                            $tax = round($taxFee->tax, 2);
                            // $feeRate = '';
                           /* $fee = $row->total_fee;
                            $tax = $row->total_tax;*/
                    
                            $afterFeeTaxAmount = $row->total_amount;
                    
                            $signedAfterFeeTaxAmount = ($afterFeeTaxAmount >= 0) ? '+' . $afterFeeTaxAmount : $afterFeeTaxAmount;
                            $txnNarration = $signedAfterFeeTaxAmount . ' credited to Primary Wallet.';
                           
                            try {
                                // Start a database transaction
                                DB::beginTransaction();
                           // print_r($row);
                            
                                // Your first UPDATE statement
                                $y = DB::table('upi_callbacks')
                                    ->where('user_id', $row->user_id)
                                    ->where('txn_id', $row->txn_id)
                                    ->update([
                                        'is_trn_credited' => '1',
                                        'trn_credited_at' => now()
                                    ]);
                            
                                // Your second UPDATE statement
                               DB::statement("UPDATE users
                                    SET transaction_amount = transaction_amount + :amount
                                    WHERE id = :user_id
                                ", [
                                    'amount' => $afterFeeTaxAmount,
                                    'user_id' => $row->user_id,
                                ]);
                            
                            
                                // Your INSERT statement
                                DB::table('transactions')->insert([
                                    'txn_id' => $row->txn_id,
                                    'txn_ref_id' => $row->merchant_txn_ref_id,
                                    'user_id' => $row->user_id,
                                    'order_id' => $row->counts,
                                    'account_number' => '100000000002', // Replace this with the actual value
                                    'tr_total_amount' => $row->total_amount,
                                    'tr_amount' => $row->total_amount,
                                    'tr_fee' => $fee,
                                    'tr_tax' => $tax,
                                    'tr_type' => 'cr',
                                    'tr_date' => now(),
                                    'tr_identifiers' => $row->frequency,
                                    'tr_narration' => $txnNarration,
                                    'closing_balance' => $row->total_amount,
                                    'tr_reference' => $row->timestamp,
                                    'service_id' => $serviceId,
                                    'udf1' => $row->counts,
                                    'udf2' => $row->frequency,
                                    'created_at' => now(),
                                    'updated_at' => now(),
                                ]);
                            
                                // Commit the transaction if all statements are successful
                                DB::commit();
                            
                                // Additional code after the transaction
                            
                            } catch (\Exception $e) {
                                // An error occurred, rollback the transaction
                                DB::rollback();
                            
                                // Log or handle the exception
                                \Log::error('Error executing SQL query: ' . $e->getMessage());
                            
                                // Optionally, throw the exception to halt execution
                                // throw $e;
                            }

                           
                        }
                    }

                    $this->info("Jobs Assigned");
                } else {
                    $this->info("No Balance found for settlement");
                }
            } else {
                $this->info("Service is Disabled");
            }
        } catch (Exception $e) {
            $this->error($e->getMessage());
        }
    }
}
