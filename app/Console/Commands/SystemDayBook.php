<?php

namespace App\Console\Commands;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use App\Models\Apilog;
use DB;
use Exception;
use Storage;
use Cashfree;
class SystemDayBook extends Command
{
	/**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'systemdaybook:update';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Day Book  update';

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
    	//$currDate =  '2021-11-13';
        //$nextDate = '2021-11-14';
        $currDate =  date('Y-m-d',strtotime("-1 days"));
        $nextDate = date('Y-m-d');
        $day_book_date = DB::table('global_config')->where('slug','day_book_date')->first();
        if(isset($day_book_date->attribute_2) && !empty($day_book_date->attribute_2))
        {
            $currDate =  date('Y-m-d',strtotime($day_book_date->attribute_2.'-1 days'));
            $nextDate = $day_book_date->attribute_2;
        }
        $resp['status'] = false;
        $resp['message'] = "No Record found";
        $i = 0;
        $j=0;
        
        
            	
                $dayBooks = DB::table('system_day_books')
                    ->where('created_at','like','%'. $nextDate.'%')
                    ->first();
                $orderAmount = self::getTotalOrder($currDate);
                
                //$totalInVan = DB::table('transactions')->select(DB::raw('sum(tr_amount) as totalAmount'))->where('tr_type','cr')->where('tr_identifiers','van_inward_credit')->where('created_at','like','%'.$currDate.'%')->first();
                //$totalVanTax = DB::table('transactions')->select(DB::raw('sum(tr_amount) as totalTax'))->where('tr_type','dr')->whereIn('tr_identifiers',array('van_fee','van_fee_tax'))->where('created_at','like','%'.$currDate.'%')->first();
                $UPIIn = DB::table('upi_callbacks')->select(DB::raw('round(sum(amount),2) as totalAmount'),DB::raw('round(sum(fee),2) as fee'),DB::raw('round(sum(tax),2) as tax'))->where('created_at','like','%'.$currDate.'%')->first();
                $UPICollectIn = DB::table('upi_collects')->select(DB::raw('sum(amount) as totalAmount'))->where('status','success')->where('trn_credited_at','like','%'.$currDate.'%')->first();
                $vanIn = DB::table('fund_receive_callbacks')->select(DB::raw('round(sum(amount),2) as totalAmount'),DB::raw('round(sum(fee),2) as fee'),DB::raw('round(sum(tax),2) as tax'))->where(DB::raw('date(created_at)'),$currDate)->first();
                $smart_collect_van = DB::table('cf_merchants_fund_callbacks')->select(DB::raw('round(sum(amount),2) as totalAmount'),DB::raw('round(sum(fee),2) as fee'),DB::raw('round(sum(tax),2) as tax'))->where('is_vpa','0')->where(DB::raw('date(created_at)'),$currDate)->first();
                $smart_collect_vpa = DB::table('cf_merchants_fund_callbacks')->select(DB::raw('round(sum(amount),2) as totalAmount'),DB::raw('round(sum(fee),2) as fee'),DB::raw('round(sum(tax),2) as tax'))->where('is_vpa','1')->where(DB::raw('date(created_at)'),$currDate)->first();
                
                
                $system_pool_account = self::getSystemAccount('system_pool_account');
                $system_fee_account = self::getSystemAccount('system_fee_account');
                $system_tax_account = self::getSystemAccount('system_tax_account');
                $processedOrder = self::getOrderStatusCount($currDate,'processed');
                $failedOrder = self::getOrderStatusCount($currDate,'failed');
                $processingOrder = self::getOrderStatusCount($currDate,'processing');
                $queuedOrder = self::getOrderStatusCount($currDate,'queued');
                $cancelledOrder = self::getOrderStatusCount($currDate,'cancelled');
                $reversedOrder = self::getOrderStatusCount($currDate,'reversed');
                if(isset($dayBooks) && !empty($dayBooks)) {
                	$j++;
                	DB::table('system_day_books')->where('created_at','like' ,'%'.$nextDate.'%')->update([
                		'rbi_account'=>$system_pool_account->account_number,
                        'rbi_account_amount' =>$system_pool_account->locked_amount + $system_pool_account->transaction_amount,
                        'fee_account' => $system_fee_account->account_number,
                        'fee_account_amount' => $system_fee_account->locked_amount + $system_fee_account->transaction_amount,
                        'tax_account' => $system_tax_account->account_number,
                        'tax_account_amount' => $system_tax_account->locked_amount + $system_tax_account->transaction_amount,
                		'van_in'=>isset($vanIn->totalAmount)?$vanIn->totalAmount:0,
                        'van_tax' => isset($vanIn->tax)?$vanIn->tax:0,
                        'van_fee' => isset($vanIn->fee)?$vanIn->fee:0,
                		'upi_in' => isset($UPIIn->totalAmount)?$UPIIn->totalAmount:0,
                        'upi_fee' => isset($UPIIn->fee)?$UPIIn->fee:0,
                        'upi_tax' => isset($UPIIn->tax)?$UPIIn->tax:0,
                        'smart_collect_vpa' => isset($smart_collect_vpa->totalAmount)?$smart_collect_vpa->totalAmount:0,
                        'smart_collect_vpa_fee' => isset($smart_collect_vpa->fee)?$smart_collect_vpa->fee:0,
                        'smart_collect_vpa_tax' => isset($smart_collect_vpa->tax)?$smart_collect_vpa->tax:0,
                        'upi_collect_in' => isset($UPICollectIn->totalAmount)?$UPICollectIn->totalAmount:0,
                		'order_processed_count' => isset($processedOrder->totalOrder)?$processedOrder->totalOrder:0,
                        'order_processed_amount' => isset($processedOrder->amount)?$processedOrder->amount:0,
                        'order_failed_count' => isset($failedOrder->totalOrder)?$failedOrder->totalOrder:0,
                        'order_failed_amount'=> isset($failedOrder->amount)?$failedOrder->amount:0,
                        'order_processing_count' => isset($processingOrder->totalOrder)?$processingOrder->totalOrder:0,
                        'order_processing_amount'=> isset($processingOrder->amount)?$processingOrder->amount:0,
                        'order_queued_amount'=> isset($queuedOrder->amount)?$queuedOrder->amount:0,
                        'order_queued_count'=> isset($queuedOrder->totalOrder)?$queuedOrder->totalOrder:0,
                        'order_cancelled_amount'=> isset($cancelledOrder->amount)?$cancelledOrder->amount:0,
                        'order_cancelled_count'=> isset($cancelledOrder->totalOrder)?$cancelledOrder->totalOrder:0,
                        'order_reversed_amount'=> isset($reversedOrder->amount)?$reversedOrder->amount:0,
                        'order_reversed_count'=> isset($reversedOrder->totalOrder)?$reversedOrder->totalOrder:0,
                        'total_payout_amount' => isset($orderAmount['total_payout']) ? $orderAmount['total_payout'] : 0,
                        'total_order_tax' => isset($orderAmount['total_tax']) ? $orderAmount['total_tax'] : 0,
                        'total_order_fee' => isset($orderAmount['total_fee']) ? $orderAmount['total_fee'] : 0,
                        'updated_at' => now()
                        
                	]);
                
                

                }
                else {
                	$i++;
                	DB::table('system_day_books')->insert([
                		'rbi_account'=>$system_pool_account->account_number,
                        'rbi_account_amount' =>$system_pool_account->locked_amount + $system_pool_account->transaction_amount,
                        'fee_account' => $system_fee_account->account_number,
                        'fee_account_amount' => $system_fee_account->locked_amount + $system_fee_account->transaction_amount,
                        'tax_account' => $system_tax_account->account_number,
                        'tax_account_amount' => $system_tax_account->locked_amount + $system_tax_account->transaction_amount,
                        'van_in'=>isset($vanIn->totalAmount)?$vanIn->totalAmount:0,
                        'van_tax' => isset($vanIn->tax)?$vanIn->tax:0,
                        'van_fee' => isset($vanIn->fee)?$vanIn->fee:0,
                        'upi_in' => isset($UPIIn->totalAmount)?$UPIIn->totalAmount:0,
                        'upi_fee' => isset($UPIIn->fee)?$UPIIn->fee:0,
                        'upi_tax' => isset($UPIIn->tax)?$UPIIn->tax:0,
                        'smart_collect_vpa' => isset($smart_collect_vpa->totalAmount)?$smart_collect_vpa->totalAmount:0,
                        'smart_collect_vpa_fee' => isset($smart_collect_vpa->fee)?$smart_collect_vpa->fee:0,
                        'smart_collect_vpa_tax' => isset($smart_collect_vpa->tax)?$smart_collect_vpa->tax:0,
                        'upi_collect_in' => isset($UPICollectIn->totalAmount)?$UPICollectIn->totalAmount:0,
                        'order_processed_count' => isset($processedOrder->totalOrder)?$processedOrder->totalOrder:0,
                        'order_processed_amount' => isset($processedOrder->amount)?$processedOrder->amount:0,
                        'order_failed_count' => isset($failedOrder->totalOrder)?$failedOrder->totalOrder:0,
                        'order_failed_amount'=> isset($failedOrder->amount)?$failedOrder->amount:0,
                        'order_processing_count' => isset($processingOrder->totalOrder)?$processingOrder->totalOrder:0,
                        'order_processing_amount'=> isset($processingOrder->amount)?$processingOrder->amount:0,
                        'order_queued_amount'=> isset($queuedOrder->amount)?$queuedOrder->amount:0,
                        'order_queued_count'=> isset($queuedOrder->totalOrder)?$queuedOrder->totalOrder:0,
                        'order_cancelled_amount'=> isset($cancelledOrder->amount)?$cancelledOrder->amount:0,
                        'order_cancelled_count'=> isset($cancelledOrder->totalOrder)?$cancelledOrder->totalOrder:0,
                        'order_reversed_amount'=> isset($reversedOrder->amount)?$reversedOrder->amount:0,
                        'order_reversed_count'=> isset($reversedOrder->totalOrder)?$reversedOrder->totalOrder:0,
                        'total_payout_amount' => isset($orderAmount['total_payout']) ? $orderAmount['total_payout'] : 0,
                        'total_order_tax' => isset($orderAmount['total_tax']) ? $orderAmount['total_tax'] : 0,
                        'total_order_fee' => isset($orderAmount['total_fee']) ? $orderAmount['total_fee'] : 0,

                		'created_at' => now()
                	]);

                	


                }

            
            $resp['status'] = true;
            $resp['message'] = $i." records inserted successfully.";
            $resp['message'] .= $j." records updated successfully.";
        

        $this->info($resp['message']);

    }


    public static function getTotalAmoutUsingMode( $date, $mode)
    {
        $resp = 0;
        
            $totalOrder = DB::table('orders')
            ->where('orders.created_at','like','%'.$date.'%')
            ->select('orders.id', DB::raw('SUM(amount) AS total'))
            //->where('orders.user_id','=',$userId)
            ->where('orders.mode','=',$mode)
            ->get();
            if(isset($totalOrder) && !empty($totalOrder) && count($totalOrder)) {
                if(isset($totalOrder[0]->total) && $totalOrder[0]->total > 0 ){
                    $resp = $totalOrder[0]->total;
                }
            }
        
            return $resp;
    }

    public static function getTotalOrder($date)
    {
        $resp['total_order'] = 0;
        $resp['total_payout'] = 0;
        $resp['total_tax'] = 0;
        $resp['total_fee'] = 0;
        	

            $totalPayout = DB::table('orders')
            ->where('orders.created_at','like','%'.$date.'%')
            ->select('orders.id',DB::raw('SUM(amount) AS amount'))
           // ->where('orders.user_id','=',$userId)
            //->where('orders.status','=','processed')
            ->get();
            if(isset($totalPayout) && !empty($totalPayout) && count($totalPayout)) {
                if(isset($totalPayout[0]->amount) && $totalPayout[0]->amount > 0 ){
                    $resp['total_payout'] = $totalPayout[0]->amount;
                }
            }

            $totalFee = DB::table('orders')
            ->where('orders.created_at','like','%'.$date.'%')
            ->select('orders.id',DB::raw('SUM(fee) AS fee'))
            //->where('orders.user_id','=',$userId)
            //->where('orders.status','=','processed')
            ->get();
            if(isset($totalFee) && !empty($totalFee) && count($totalFee)) {
                if(isset($totalFee[0]->fee) && $totalFee[0]->fee > 0 ){
                    $resp['total_fee'] = $totalFee[0]->fee;
                }
            }

            $totalTax = DB::table('orders')
            ->where('orders.created_at','like','%'.$date.'%')
            ->select('orders.id',DB::raw('SUM(tax) AS tax'))
            //->where('orders.user_id','=',$userId)
            //->where('orders.status','=','processed')
            ->get();
            if(isset($totalTax) && !empty($totalTax) && count($totalTax)) {
                if(isset($totalTax[0]->tax) && $totalTax[0]->tax > 0 ){
                    $resp['total_tax'] = $totalTax[0]->tax;
                }
            }

        
            return $resp;
    }

    public static function getOrderStatusCount($date,$status)
    {
    	DB::enableQueryLog();
        $totalOrder = DB::table('orders')
        ->select('status',DB::raw('count(id) as totalOrder'),DB::raw('sum(amount) as amount'))
        ->where('orders.created_at','like','%'.$date.'%')
        //->select('orders.id')
        //->where('orders.user_id','=',$userId)
        ->groupBy('orders.status')
        ->get();
        //dd(DB::getQueryLog());
        foreach($totalOrder as $val)
        {
        	if($val->status==$status)
        		return $val;
        }
        return 0;
        
    }

    public static function getSystemAccount($slug)
    {
        DB::enableQueryLog();
       $data = DB::table('global_config')->select('users.account_number','users.locked_amount','users.transaction_amount')->join('users','users.account_number','=','global_config.attribute_1')->where('slug',$slug)->first();
       // dd(DB::getQueryLog());
       // print_r($data);exit();
       return $data;
    }

    public static function getVanTransaction($identifiers,$type,$currDate)
    {
        
        $totalVanTax = DB::table('transactions')->select(DB::raw('sum(tr_amount) as totalAmount'))->where('tr_type',$type)->where('tr_identifiers',$identifiers)->where('created_at','like','%'.$currDate.'%')->first();
        return $totalVanTax;
        
    }
}