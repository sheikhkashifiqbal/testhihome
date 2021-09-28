<?php

namespace App\Modules\Offers\Models;

use App\Core\MyBaseApiModel;

class OfferDescription extends MyBaseApiModel
{
    public $timestamps = false;
    protected $fillable = ['lang', 'title', 'description'];
}
