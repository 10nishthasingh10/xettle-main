<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserActivityLog extends Model
{
    use HasFactory;

    protected $table = 'user_activities_logs';
    protected $fillable = [
        'user_id', 'message', 'type', 'created_at', 'updated_at'
    ];
}
