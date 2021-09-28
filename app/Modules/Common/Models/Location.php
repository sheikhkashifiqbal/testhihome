<?php

namespace App\Modules\Common\Models;

use App\Core\MyBaseApiModel;

class Location extends MyBaseApiModel
{
    public $table = 'locations';
    protected $guarded = [];

    public function subLocations() {
        return $this->hasMany(SubLocation::class, 'location_id', 'id');
    }
}
