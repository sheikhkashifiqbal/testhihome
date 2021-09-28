<?php
#app/Models/ShopProductImage.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StoreBannerImage extends Model
{
    public $timestamps = false;
    public $table = 'store_banner_images';
    protected $guarded = [];

/*
Get image
 */
    public function getImage()
    {
        return sc_image_get_path($this->image);

    }
}
