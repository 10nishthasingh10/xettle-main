<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomSchemeRules extends Model
{
    protected $table = 'scheme_rules';



    public function schemesName()
    {
        return $this->belongsTo(CustomScheme::class, 'scheme_id', 'id')->select('id', 'scheme_name');
    }


    public function serviceName()
    {
        return $this->belongsTo(Service::class, 'service_id', 'service_id')->select('id', 'service_id', 'service_name');
    }


    public function productName()
    {
        return $this->belongsTo(Product::class, 'product_id', 'product_id')->select('id', 'product_id', 'name');
    }



    public function productInfo()
    {
        return $this->hasMany(Product::class, 'service_id', 'service_id')->select('id', 'service_id', 'product_id', 'name');
    }
}
