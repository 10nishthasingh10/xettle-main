<?php

namespace App\Console\Commands;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use App\Models\Apilog;
use DB;
use Exception;
use Storage;
use Cashfree;
class DayBook extends Command
{
	/**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'daybook:update';

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
        $currDateTime =  date('Y-m-d H:i:s',strtotime("-1 days"));
        $nextDate = date('Y-m-d');
        $nextDayPlus = date('Y-m-d',strtotime("+1 days"));
        $nextDateTime = date('Y-m-d H:i:s',strtotime("+1 days"));
        $day_book_date = DB::table('global_config')->where('slug','day_book_date')->first();
        if(isset($day_book_date->attribute_1) && !empty($day_book_date->attribute_1))
        {
            $currDate =  date('Y-m-d',strtotime($day_book_date->attribute_1.'-1 days'));
            $currDateTime =  date('Y-m-d H:i:s',strtotime($day_book_date->attribute_1."-1 days"));
            $nextDate = $day_book_date->attribute_1;
            $nextDayPlus = date('Y-m-d',strtotime($day_book_date->attribute_1."+1 days"));
            $nextDateTime = date('Y-m-d H:i:s',strtotime($day_book_date->attribute_1."+1 days"));
        }
        $resp['status'] = false;
        $resp['message'] = "No Record found";
        $i = 0;
        $j=0;
        
        
            $users = DB::table('users')
            ->select('id','transaction_amount','locked_amount')
            ->where('is_active','1')
            ->where('is_admin','0')
            ->orderBy('id','asc')
            ->get();
            
            foreach($users as $user) {

            	$balance = DB::table('user_services')
                    ->select('transaction_amount','locked_amount')
                    ->where('user_id', $user->id)
                    ->where('service_id', PAYOUT_SERVICE_ID)
                    ->first();
                
                $dayBooks = DB::table('day_books')
                    ->where('user_id', $user->id)
                    ->where('record_date','like','%'.$currDate.'%')
                    ->first();
                    echo '<pre>';
                    print_r($dayBooks);
                $orderAmount = self::getTotalOrder($user->id, $currDate);
                
                $totalInVan = DB::table('transactions')->select(DB::raw('sum(tr_total_amount) as totalAmount'))->where('user_id',$user->id)->where('tr_type','cr')->where('tr_identifiers','van_inward_credit')->where('created_at','like','%'.$currDate.'%')->first();

                $totalVanTax = DB::table('transactions')->select(DB::raw('sum(tr_fee+tr_fee) as totalTax'))->where('user_id',$user->id)->where('tr_identifiers','van_inward_credit')->where('created_at','like','%'.$currDate.'%')->first();

                $smart_collect_van = DB::table('transactions')->select(DB::raw('sum(tr_total_amount) as totalAmount'))->where('user_id',$user->id)->where('tr_type','cr')->where('tr_identifiers','smart_collect_van')->where('created_at','like','%'.$currDate.'%')->first();
                $smart_collect_VanTax = DB::table('transactions')->select(DB::raw('sum(tr_fee+tr_fee) as totalTax'))->where('user_id',$user->id)->where('tr_identifiers','smart_collect_van')->where('created_at','like','%'.$currDate.'%')->first();

                $UPIIn = DB::table('upi_callbacks')->select(DB::raw('sum(amount) as totalAmount'))->where('user_id',$user->id)->where('created_at','like','%'.$currDate.'%')->first();
                $smart_collect_vpa = DB::table('transactions')->select(DB::raw('sum(tr_total_amount) as totalAmount'))->where('user_id',$user->id)->where('tr_type','cr')->where('tr_identifiers','smart_collect_vpa')->where('created_at','like','%'.$currDate.'%')->first();
                $smart_collect_vpaTax = DB::table('transactions')->select(DB::raw('sum(tr_fee+tr_fee) as totalTax'))->where('user_id',$user->id)->where('tr_identifiers','smart_collect_vpa')->where('created_at','like','%'.$currDate.'%')->first();
                $UPICollectIn = DB::table('upi_collects')->select(DB::raw('sum(amount) as totalAmount'))->where('user_id',$user->id)->where('status','success')->where('created_at','like','%'.$currDate.'%')->first();
                //print_r($dayBooks);
                $processedOrder = self::getOrderStatusCount($user->id,$currDate,'processed');
                $failedOrder = self::getOrderStatusCount($user->id,$currDate,'failed');
                $processingOrder = self::getOrderStatusCount($user->id,$currDate,'processing');
                $queuedOrder = self::getOrderStatusCount($user->id,$currDate,'queued');
                $cancelledOrder = self::getOrderStatusCount($user->id,$currDate,'cancelled');
                $reversedOrder = self::getOrderStatusCount($user->id,$currDate,'reversed');
                if(isset($dayBooks) && !empty($dayBooks)) {
                	$j++;
                	DB::table('day_books')->where('user_id', $user->id)->where('record_date','like','%'.$currDate.'%')->update([
                		//'primary_opening_balance'=>$user->transaction_amount + $user->locked_amount,
                		'primary_closing_balance'=>!empty($user)?$user->transaction_amount + $user->locked_amount:0,
                		//'payout_opening_balance'=>isset($balance->transaction_amount) ? $balance->transaction_amount : 0,
                		'payout_closing_balance'=>isset($balance->transaction_amount) ? $balance->transaction_amount : 0,
                        'payout_closing_locked_amount'=>isset($balance->locked_amount)?$balance->locked_amount:0,
                		'van_in'=>isset($totalInVan->totalAmount)?$totalInVan->totalAmount:0,
                        'smart_collect_van'=>isset($smart_collect_van->totalAmount)?$smart_collect_van->totalAmount:0,
                		'upi_in'=>isset($UPIIn->totalAmount)?$UPIIn->totalAmount:0,
                        'smart_collect_vpa'=>isset($smart_collect_vpa->totalAmount)?$smart_collect_vpa->totalAmount:0,
                        'upi_collect_in' => isset($UPICollectIn->totalAmount)?$UPICollectIn->totalAmount:0,
                		'van_out'=>isset($totalVanTax->totalTax)?$totalVanTax->totalTax:0,
                        'smart_collect_van_tax'=>isset($smart_collect_VanTax->totalAmount)?$smart_collect_VanTax->totalAmount:0,
                        'smart_collect_vpa_tax'=>isset($smart_collect_vpaTax->totalAmount)?$smart_collect_vpaTax->totalAmount:0,
                		'order_processed_count' => isset($processedOrder->totalOrder)?$processedOrder->totalOrder:0,
                		'order_failed_count' => isset($failedOrder->totalOrder)?$failedOrder->totalOrder:0,
                        'order_failed_amount'=> isset($failedOrder->amount)?$failedOrder->amount:0,
                        'order_processing_count' => isset($processingOrder->totalOrder)?$processingOrder->totalOrder:0,
                        'order_processing_amount'=> isset($processingOrder->amount)?$processingOrder->amount:0,
                        'order_processed_amount' => isset($orderAmount['total_payout']) ? $orderAmount['total_payout'] : 0,
                        'total_tax' => isset($orderAmount['total_tax']) ? $orderAmount['total_tax'] : 0,
                        'total_fee' => isset($orderAmount['total_fee']) ? $orderAmount['total_fee'] : 0,
                        'order_queued_amount'=> isset($queuedOrder->amount)?$queuedOrder->amount:0,
                        'order_queued_count'=> isset($queuedOrder->amount)?$queuedOrder->amount:0,
                        'order_cancelled_amount'=> isset($cancelledOrder->amount)?$cancelledOrder->amount:0,
                        'order_cancelled_count'=> isset($cancelledOrder->amount)?$cancelledOrder->amount:0,
                        'order_reversed_amount'=> isset($reversedOrder->amount)?$reversedOrder->amount:0,
                        'order_reversed_count'=> isset($reversedOrder->amount)?$reversedOrder->amount:0,
                        // 'payout_IMPS' => self::getTotalAmoutUsingMode($user->id, $currDate, 'IMPS'),
                        // 'payout_NEFT' => self::getTotalAmoutUsingMode($user->id, $currDate, 'NEFT'),
                        // 'payout_RTGS' => self::getTotalAmoutUsingMode($user->id, $currDate, 'RTGS'),
                        'updated_at' => now()
                	]);
                $nextdayBooks = DB::table('day_books')
                    ->where('user_id', $user->id)
                    ->where('record_date','like','%'.$nextDate.'%')
                    ->count();
                 if(isset($nextdayBooks) && ($nextdayBooks==0)) {
                     DB::table('day_books')->insert([
                     'user_id'=> $user->id,
                     'primary_opening_balance'=>!empty($user)?$user->transaction_amount + $user->locked_amount:0,
                     'primary_closing_balance'=>0,
                     'payout_opening_balance'=>isset($balance->transaction_amount) ? $balance->transaction_amount: 0,
                     'payout_opening_locked_amount'=>isset($balance->locked_amount)?$balance->locked_amount:0,
                     'payout_closing_balance'=> 0,
                     'record_date'=>$nextDate,
                     'created_at' => now()
                 ]);

                // 	DB::table('day_books')->where('user_id', $user->id)->where('created_at' , $nextDate)->update([
                // 		'primary_opening_balance'=>$dayBooks->primary_closing_balance,
                // 		'primary_closing_balance'=>0,
                // 		'payout_opening_balance'=>isset($balance->transaction_amount) ? $balance->transaction_amount + $balance->locked_amount : 0,
                // 		'payout_closing_balance'=>0,
                // 		'updated_at' => now(),

                // 	]);
                 }
                 //else
                 //{
                // 	DB::table('day_books')->insert([
                // 		'user_id'=> $user->id,
                // 		'primary_opening_balance'=>$dayBooks->primary_closing_balance,
                // 		'primary_closing_balance'=>0,
                // 		'payout_opening_balance'=>isset($balance->transaction_amount) ? $balance->transaction_amount + $balance->locked_amount : 0,
                // 		'payout_closing_balance'=> 0,
                // 		'created_at' => $nextDateTime
                // 	]);
                // }

                }
                else {
                	$i++;
                    $currDayBooks = DB::table('day_books')
                    ->where('user_id', $user->id)
                    ->where('record_date','like','%'.$nextDate.'%')
                    ->first();
                    if(isset($dayBooks) && !empty($dayBooks)) {
                    	DB::table('day_books')->insert([
                    		'user_id'=> $user->id,
                    		'primary_opening_balance'=>!empty($user)?$user->transaction_amount + $user->locked_amount:0,
                    		'primary_closing_balance'=>!empty($user)?$user->transaction_amount + $user->locked_amount:0,
                    		'payout_opening_balance'=>isset($balance->transaction_amount) ? $balance->transaction_amount : 0,
                            'payout_opening_locked_amount'=>isset($balance->locked_amount)?$balance->locked_amount:0,
                    		'payout_closing_balance'=>isset($balance->transaction_amount) ? $balance->transaction_amount : 0,
                            'payout_closing_locked_amount'=>isset($balance->locked_amount)?$balance->locked_amount:0,
                    		'van_in'=>isset($totalInVan->totalAmount)?$totalInVan->totalAmount:0,
                            'smart_collect_vpa'=>isset($smart_collect_vpa->totalAmount)?$smart_collect_vpa->totalAmount:0,
                            'smart_collect_van'=>isset($smart_collect_van->totalAmount)?$smart_collect_van->totalAmount:0,
                    		'upi_in'=>isset($UPIIn->totalAmount)?$UPIIn->totalAmount:0,
                            'upi_collect_in' => isset($UPICollectIn->totalAmount)?$UPICollectIn->totalAmount:0,
                    		'van_out'=>isset($totalVanTax->totalTax)?$totalVanTax->totalTax:0,
                            'smart_collect_van_tax'=>isset($smart_collect_VanTax->totalAmount)?$smart_collect_VanTax->totalAmount:0,
                            'smart_collect_vpa_tax'=>isset($smart_collect_vpaTax->totalAmount)?$smart_collect_vpaTax->totalAmount:0,
                    		'order_processed_count' => isset($processedOrder->totalOrder)?$processedOrder->totalOrder:0,
                            'order_failed_count' => isset($failedOrder->totalOrder)?$failedOrder->totalOrder:0,
                            'order_failed_amount'=> isset($failedOrder->amount)?$failedOrder->amount:0,
                            'order_processing_count' => isset($processingOrder->totalOrder)?$processingOrder->totalOrder:0,
                            'order_processing_amount'=> isset($processingOrder->amount)?$processingOrder->amount:0,
                            'order_processed_amount' => isset($orderAmount['total_payout']) ? $orderAmount['total_payout'] : 0,
                            'total_tax' => isset($orderAmount['total_tax']) ? $orderAmount['total_tax'] : 0,
                            'total_fee' => isset($orderAmount['total_fee']) ? $orderAmount['total_fee'] : 0,
                            'order_queued_amount'=> isset($queuedOrder->amount)?$queuedOrder->amount:0,
                            'order_queued_count'=> isset($queuedOrder->amount)?$queuedOrder->amount:0,
                            'order_cancelled_amount'=> isset($cancelledOrder->amount)?$cancelledOrder->amount:0,
                            'order_cancelled_count'=> isset($cancelledOrder->amount)?$cancelledOrder->amount:0,
                            'order_reversed_amount'=> isset($reversedOrder->amount)?$reversedOrder->amount:0,
                            'order_reversed_count'=> isset($reversedOrder->amount)?$reversedOrder->amount:0,
                            'record_date'=>$currDate,
                            // 'payout_IMPS' => self::getTotalAmoutUsingMode($user->id, $currDate, 'IMPS'),
                            // 'payout_NEFT' => self::getTotalAmoutUsingMode($user->id, $currDate, 'NEFT'),
                            // 'payout_RTGS' => self::getTotalAmoutUsingMode($user->id, $currDate, 'RTGS'),

                    		'created_at' => now()
                    	]);
                    }
                    if(!isset($currDayBooks) && empty($currDayBooks)) {
                    	DB::table('day_books')->insert([
                    		'user_id'=> $user->id,
                    		'primary_opening_balance'=>$user->transaction_amount + $user->locked_amount,
                    		'primary_closing_balance'=>0,
                    		'payout_opening_balance'=>isset($balance->transaction_amount) ? $balance->transaction_amount : 0,
                            'payout_opening_locked_amount'=>isset($balance->locked_amount)?$balance->locked_amount:0,
                    		'payout_closing_balance'=> 0,
                            'payout_closing_locked_amount'=>0,
                            'record_date'=>$nextDate,
                    		'created_at' => now(),
                    		// 'van_in'=>$totalInVan->totalAmount,
                    		// 'upi_in'=>$UPIIn + $UPICollectIn,
                    		// 'van_out'=>$totalVanTax->totalTax,
                    	]);
                    }


                }

            }
            $resp['status'] = true;
            $resp['message'] = $i." records inserted successfully.";
            $resp['message'] .= $j." records updated successfully.";
        

        $this->info($resp['message']);

    }


    public static function getTotalAmoutUsingMode($userId, $date, $mode)
    {
        $resp = 0;
        if($userId) {
            $totalOrder = DB::table('orders')
            ->where('orders.created_at','like','%'.$date.'%')
            ->select('orders.id', DB::raw('SUM(amount) AS total'))
            ->where('orders.user_id','=',$userId)
            ->where('orders.mode','=',$mode)
            ->get();
            if(isset($totalOrder) && !empty($totalOrder) && count($totalOrder)) {
                if(isset($totalOrder[0]->total) && $totalOrder[0]->total > 0 ){
                    $resp = $totalOrder[0]->total;
                }
            }
        }
            return $resp;
    }

    public static function getTotalOrder($userId, $date)
    {
        $resp['total_order'] = 0;
        $resp['total_payout'] = 0;
        $resp['total_tax'] = 0;
        $resp['total_fee'] = 0;
        if($userId) {
        	
        	

            $totalPayout = DB::table('orders')
            ->where('orders.created_at','like','%'.$date.'%')
            ->select('orders.id',DB::raw('SUM(amount) AS amount'))
            ->where('orders.user_id','=',$userId)
            ->where('orders.status','=','processed')
            ->get();
            if(isset($totalPayout) && !empty($totalPayout) && count($totalPayout)) {
                if(isset($totalPayout[0]->amount) && $totalPayout[0]->amount > 0 ){
                    $resp['total_payout'] = $totalPayout[0]->amount;
                }
            }

            $totalFee = DB::table('orders')
            ->where('orders.created_at','like','%'.$date.'%')
            ->select('orders.id',DB::raw('SUM(fee) AS fee'))
            ->where('orders.user_id','=',$userId)
            ->where('orders.status','=','processed')
            ->get();
            if(isset($totalFee) && !empty($totalFee) && count($totalFee)) {
                if(isset($totalFee[0]->fee) && $totalFee[0]->fee > 0 ){
                    $resp['total_fee'] = $totalFee[0]->fee;
                }
            }

            $totalTax = DB::table('orders')
            ->where('orders.created_at','like','%'.$date.'%')
            ->select('orders.id',DB::raw('SUM(tax) AS tax'))
            ->where('orders.user_id','=',$userId)
            ->where('orders.status','=','processed')
            ->get();
            if(isset($totalTax) && !empty($totalTax) && count($totalTax)) {
                if(isset($totalTax[0]->tax) && $totalTax[0]->tax > 0 ){
                    $resp['total_tax'] = $totalTax[0]->tax;
                }
            }

        }
            return $resp;
    }

    public static function getOrderStatusCount($userId, $date,$status)
    {
    	DB::enableQueryLog();
        $totalOrder = DB::table('orders')
        ->select('status',DB::raw('count(id) as totalOrder'),DB::raw('sum(amount) as amount'))
        ->where('orders.created_at','like','%'.$date.'%')
        //->select('orders.id')
        ->where('orders.user_id','=',$userId)
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
}