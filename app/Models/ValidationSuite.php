<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ValidationSuite extends Model
{
    protected $table = 'validation_suite';
    protected $primaryKey = 'id';



    /**
     * Belongs to relation with user table
     */
    public function userBasicInfo()
    {
        return $this->belongsTo(User::class, 'user_id', 'id')->select('id', 'name', 'email', 'mobile');
    }
}
