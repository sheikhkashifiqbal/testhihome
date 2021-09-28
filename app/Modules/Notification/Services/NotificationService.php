<?php

namespace App\Modules\Notification\Services;

use App\Modules\Notification\Models\Notification;

class NotificationService
{

  public static function productUpdateNotification($notifiable_id, $data)
  {
    $type = config('notification.constants.TYPES.PRODUCT_UPDATE');
    $notifiable_type = config('notification.constants.NOTIFIABLE_TYPES.SELLER');
    //dd([$type, $notifiable_type, $notifiable_id, json_encode($data)]);
    return self::storeNotification($type, $notifiable_type, $notifiable_id, $data);
  }//productUpdateNotification

  public static function storeRatingNotification($notifiable_id, $data)
  {
    $type = config('notification.constants.TYPES.SELLER_REVIEW');
    $notifiable_type = config('notification.constants.NOTIFIABLE_TYPES.SELLER');
    //dd([$type, $notifiable_type, $notifiable_id, json_encode($data)]);
    return self::storeNotification($type, $notifiable_type, $notifiable_id, $data);
  }//storeRatingNotification

  public static function orderRatingNotification($notifiable_id, $data)
  {
    $type = config('notification.constants.TYPES.ORDER_RATE');
    $notifiable_type = config('notification.constants.NOTIFIABLE_TYPES.SELLER');
    //dd([$type, $notifiable_type, $notifiable_id, json_encode($data)]);
    return self::storeNotification($type, $notifiable_type, $notifiable_id, $data);
  }//orderRatingNotification

  public static function storeNotification($type, $notifiable_type, $notifiable_id, $data)
  {
    $notification = Notification::create(
      [
        'type' => $type,
        'notifiable_type' => $notifiable_type,
        'notifiable_id' => $notifiable_id,
        'data' => json_encode($data),
      ]
    );
    return $notification;
  }//storeNotification

}
