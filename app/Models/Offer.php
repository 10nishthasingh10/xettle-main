<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Offer extends Model
{
    protected $table = 'offers';

    public function category()
    {
        return $this->hasOne(OfferCategory::class, 'id', 'category_id');
    }
}