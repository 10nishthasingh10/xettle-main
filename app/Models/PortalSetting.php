<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class PortalSetting extends Model
{
	
    protected $fillable = ['name', 'code', 'value'];

    protected static $logAttributes = ['name', 'code', 'value'];
    protected static $logOnlyDirty = true;
    
    public $timestamps = false;
}
