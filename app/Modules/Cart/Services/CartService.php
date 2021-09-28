<?php
namespace App\Modules\Cart\Services;

use App\Modules\Cart\Models\Cart;
use App\Modules\Cart\Models\CartItem;
use App\Modules\Cart\Transformers\CartTransformer;

class CartService
{

  public static function getCartDetails($cart_key)
  {
    $cart = Cart::where('key', $cart_key)
            ->with('items')
            ->first();

    return new CartTransformer($cart, self::CartCalculations($cart->id));
  }//getCartDetails

  private static function CartCalculations($cart_id)
  {
      $cart_items     = CartItem::where('cart_id', $cart_id)->get();
      $count_items    = count($cart_items);
      $total_quantity = $cart_items->sum('quantity');
      $total_price    = 0;
      if ($count_items) {
          foreach ($cart_items as $item) {
              $total_price += $item->quantity * $item->price;
          }
      }

      return ([
          'count_items'    => $count_items,
          'total_quantity' => $total_quantity,
          'total_price'    => $total_price,
      ]);
  }

}
