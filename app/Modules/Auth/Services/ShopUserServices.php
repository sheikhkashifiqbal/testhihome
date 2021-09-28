<?php
namespace App\Modules\Auth\Services;

use App\Modules\Auth\Models\ShopUser;

class ShopUserServices
{

  public static function getShopUserDetails($user_id)
  {
    return ShopUser::where('id', $user_id)->first();
  }//getShopUserDetails

}
