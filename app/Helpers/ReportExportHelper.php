<?php
namespace App\Helpers;


use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use App\Helpers\ExcelSheetsHelper;
use App\Models\Order;
use App\Models\UPICallback;
use App\Models\UPICollect;
use App\Models\FundReceiveCallback;
use App\Models\Transaction;
use App\Models\DayBook;
use App\Models\User;
use DB;

class ReportExportHelper implements WithMultipleSheets
{
	use Exportable;
	protected $endDate, $startDate, $userId, $reportName, $type;
    public function __construct($startDate, $endDate, $userId, $reportName, $type)
    {
        $this->startDate = $startDate;
		$this->endDate = $endDate;
		$this->userId = $userId;
		$this->reportName = $reportName;
		$this->type = $type;
    }

    /**
     * @return array
     */
    public function sheets(): array
    {
        $sheets = [];
        $heading='';
        $sqlModel='';
        $array = array('Summary', 'Orders', 'UPICallbacks', 'UPICollect', 'VAN', 'Transactions');
		if (in_array( $this->reportName, $array)) {
			$array = array($this->reportName);
		}else if($this->reportName=='UserTransactions')
		{
			$array = array('Payout','UserVan','UPIStack','SmartCollect');
		}
        foreach($array as $val){
        	switch($val){
        		case 'Summary':
        			$heading = array('name','user_id','primary_opening_balance','primary_closing_balance','payout_opening_balance','payout_closing_balance','van_in','van_out','upi_in','upi_collect_in','order_processed_count','order_processed_amount','total_tax','total_fee','order_failed_count','order_failed_amount','order_processing_count','order_processing_amount','order_cancelled_count','order_cancelled_amount','order_reversed_count','order_reversed_amount','record_date','created_at','updated_at');

        			$sqlModel = DayBook::query()->select('users.name','user_id','primary_opening_balance','primary_closing_balance','payout_opening_balance','payout_closing_balance','van_in','van_out','upi_in','upi_collect_in','order_processed_count','order_processed_amount','total_tax','total_fee','order_failed_count','order_failed_amount','order_processing_count','order_processing_amount','order_cancelled_count','order_cancelled_amount','order_reversed_count','order_reversed_amount','record_date','users.created_at','users.updated_at')->join('users','users.id','=','day_books.user_id');
					if ($this->type == 0) {
						$sqlModel = $sqlModel->whereIn('day_books.user_id', $this->userId);
					}
					$sqlModel->whereBetween('record_date', [$this->startDate.' 00:00:00', $this->endDate.' 23:59:59']);
					//->where(DB::raw('record_date'),$this->date);
        			$sheets[] = new ExcelSheetsHelper($heading,$sqlModel, $val);
	        		break;
        		case 'Orders':
	        		$heading = array('order_id','user_id','service_id','client_ref_id','contact_id','payout_id','batch_id','order_ref_id','amount','fee','tax','mode','narration','remark','status','status_response','bank_reference','cancellation_reason','cancelled_at','created_at','updated_at','trn_reflected','trn_reflected_at','trn_reversed','trn_reversed_at','txt_3');

	        		$sqlModel = Order::query()->select('order_id','user_id','service_id','client_ref_id','contact_id','payout_id','batch_id','order_ref_id','amount','fee','tax','mode','narration','remark','status','status_response','bank_reference','cancellation_reason','cancelled_at','created_at','updated_at','trn_reflected','trn_reflected_at','trn_reversed','trn_reversed_at','txt_3');
					if ($this->type == 0) {
						$sqlModel = $sqlModel->whereIn('orders.user_id', $this->userId);
					}
					$sqlModel->whereBetween('created_at', [$this->startDate.' 00:00:00', $this->endDate.' 23:59:59']);
					//->where(DB::raw('date(created_at)'),$this->date);
	        		$sheets[] = new ExcelSheetsHelper($heading,$sqlModel, $val);
	        		break;
        		case 'UPICallbacks':
        			$heading = array('id','user_id','payee_vpa','amount','txn_note','description','type','npci_txn_id','original_order_id','merchant_txn_ref_id','bank_txn_id','customer_ref_id','payer_vpa','payer_acc_name','payer_mobile','txn_date','is_trn_credited','webhook_sent_at','is_webhook_sent','created_at','updated_at');

        			$sqlModel = UPICallback::query()->select('id','user_id','payee_vpa','amount','txn_note','description','type','npci_txn_id','original_order_id','merchant_txn_ref_id','bank_txn_id','customer_ref_id','payer_vpa','payer_acc_name','payer_mobile','txn_date','is_trn_credited','webhook_sent_at','is_webhook_sent','created_at','updated_at');
					if ($this->type == 0) {
						$sqlModel = $sqlModel->whereIn('upi_callbacks.user_id', $this->userId);
					}
					$sqlModel->whereBetween('created_at', [$this->startDate.' 00:00:00', $this->endDate.' 23:59:59']);
					//->where(DB::raw('date(created_at)'),$this->date);
        			$sheets[] = new ExcelSheetsHelper($heading,$sqlModel, $val);
        			break;
    			case 'UPICollect':
        			$heading = array('id','user_id','customer_ref_id','merchant_txn_ref_id','bank_txn_id','original_order_id','amount','description','payee_vpa','txn_id','payer_vpa','upi_txn_id','txn_note','status','npci_txn_id','payer_acc_name','payer_mobile','txn_date','is_trn_credited','webhook_sent_at','is_webhook_sent','created_at','updated_at');

        			$sqlModel = UPICollect::query()->select('id','user_id','customer_ref_id','merchant_txn_ref_id','bank_txn_id','original_order_id','amount','description','payee_vpa','txn_id','payer_vpa','upi_txn_id','txn_note','status','npci_txn_id','payer_acc_name','payer_mobile','txn_date','is_trn_credited','webhook_sent_at','is_webhook_sent','created_at','updated_at')->where('status','success');
					if ($this->type == 0) {
						$sqlModel = $sqlModel->whereIn('upi_collects.user_id', $this->userId);
					}
					$sqlModel->whereBetween('created_at', [$this->startDate.' 00:00:00', $this->endDate.' 23:59:59']);
					//->where(DB::raw('date(created_at)'),$this->date);
        			$sheets[] = new ExcelSheetsHelper($heading,$sqlModel, $val);
        			break;
        		case 'VAN':
        			$heading = array('user_id','amount','utr','v_account_id','virtual_vpa_id','is_vpa','v_account_number','reference_id','email','phone','credit_ref_no','payment_time','webhook_sent_at','is_trn_credited','trn_credited_at','amount_collected','created_at','updated_at');
        			$sqlModel = FundReceiveCallback::query()->select('user_id','amount','utr','v_account_id','virtual_vpa_id','is_vpa','v_account_number','reference_id','email','phone','credit_ref_no','payment_time','webhook_sent_at','is_trn_credited','trn_credited_at','amount_collected','created_at','updated_at');
					if ($this->type == 0) {
						$sqlModel = $sqlModel->whereIn('fund_receive_callbacks.user_id', $this->userId);
					}
					
					$sqlModel->whereBetween('created_at', [$this->startDate.' 00:00:00', $this->endDate.' 23:59:59']);
					//->where(DB::raw('date(created_at)'),$this->date);
        			$sheets[] = new ExcelSheetsHelper($heading,$sqlModel, $val);
        			break;
        		case 'Transactions':
        			$heading = array('trans_id','txn_id','txn_ref_id','account_number','user_id','order_id','tr_type','tr_amount','tr_total_amount','tr_fee','tr_tax','closing_balance','tr_date','tr_identifiers','udf1','udf2','udf3','udf4','created_at','updated_at');
        			$sqlModel = Transaction::query()->select('trans_id','txn_id','txn_ref_id','account_number','user_id','order_id','tr_type','tr_amount','tr_total_amount','tr_fee','tr_tax','closing_balance','tr_date','tr_identifiers','udf1','udf2','udf3','udf4','created_at','updated_at');
					if ($this->type == 0) {
						$sqlModel = $sqlModel->whereIn('transactions.user_id', $this->userId);
					}
					$sqlModel->whereBetween('created_at', [$this->startDate.' 00:00:00', $this->endDate.' 23:59:59']);
					//->where(DB::raw('date(created_at)'),$this->date);
        			$sheets[] = new ExcelSheetsHelper($heading,$sqlModel, $val);
        			break;
        		case 'Payout':
	        		$heading = array('Name','Email','Amount','Fee','Tax','Transactions');
	        		
	        		$sqlModel = User::query()->select('name','email',DB::raw('sum(orders.amount)'),DB::raw('sum(orders.fee)'),DB::raw('sum(orders.tax)'),DB::raw('count(*)'))->join('orders','orders.user_id','=','users.id')->where('orders.status','processed')->groupBy('orders.user_id');
					if ($this->type == 0) {
						$sqlModel = $sqlModel->whereIn('orders.user_id', $this->userId);
					}
					$sqlModel->whereBetween('orders.created_at', [$this->startDate.' 00:00:00', $this->endDate.' 23:59:59']);
					//->where(DB::raw('date(created_at)'),$this->date);
	        		$sheets[] = new ExcelSheetsHelper($heading,$sqlModel, $val);
	        		break;
	        	case 'UPIStack':
	        		$heading = array('Name','Email','Amount','Transactions');
	        		
	        		$sqlModel = UPICallback::query()
                    ->select('users.name', 'users.email', DB::raw("sum(upi_callbacks.amount) AS tot_amount, count(upi_callbacks.id) AS tot_txn") )
                    ->leftJoin('users', 'upi_callbacks.user_id', 'users.id')
                    ->where('upi_callbacks.type', 'PAYMENT_RECV')->groupBy('upi_callbacks.user_id');
					if ($this->type == 0) {
						$sqlModel = $sqlModel->whereIn('upi_callbacks.user_id', $this->userId);
					}
					$sqlModel->whereBetween('upi_callbacks.created_at', [$this->startDate.' 00:00:00', $this->endDate.' 23:59:59']);
					//->where(DB::raw('date(created_at)'),$this->date);
	        		$sheets[] = new ExcelSheetsHelper($heading,$sqlModel, $val);
	        		break;
	        	case 'UserVan':
	        		$heading = array('Name','Email','Amount','Transactions');
	        		
	        		$sqlModel = User::query()->select('users.name','users.email',DB::raw('sum(amount)'),DB::raw('count(*)'))->join('fund_receive_callbacks','fund_receive_callbacks.user_id','=','users.id')->groupBy('fund_receive_callbacks.user_id');
					if ($this->type == 0) {
						$sqlModel = $sqlModel->whereIn('fund_receive_callbacks.user_id', $this->userId);
					}
					$sqlModel->whereBetween('fund_receive_callbacks.created_at', [$this->startDate.' 00:00:00', $this->endDate.' 23:59:59']);
					//->where(DB::raw('date(created_at)'),$this->date);
	        		$sheets[] = new ExcelSheetsHelper($heading,$sqlModel, $val);
	        		break;
	        	case 'SmartCollect':
	        		$heading = array('Name','Email','Amount','Transactions');
	        		
	        		$sqlModel = User::query()->select('users.name','users.email',DB::raw('sum(amount)'),DB::raw('count(*)'))->join('cf_merchants_fund_callbacks','cf_merchants_fund_callbacks.user_id','=','users.id')->groupBy('cf_merchants_fund_callbacks.user_id');
					if ($this->type == 0) {
						$sqlModel = $sqlModel->whereIn('cf_merchants_fund_callbacks.user_id', $this->userId);
					}
					$sqlModel->whereBetween('cf_merchants_fund_callbacks.created_at', [$this->startDate.' 00:00:00', $this->endDate.' 23:59:59']);
					//->where(DB::raw('date(created_at)'),$this->date);
	        		$sheets[] = new ExcelSheetsHelper($heading,$sqlModel, $val);
	        		break;
        	}
        }
        return $sheets;
    }
}