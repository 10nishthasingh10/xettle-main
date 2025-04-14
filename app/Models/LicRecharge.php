<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LicRecharge extends Model
{
    use HasFactory;
    protected $table = 'lic_recharge';
   
    protected $fillable = [
        'user_id', 'service_id', 'integration_id', 'amount', 'description', 'customer_ref_id', 'ad1', 'cn', 'op', 'txn_id', 'original_order_id', 'bank_txn_id', 'fee', 'tax', 'ad2', 'ad3', 'total_amount', 'status','response','created_at','updated_at'
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
