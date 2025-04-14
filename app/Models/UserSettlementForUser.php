<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class UserSettlementForUser extends Model
{
    protected $table = 'user_settlements';

    protected $casts = [
        'created_at' => 'datetime:Y-m-d H:i:s',
    ];


    public function getSettlementLog()
    {
        return $this->hasMany(SettlementOrderLogForUser::class, 'settlement_ref_id', 'settlement_ref_id')
            ->select(
                'settlement_ref_id',
                'settlement_txn_id',
                'amount',
                'status',
                'status_response',
                'bank_reference',
                'failed_message',
                'created_at'
            );
    }
}
