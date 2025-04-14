<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DthRecharge extends Model
{
    use HasFactory;

    protected $table = 'dth_recharges';
   
    protected $fillable = [
        'user_id', 'service_id', 'integration_id', 'amount', 'order_id', 'description', 'customer_ref_id', 'cir', 'cn', 'op', 'txn_id', 'total_amount', 'original_order_id', 'bank_txn_id', 'fee', 'tax', 'req_id', 'status','response','created_at','updated_at'
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
