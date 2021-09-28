<?php

namespace App\Modules\Products\Models;

use App\Core\MyBaseApiModel;
use App\Modules\Auth\Models\ShopUser;

class ProductRate extends MyBaseApiModel
{
    public $timestamps = false;
    protected $fillable = ['product_id', 'user_id', 'rate'];

    public function product() {
        return $this->belongsTo(ShopProduct::class, 'product_id', 'id');
    }

    public function user() {
        return $this->belongsTo(ShopUser::class, 'user_id', 'id');
    }

}
