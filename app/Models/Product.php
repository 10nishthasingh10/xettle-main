<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $table = 'global_products';


    public function productFee()
    {
        return $this->hasMany(ProductFee::class, 'product_id', 'product_id');
    }


    public function serviceName()
    {
        return $this->belongsTo(Service::class, 'service_id', 'service_id')->select('service_id', 'service_name');
    }


    public static function listing($type = 'array', $keys = '*', $where = '', $order_by = 'id-desc', $limit = 10)
    {
        $table_name = self::select($keys);

        if ($where) {
            $table_name->whereRaw($where);
        }

        if (!empty($order_by)) {
            $order_by = explode('-', $order_by);
            $table_name->orderBy($order_by[0], $order_by[1]);
        }

        if ($type === 'array') {
            $list = $table_name->get();
            return json_decode(json_encode($list), true);
        } else if ($type === 'obj') {
            return $table_name->limit($limit)->get();
        } else if ($type === 'single') {
            return $table_name->get()->first();
        } else {
            return $table_name->limit($limit)->get();
        }
    }



    public static function updateStatus($id, $data)
    {
        $isUpdated = false;
        if (!empty($data)) {
            $table_name = self::where('id', $id);
            $isUpdated = $table_name->update($data);
        }
        return (bool)$isUpdated;
    }
}
