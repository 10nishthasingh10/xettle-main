<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TransactionHistory extends Model
{   
    protected $hidden = ['updated_at'];
    protected $table = 'transaction_history';
    
    public static function insert_data($data)
    {
       if(!empty($data)){
            return self::insertGetId($data);
        }else{
            return false;
        }

    }

}
