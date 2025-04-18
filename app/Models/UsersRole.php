<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UsersRole extends Model
{
    use HasFactory;

    public function User()
    {
        return $this->belongsTo(\App\Models\User::class);
    }
    public function Role()
    {
        return $this->belongsTo(\App\Models\Role::class);
    }
}