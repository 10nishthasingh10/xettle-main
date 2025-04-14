<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class SettlementOrderLog extends Model
{
    protected $table = 'user_settlement_logs';

    protected $with = ['routeName'];
    
    public function routeName()
    {
        return $this->hasOne(Integration::class, 'integration_id', 'integration_id');
    }
}
