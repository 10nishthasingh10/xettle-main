<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RechargeBack extends Model
{
    use HasFactory;

    protected $table = 'charge_back';
    public static $tableName = 'charge_back';

    public function user()
    {
        return $this->hasOne(User::class, 'id', 'user_id');
    }

    public function operator()
    {
        return $this->hasOne(Operator::class, 'id', 'operator_id');
    }
}
