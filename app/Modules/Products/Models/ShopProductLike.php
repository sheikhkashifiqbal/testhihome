<?php
#app/Models/ShopProductLike.php
namespace App\Modules\Products\Models;

use App\Core\MyBaseApiModel;
use App\Modules\Auth\Models\ShopUser;

class ShopProductLike extends MyBaseApiModel
{
    public $timestamps = false;
    protected $fillable = ['user_id'];

    public function product() {
        return $this->belongsTo(ShopProduct::class, 'product_id', 'id');
    }

    public function user() {
        return $this->belongsTo(ShopUser::class, 'user_id', 'id');
    }

}
