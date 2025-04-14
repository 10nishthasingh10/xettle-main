<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class CustomScheme extends Model
{
    protected $table = 'schemes';


    /**
     * Rules info
     */
    public function rulesInfo()
    {
        return $this->hasMany(CustomSchemeRules::class, 'scheme_id', 'id');
    }


    /**
     * get user counts
     */
    public function isAssigned()
    {
        return $this->hasMany(UserConfig::class, 'scheme_id', 'id')->select('scheme_id');
    }
}
