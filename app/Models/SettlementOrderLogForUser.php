<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class SettlementOrderLogForUser extends Model
{
    protected $table = 'user_settlement_logs';

    protected $casts = [
        'created_at' => 'datetime:Y-m-d H:i:s',
    ];
}
