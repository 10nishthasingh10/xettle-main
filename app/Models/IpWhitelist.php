<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IpWhitelist extends Model
{
    protected $hidden = ['updated_at'];

    protected $with = ['Service'];

    public function Service()
    {
        return $this->belongsTo(\App\Models\Service::class , 'service_id', 'service_id');
    }
}
