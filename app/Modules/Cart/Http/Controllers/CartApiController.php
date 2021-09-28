<?php

namespace App\Modules\Cart\Http\Controllers;

use App\Core\MyBaseApiController;
use App\Modules\Auth\Models\ShopUser;
use App\Modules\Cart\Models\Cart;
use App\Modules\Cart\Models\CartItem;
use App\Modules\Cart\Transformers\CartItemTransformer;
use App\Modules\Cart\Transformers\CartTransformer;
use App\Modules\Orders\Models\ShopOrder;
use App\Modules\Orders\Transformers\OrderTransformer;
use App\Modules\Products\Models\ShopProduct;
use App\Modules\Orders\Services\OrderService;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use OneSignal;

class CartApiController extends MyBaseApiController
{
    public function getUserCart(Request $request)
    {
        try {
            $cart = Cart::where('user_id', Auth::user()->id)
                ->with(
                    [
                        'items',
                        //                              'items.product', 'items.product.productDescription'
                    ]
                )
                ->first();

            if (!$cart) {
                $cart = Cart::create(
                    [
                        'key'     => md5(uniqid(rand(), true)),
                        'user_id' => Auth::user()->id
                    ]
                );
            }

            $cart_data = new CartTransformer($cart, $this->CartCalculations($cart->id));

            return $this->successResponseWithData($cart_data);
        } catch (\Exception $e) {
            if (app()->environment('local')) {
                $message = $e->getMessage() . ' file : ' . $e->getFile() . ' In Line : ' . $e->getLine();
            } else {
                $message = trans('common.Something Went Wrong');
            }
            return $this->errorResponse($message);
        }
    }

    public function addItem(Request $request)
    {
        try {
            $this->validateApiRequest(
                ['product_id', 'cart_key', 'quantity'],
                [
                    'product_id' => 'exists:shop_product,id',
                    'cart_key'   => 'exists:carts,key',
                    'quantity'   => 'numeric'
                ],
                ['product_id', 'cart_key']
            );

            $cart    = Cart::with('items')->where('key', $request->cart_key)->first();
            $Product = ShopProduct::find($request->product_id);

            if($cart->items->count() > 0 ){
              $item = $cart->items->first();
              if($Product->store_id !== $item->store_id ){
                return $this->errorResponse(trans('Can not add the product product from different seller'));
              }

            }


            $cart->branch_id = $Product->store_id;
            $cart->save();
            $cart_item = CartItem::where('cart_id', $cart->id)
                ->where('product_id', $Product->id)
                ->first();



            if ($cart_item) {
                if ($request->quantity == 0) {
                    CartItem::where('cart_id', $cart->id)
                        ->where('product_id', $Product->id)->delete();
                } else {
                    CartItem::where('cart_id', $cart->id)
                        ->where('product_id', $Product->id)->update(
                            [
                                'quantity' => (float)$cart_item->quantity + (float)$request->quantity,
                                'price'    => $Product->price
                            ]
                        );
                }
            } else {
                $data = [
                    'cart_id'    => $cart->id,
                    'store_id'   => $Product->store_id,
                    'product_id' => $Product->id,
                    'price'      => $Product->price,
                    'quantity'   => $request->quantity
                ];
                CartItem::create($data);
            }

            $data = $this->CartCalculations($cart->id);
            return $this->successResponseWithData($data, trans('cart.item_added'));
        } catch (\Exception $e) {
            if (app()->environment('local')) {
                $message = $e->getMessage() . ' file : ' . $e->getFile() . ' In Line : ' . $e->getLine();
            } else {
                $message = trans('common.Something Went Wrong');
            }
            return $this->errorResponse($message);
        }
    }

    public function decreaseItem(Request $request)
    {
        try {
            $this->validateApiRequest(
                ['product_id', 'cart_key'],
                [
                    'product_id' => 'exists:shop_product,id',
                    'cart_key'   => 'exists:carts,key',
                ],
                ['product_id', 'cart_key']
            );

            $cart    = Cart::where('key', $request->cart_key)->first();
            $Product = ShopProduct::find($request->product_id);

            $cart_item = CartItem::where('cart_id', $cart->id)
                ->where('product_id', $Product->id)
                ->first();

            if ($cart_item) {
                if ($cart_item->quantity > 1) {
                    CartItem::where('cart_id', $cart->id)
                        ->where('product_id', $Product->id)
                        ->decrement('quantity');
                } else {
                    CartItem::where('cart_id', $cart->id)
                        ->where('product_id', $Product->id)
                        ->delete();
                }
            }

            $data = $this->CartCalculations($cart->id);
            return $this->successResponseWithData($data, trans('cart.item_decremented'));
        } catch (\Exception $e) {
            if (app()->environment('local')) {
                $message = $e->getMessage() . ' file : ' . $e->getFile() . ' In Line : ' . $e->getLine();
            } else {
                $message = trans('common.Something Went Wrong');
            }
            return $this->errorResponse($message);
        }
    }

    public function removeItem(Request $request)
    {
        try {
            $this->validateApiRequest(
                ['product_id', 'cart_key'],
                [
                    'product_id' => 'exists:shop_product,id',
                    'cart_key'   => 'exists:carts,key',
                ],
                ['product_id', 'cart_key']
            );

            $cart    = Cart::where('key', $request->cart_key)->first();
            $Product = ShopProduct::find($request->product_id);

            CartItem::where('cart_id', $cart->id)
                ->where('product_id', $Product->id)
                ->delete();

            $data = $this->CartCalculations($cart->id);
            return $this->successResponseWithData($data, trans('cart.item_removed'));
        } catch (\Exception $e) {
            if (app()->environment('local')) {
                $message = $e->getMessage() . ' file : ' . $e->getFile() . ' In Line : ' . $e->getLine();
            } else {
                $message = trans('common.Something Went Wrong');
            }
            return $this->errorResponse($message);
        }
    }

