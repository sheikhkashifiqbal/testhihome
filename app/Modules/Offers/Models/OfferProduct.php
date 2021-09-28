<?php

namespace App\Modules\Offers\Models;

use App\Core\MyBaseApiModel;

class OfferProduct extends MyBaseApiModel
{
    public $timestamps = false;
    protected $fillable = ['offer_id', 'product_id', 'seller_id'];
}
