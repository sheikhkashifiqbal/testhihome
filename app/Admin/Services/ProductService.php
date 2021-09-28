<?php
namespace App\Admin\Services;

use Carbon\Carbon;

class ProductService
{

  public static function calculateFinalPrice($product)
  {
    $today = Carbon::today();
    if($product->discount_percentage > 0 && Carbon::today()->between($product->discount_start_date, $product->discount_expiry_date))
    {
      $finalPrice = ($product->price * $product->discount_percentage) / 100;
      return $product->price - $finalPrice . " ( ".$product->price." )";
    }
    return $product->price;
  }//getShopUserDetails

}
