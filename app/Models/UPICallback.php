<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UPICallback extends Model
{
    use HasFactory;

    protected $table = 'upi_callbacks';

    protected $with = ['merchant'];
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id', 'payee_vpa', 'amount', 'txn_note', 'description', 'type', 'npci_txn_id', 'original_order_id', 'merchant_txn_ref_id', 'bank_txn_id', 'code', 'response_code', 'customer_ref_id', 'payer_vpa', 'payer_acc_name', 'payer_mobile', 'payer_acc_no', 'payer_ifsc', 'txn_date'
    ];


    public function User()
    {
        return $this->belongsTo(User::class);
    }

    public function merchant()
    {
        return $this->belongsTo(\App\Models\UPIMerchant::class, 'payee_vpa','merchant_virtual_address')->select('merchant_business_name','merchant_virtual_address');
    }
}
