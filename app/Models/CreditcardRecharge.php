<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CreditcardRecharge extends Model
{
    use HasFactory;
    protected $table = 'creditcard_recharge';
   
    protected $fillable = [
        'user_id', 'service_id', 'integration_id', 'amount', 'description', 'customer_ref_id', 'cn', 'op', 'cir', 'txn_id', 'original_order_id', 'total_amount', 'fee', 'tax', 'bank_txn_id', 'status','response','created_at','updated_at'
    ];

    public function user()
    {
        return $this->hasOne(User::class, 'id', 'user_id');
    }
    
    public function operator()
    {
        return $this->hasOne(Operator::class, 'id', 'operator_id');
    }    
}
