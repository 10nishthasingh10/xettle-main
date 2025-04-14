<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    protected $table = 'activity_logs';
    protected $fillable = [
       'url', 'method', 'ip', 'agent', 'user_id', 'type','message', 'updated_by'
    ];
    public function user()
    {
        return $this->hasOne(User::class, 'id', 'user_id');
    }
    
    public function operator()
    {
        return $this->hasOne(Operator::class, 'id', 'operator_id');
    }   

}
