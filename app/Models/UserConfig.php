<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserConfig extends Model
{
    protected $table ='user_config';
    protected $hidden = ['updated_at'];


    public function userNameEmail()
    {
        return $this->belongsTo(User::class, 'user_id', 'id')->select('id', 'name', 'email');
    }


    public function schemesName()
    {
        return $this->belongsTo(CustomScheme::class, 'scheme_id', 'id')->select('id', 'scheme_name');
    }
}