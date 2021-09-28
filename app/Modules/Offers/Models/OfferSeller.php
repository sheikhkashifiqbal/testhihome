<?php

namespace App\Modules\Offers\Models;

use App\Core\MyBaseApiModel;

class OfferSeller extends MyBaseApiModel
{
    public $timestamps = false;
    protected $fillable = ['seller_id', 'offer_id'];
}
