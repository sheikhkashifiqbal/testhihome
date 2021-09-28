<?php
#app/Models/ShopProductLike.php
namespace App\Modules\Sellers\Models;

use App\Core\MyBaseApiModel;
use App\Modules\Auth\Models\ShopUser;

class StoreLike extends MyBaseApiModel
{
    public $timestamps = false;
    protected $fillable = ['store_id', 'user_id'];

    public function store() {
        return $this->belongsTo(Store::class, 'store_id', 'id');
    }

    public function user() {
        return $this->belongsTo(ShopUser::class, 'user_id', 'id');
    }

}
