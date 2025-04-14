<?php

namespace App\Models;
use Cashfree;
use CommonHelper;
use Illuminate\Database\Eloquent\Model;

use App\Models\Contact;

class Transaction extends Model
{
    protected $fillable = [
        'trans_id', 'txn_id', 'txn_ref_id', 'account_number', 'user_id', 'order_id', 'tr_total_amount', 'tr_amount', 'tr_fee', 'tr_commission', 'tr_tds', 'tr_tax', 'tr_date', 'tr_type', 'tr_identifiers', 'service_id', 'opening_balance', 'closing_balance', 'tr_narration', 'tr_reference', 'remarks', 'udf1', 'udf2', 'udf3', 'udf4', 'udf5', 'fee_rate', 'created_at','updated_at'
    ];

    protected $with = ['User', 'ServiceName'];
    public static function listing($type='array',$keys='*',$where='',$order_by='id-desc',$limit=10){
	    $table_name = self::select($keys);

	    if($where){
	        $table_name->whereRaw($where);
	    }

	    if(!empty($order_by)){
	        $order_by = explode('-', $order_by);
	        $table_name->orderBy($order_by[0],$order_by[1]);
	    }
       
	    if($type === 'array'){
            $list = $table_name->get();
            return json_decode(json_encode($list), true );
        }else if($type === 'obj'){
            return $table_name->limit($limit)->get();                
        }else if($type === 'single'){
            return $table_name->get()->first();
        }else{
            return $table_name->limit($limit)->get();
        }
	}

    public static function amtTrnsfToLockAcc($product_id,$amount,$user_id,$service_id)
    {
        $remarks = '';

       

        $getServiceAcc = UserService::where('user_id', $user_id)->where('service_id', $service_id)->first();
        if($getServiceAcc) {
            $userClosingBalance = $getServiceAcc->transaction_amount - $amount;
            $txn = CommonHelper::getRandomString('txn', true, 10);

            /* Order Amount Transaction */
            $trans = new Transaction;
            $trans->trans_id = $txn;
            $trans->user_id = $user_id;
            $trans->service_id = $service_id;
            $trans->account_number = $getServiceAcc->service_account_number;
            $trans->tr_amount = $amount;
            $trans->tr_date = date('Y-m-d H:i:s');
            $trans->tr_type = 'dr';
            $trans->tr_identifiers = 'order_create';
            $trans->tr_narration = $amount.' debited from transaction amount' ;
            $trans->opening_balance = $getServiceAcc->transaction_amount;
            $trans->closing_balance = $userClosingBalance;
            $trans->remarks = $remarks;
            $trans->save();

            $trans = new Transaction;
            $trans->trans_id = $txn;
            $trans->user_id = $getServiceAcc->user_id;
            $trans->service_id = $service_id;
            $trans->account_number = $getServiceAcc->service_account_number;
            $trans->tr_amount = $amount;
            $trans->tr_date = date('Y-m-d H:i:s');
            $trans->tr_type = 'cr';
            $trans->tr_identifiers = 'order_create';
            $trans->tr_narration = $amount.' credited to locked amount ' ;
            $trans->opening_balance = $getServiceAcc->locked_amount;
            $trans->closing_balance = $getServiceAcc->locked_amount + ($amount);
            $trans->remarks = $remarks;
            $trans->save();

            $getServiceAcc->transaction_amount = $userClosingBalance;
            $getServiceAcc->locked_amount = $getServiceAcc->locked_amount - ($amount);
            $getServiceAcc->save();
        }
    }

    public function User()
    {
        return $this->belongsTo(User::class);
    }

    public function ServiceName()
    {
        return $this->belongsTo(Service::class, 'service_id', 'service_id');
    }
}
