<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductFee extends Model
{
    protected $table = 'global_product_fees';


    public function productName()
    {
        return $this->belongsTo(Product::class, 'product_id', 'product_id')->select('id', 'product_id', 'service_id', 'name');
    }
}
