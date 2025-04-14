<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RetaillerBill extends Model
{
    use HasFactory;
    protected $table = 'retailler_bill';
   
    protected $fillable = [
        'user_id', 'service_id', 'integration_id', 'amount', 'description', 'customer_ref_id', 'cir', 'cn', 'op', 'txn_id', 'original_order_id', 'bank_txn_id', 'adParams', 'status','response','created_at','updated_at'
    ];
}
