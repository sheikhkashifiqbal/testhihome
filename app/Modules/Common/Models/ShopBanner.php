<?php
namespace App\Modules\Common\Models;

use App\Core\MyBaseApiModel;

class ShopBanner extends MyBaseApiModel
{
    public $table = 'shop_banner';
    protected $guarded = [];

    /*
    Get thumb
     */
    public function getThumb() {
        return sc_image_get_path_thumb($this->image);
    }

/*
Get image
 */
    public function getImage()
    {
        return sc_image_get_path($this->image);

    }
//Scort
    public function scopeSort($query, $column = null)
    {
        $column = $column ?? 'sort';
        return $query->orderBy($column, 'asc')->orderBy('id', 'desc');
    }

}
