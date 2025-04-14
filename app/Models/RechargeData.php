<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RechargeData extends Model
{
    use HasFactory;

    protected $table = 'recharge_datas';
    public static $tableName = 'recharge_datas';

    public function user()
    {
        return $this->hasOne(User::class, 'id', 'user_id');
    }
    protected $fillable = [
        'user_id', 'service_id', 'integration_id', 'amount', 'description', 'customer_ref_id', 'cir', 'cn', 'op', 'txn_id', 'original_order_id', 'fee', 'tax', 'total_amount', 'bank_txn_id', 'status','response','created_at','updated_at'
    ];
    
    public function operator()
    {
        return $this->hasOne(Operator::class, 'id', 'operator_id');
    }

}
