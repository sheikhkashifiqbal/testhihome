<?php
namespace App\Modules\Orders\Services;

use App\Modules\Orders\Models\ShopOrder;

class OrderService
{

  public static function usersDeliveredOrdersInStoreCount($user_id, $store_id)
  {
    return ShopOrder::where([
                          ['user_id', $user_id],
                          ['store_id', $store_id],
                          ['status', 5]
                        ])->count();
  }//usersDeliveredOrdersInStoreCount

  public static function checkOrderCreatedByCustomer($order_id, $user_id)
  {
    return ShopOrder::where([
                            ['user_id', $user_id],
                            ['id', $order_id]
                            ])->exists();
  }//checkOrderCreatedByCustomer

  public static function getOrderbyCustomer($order_id, $user_id)
  {
    return ShopOrder::where([
                            ['user_id', $user_id],
                            ['id', $order_id]
                            ]);
  }//checkOrderCreatedByCustomer

  public static function generateOrderNumber()
    {
      $num = mt_rand(10000000,99999999);
      if(!ShopOrder::where('number',$num)->count())
        return $num;
      else
        return self::generateOrderNumber();
    }

}
