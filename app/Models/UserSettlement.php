<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserSettlement extends Model
{
    protected $table = 'user_settlements';

    protected $with = ['getSettlementLog', 'User'];
    
    public function getSettlementLog() {
        return $this->hasMany(SettlementOrderLog::class, 'settlement_ref_id', 'settlement_ref_id');
    }
    public function User()
    {
        return $this->belongsTo(User::class)->select(['id', 'name', 'email']);
    }

}