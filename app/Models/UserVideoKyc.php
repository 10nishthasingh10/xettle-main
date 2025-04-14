<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserVideoKyc extends Model
{
    protected $table = 'user_video_kyc';


    protected $fillable = [
        'user_id', 'kyc_text', 'video_path', 'status', 'status_changed_at'
    ];


    public function userInfo()
    {
        return $this->hasOne(User::class, 'id', 'user_id');
    }


    public function businessName()
    {
        return $this->hasOne(BusinessInfo::class, 'user_id', 'user_id')
            ->select(
                'user_id',
                'business_name',
                'business_name_from_pan',
                'business_type',
                'business_pan',
                'pan_number',
                'pan_owner_name',
                'aadhar_number',
                'aadhaar_name',
                // 'name',
                // 'mobile',
                // 'email',
                'is_kyc_updated'
            );
    }
}
