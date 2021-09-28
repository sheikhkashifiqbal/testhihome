<?php

namespace App\Modules\RateReview\Models;

use App\Core\MyBaseApiModel;
use App\Modules\Auth\Models\ShopUser;

class ProductRate extends MyBaseApiModel
{
    protected $fillable = ['product_id', 'user_id', 'rate', 'order_id'];

    public function scopeRatingByProduct($query, $product_id)
    {
      return $query->where('product_id', $product_id);
    }
}
