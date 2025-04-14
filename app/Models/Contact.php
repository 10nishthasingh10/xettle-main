<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Contact extends Model
{
    protected $hidden = ['id', 'updated_at'];
    
    public static function listing($type='array',$keys='*',$where='',$order_by='contacts.bulk_payouts-desc',$limit=10){
	    $table_name = self::select($keys);

	    if($where){
	        $table_name->whereRaw($where);
	    }

	    if(!empty($order_by)){
	        $order_by = explode('-', $order_by);
	        $table_name->orderBy($order_by[0],$order_by[1]);
	    }
       
	    if($type === 'array'){
            $list = $table_name->get();
            return $list;
        }else if($type === 'obj'){
            return $table_name->limit($limit)->get();                
        }else if($type === 'single'){
            return $table_name->get()->first();
        }else{
            return $table_name->limit($limit)->get();
        }
	}


    public static function insert_data($data)
    {
       if(!empty($data)){
            return self::insertGetId($data);
        }else{
            return false;
        }

    }

    public static function update_data($id,$data){
        $isUpdated = false;
        if(!empty($data)){
            if(isset($data['_method'])){
            unset($data['_method']);
            }
            $table_name=self::where('id','=',$id);
            $isUpdated = $table_name->update($data); 
        }      
        return (bool)$isUpdated;
    }

      public static function updateStatus($id,$data){
        $isUpdated = false;
        if(!empty($data)){
            $table_name=self::where('id',$id);
            $isUpdated = $table_name->update($data); 
        }       
        return (bool)$isUpdated;
    }

}
