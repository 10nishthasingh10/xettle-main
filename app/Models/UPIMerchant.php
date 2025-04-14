<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UPIMerchant extends Model
{
    use HasFactory;

    protected $table = 'upi_merchants';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'root_type', 'user_id', 'merchant_business_name', 'merchant_virtual_address', 'request_url', 'pan_no', 'contact_email', 'gstn', 'merchant_business_type', 'per_day_txn_count', 'per_day_txn_lmt', 'per_day_txn_amt', 'mobile', 'address', 'state', 'city', 'pin_code', 'sub_merchant_id', 'merchant_txn_ref_id', 'mcc', 'request_id', 'crt_date'
    ];
}
