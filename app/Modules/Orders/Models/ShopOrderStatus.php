<?php
#app/Models/ShopOrderStatus.php
namespace App\Modules\Orders\Models;

use App\Core\MyBaseApiModel;

class ShopOrderStatus extends MyBaseApiModel
{
    public $timestamps = false;
    public $table = 'shop_order_status';
    protected $guarded = [];
    protected static $listStatus = null;

    public static function getListStatus() {
        if (!self::$listStatus) {
            self::$listStatus = self::pluck('name', 'id')->all();
        }
        return self::$listStatus;
    }
    public static function mapValue()
    {
        $listStatus = self::getListStatus();
        $new_arr    = [];
        foreach ($listStatus as $key => $value) {
            $new_arr[] = ['value' => $key, 'text' => $value];
        }
        return $new_arr;
    }
}
