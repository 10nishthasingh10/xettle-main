<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Agent extends Model
{
    protected $hidden = ['updated_at'];

    public function businessName()
    {
        return $this->hasOne(BusinessInfo::class, 'user_id', 'user_id')->select('user_id', 'business_name', 'business_type', 'name', 'mobile', 'email');
    }

}
