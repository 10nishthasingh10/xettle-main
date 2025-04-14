<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserEnvironment extends Model
{
    protected $table ='user_environments';
    protected $hidden = ['updated_at'];

}
