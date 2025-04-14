<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserService extends Model
{
    protected $with = ['User','Service'];
    public function User()
    {
        return $this->belongsTo(\App\Models\User::class);
    }
    public function Service()
    {
        return $this->belongsTo(\App\Models\Service::class , 'service_id', 'service_id');
    }


}
