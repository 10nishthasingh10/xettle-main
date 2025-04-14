<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reseller extends Model
{
    use HasFactory;

    protected $table = 'resellers';
    protected $fillable = [
        'name','email','status', 'token', 'created_at','updated_at'
    ];
}
