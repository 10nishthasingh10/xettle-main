<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class FundReceiveCallback extends Model
{
    //fund_receive_callbacks


    protected $with = ['userInfo'];


    /**
     * Belongs To Relationship
     */
    public function userInfo()
    {
        return $this->belongsTo(User::class, 'user_id', 'id')->select('id', 'name', 'email', 'mobile');
    }

    public function User()
    {
        return $this->belongsTo(User::class);

    }
}
