<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AepsTransaction extends Model
{
    protected $table = 'aeps_transactions';
    protected $hidden = ['updated_at'];
    protected $casts = [
        'y' => 'integer'
        ];
    protected $with = ['User', 'Bank', 'Merchant'];
    public function User()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');

    }

    public function Bank()
    {
        return $this->belongsTo(Bank::class, 'bankiin', 'iin');

    }

    public function Merchant()
    {
        return $this->belongsTo(Agent::class, 'merchant_code',
        'merchant_code');;

    }
}
