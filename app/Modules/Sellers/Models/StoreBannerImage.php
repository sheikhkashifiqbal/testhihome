<?php

namespace App\Modules\Sellers\Models;

use Illuminate\Database\Eloquent\Model;

class StoreBannerImage extends Model
{
    public $timestamps = false;
    public $table = 'store_banner_images';
    protected $guarded = [];
}
