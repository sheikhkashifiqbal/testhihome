<?php

namespace App\Modules\Common\Models;

use App\Core\MyBaseApiModel;
use App\Modules\Sellers\Models\ShopBrand;

class SubLocation extends MyBaseApiModel
{
    public $table = 'sub_locations';
    protected $guarded = [];

    public function location() {
        return $this->belongsTo(Location::class, 'location_id', 'id');
    }

    public function sellers() {
        return $this->belongsToMany(ShopBrand::class, 'brands_sub_locations', 'sub_location_id', 'seller_id');
    }

}
