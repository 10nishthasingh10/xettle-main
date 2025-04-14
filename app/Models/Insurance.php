<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Insurance extends Model
{
    use HasFactory;

    protected $table = 'insurance_agents';

    public function user()
    {
        return $this->hasOne(User::class, 'id', 'user_id');
    }
}
