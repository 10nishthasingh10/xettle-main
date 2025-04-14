<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model;


class AutoCollectCallback extends Model
{
    protected $table = 'cf_merchants_fund_callbacks';

    // protected $with = ['userInfo'];

    /**
     * Belongs To Relationship
     */
    public function userInfo()
    {
        return $this->belongsTo(User::class, 'user_id', 'id')->select('id', 'name', 'email', 'mobile');
    }


    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