    private function CartCalculations($cart_id)
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

    public function checkout(Request $request)
    {

        try {
            DB::beginTransaction();
            $this->validateApiRequest(
                ['customer_name', 'transaction_id', 'transaction_type', 'delivery_date', 'delivery_slot'],
                [
                    'address_id'       => 'required_without:address_text',
                    'address_text'     => 'required_without:address_id',
                    'transaction_type' => 'in:cod,card,apple_pay,google_pay',
                ]
            );


            $user_cart = Cart::where('user_id', Auth::user()->id)
                ->with(
                    [
                        'items',
                        'items.product',
                        'items.product.productDescription'
                    ]
                )
                ->firstOrFail();

            $total_quantity = CartItem::where(['cart_id' => $user_cart->id])->sum('quantity');
            if (!$total_quantity) {
                return $this->errorResponse('Shopping Cart is empty.');
            }

            $store_id          = ($user_cart->branch_id) ? $user_cart->branch_id : null;
            $cart_calculations = $this->CartCalculations($user_cart->id);
            $customer_name     = explode(' ', $request->customer_name);


            $PaymentGatewayResponse = true;
            if ($PaymentGatewayResponse) {
                $order_data        = [
                    'number'         => OrderService::generateOrderNumber(),
                    'user_id'         => $user_cart->user_id,
                    'first_name'      => $customer_name[0],
                    'last_name'       => $customer_name[1] ?? " ",
                    'address_id'       => $request->address_id,
                    'address1'         => $request->address_text,
                    'address1_lat'     => $request->address_lat,
                    'address1_long'    => $request->address_long,
                    'address2'         => $request->address_landmark,
                    'phone'            => $request->mobile,
                    'email'            => $request->email,
                    'status'           => 1,
                    'payment_status'   => 3,
                    'shipping_status'  => 1,
                    'currency'         => 'AED',
                    'exchange_rate'    => '1.00',
                    'exchange_rate'    => '1.00',
                    'items'            => json_encode(CartItemTransformer::collection($user_cart->items)),
                    'subtotal'         => $cart_calculations['total_price'],
                    'shipping'         => 0,
                    'discount'         => 0,
                    'tax'              => 0,
                    'total'            => $cart_calculations['total_price'],
                    'transaction'      => $request->transaction_id,
                    'transaction_type' => $request->transaction_type,
                    'store_id'         => $store_id,
                    'delivery_date'    => $request->delivery_date,
                    'delivery_slot'    => $request->delivery_slot,
                ];

                $estimated_time               = 45; //to do dynamic from store
                $order_data['estimated_time'] = Carbon::now()->addMinutes($estimated_time);
                $order                        = ShopOrder::create($order_data);

                foreach ($user_cart->items as $item) {

                    $order->details()->create(
                        [
                            'store_id'    => $item->store_id,
                            'product_id'  => $item->product_id,
                            'name'        => $item->product->name,
                            'price'       => $item->price,
                            'qty'         => $item->quantity,
                            'total_price' => $item->price * $item->quantity,
                            'sku'         => 'N/A',
                            'currency'    => 'AED',
                        ]
                    );
                }

                //

                if ($store_id) {
                    $seller_user = ShopUser::where('role', 'seller')
                        ->where('store_id', $store_id)
                        ->first();

                    if ($seller_user && !empty($seller_user->device_udid)) {
                        send_notification_to_seller(
                            'you have new order please accept it',
                            $seller_user->device_udid,
                            null,
                            ['order_id' => $order->id]
                        );
                    }
                }


                if (Auth::user()->device_udid) {
                    send_notification_to_customer(
                        "Your order #" . $order->id . " has been placed. Awaiting confirmation from restaurant",
                        Auth::user()->device_udid,
                        null,
                        ['order_id' => $order->id]
                    );
                }
                $data = new OrderTransformer($order);
                DB::commit();
                $user_cart->delete();
                return $this->successResponseWithData($data, 'Your order has been completed successfully, please check the order status for the updates!');
            }
        } catch (ModelNotFoundException $e) {
            DB::rollBack();
            return $this->errorResponse('user cart not found');
        } catch (\Exception $e) {
            DB::rollBack();
            if (app()->environment('local')) {
                $message = $e->getMessage() . ' file : ' . $e->getFile() . ' In Line : ' . $e->getLine();
            } else {
                $message = trans('common.Something Went Wrong');
            }
            return $this->errorResponse($message);
        }
    }

    public function destroyUserCart()
    {
        try {
            DB::beginTransaction();
            $user_cart = Cart::where('user_id', Auth::user()->id)->firstOrFail();
            $user_cart->items()->delete();
            $user_cart->delete();
            DB::commit();
            return $this->successEmptyResponse('Cart Deleted successfully');
        } catch (ModelNotFoundException $e) {
            DB::rollBack();
            return $this->errorResponse('user cart not found');
        } catch (\Exception $e) {
            DB::rollBack();
            if (app()->environment('local')) {
                $message = $e->getMessage() . ' file : ' . $e->getFile() . ' In Line : ' . $e->getLine();
            } else {
                $message = trans('common.Something Went Wrong');
            }
            return $this->errorResponse($message);
        }
    }
}
