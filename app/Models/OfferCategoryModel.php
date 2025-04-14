<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OfferCategoryModel extends Model
{
    protected $table = 'offer_category';


    public function activeOffers()
    {
        return $this->hasMany(OfferModel::class, 'category_id', 'id')
            ->where('status', '1')
            ->whereDate('expired_at', '>=', date('Y-m-d'))
            ->limit(10);
    }
}
