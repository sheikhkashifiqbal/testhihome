<?php

namespace App\Modules\RateReview\Models;

use App\Core\MyBaseApiModel;

class OrderReview extends MyBaseApiModel
{
    protected $fillable = ['review', 'order_id'];


}
