<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LoadMoneyRequest extends Model
{
    protected $table = 'load_money_request';

    protected $with = ['userNameEmail', 'changeBy'];
    public function userNameEmail()
    {
        return $this->belongsTo(User::class, 'user_id')->select('id', 'name', 'email');
    }

    public function changeBy()
    {
        return $this->belongsTo(User::class, 'admin_id')->select('id', 'name', 'email');
    }
}
