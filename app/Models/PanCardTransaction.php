<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PanCardTransaction extends Model
{
    use HasFactory;

    protected $table = 'pan_txns';


    public function user()
    {
        return $this->hasOne(User::class, 'id', 'user_id');
    }



}