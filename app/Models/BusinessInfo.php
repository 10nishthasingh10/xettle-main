<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BusinessInfo extends Model
{
    protected $hidden = ['updated_at'];

    protected $fillable = [
        'user_id', 'name', 'mobile', 'email', 'business_name', 'business_pan', 'business_type', 'business_category_id', 'business_subcategory_id', 'mcc', 'pan_number',
        'pan_owner_name', 'billing_label', 'address', 'pincode', 'city', 'state', 'web_url', 'app_url', 'gstin', 'beneficiary_name', 'ifsc', 'account_number',
        'aadhar_number', 'business_registration_proof', 'pan_doc', 'is_kyc_updated', 'is_bank_updated', 'acc_manager_id', 'acc_coordinator_id','business_description'
    ];


    public function userInfo()
    {
        return $this->hasOne(User::class, 'id', 'user_id');
    }
}
