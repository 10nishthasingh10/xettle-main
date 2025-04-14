<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    protected $table = 'global_services';


    protected $fillable = [
        'service_id', 'service_name', 'service_slug', 'is_active', 'is_activation_allowed', 'url', 'service_type', 'created_at', 'updated_at'
    ];

    public function globalProducts()
    {
        return $this->hasMany(Product::class, 'service_id', 'service_id');
    }
}
