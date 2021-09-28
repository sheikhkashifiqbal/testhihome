<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\CartItemCollection as CartItemCollection;
use App\Http\Resources\Order2Resource as OrderResource;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\ShopOrder as Order;
use App\Models\ShopProduct as Product;
use Auth;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CartController extends Controller
{

    /**
     * Store a newly created Cart in storage and return the data to the user.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */

    public function store(Request $request) {
        $input = $request->only(
            [
                'brand_id',
                'branch_id',
                'cart_type',
            ]
        );

        /* $input = $request->all();
        */
        $this->validate(
            $request,
            [
                'brand_id'  => 'required',
                'cart_type' => 'required',
            ]
        );
        $brand_id  = $input['brand_id'] ?? null;
        $branch_id = $input['branch_id'] ?? null;
        $cart_type = $input['cart_type'] ?? null;
        //pickup/delivery
        if (Auth::guard('api')->check()) {
            $userID = auth('api')->user()->getKey();
        }
        $user      = Auth::user();
        $cart_item = $user->cart
            ->where('brand_id', $brand_id)
            ->where('branch_id', $branch_id)
            ->where('cart_type', $cart_type)
            ->first();

        if (!$cart_item) {
            $cart = Cart::create(
                [
                    'id'        => md5(uniqid(rand(), true)),
                    'key'       => md5(uniqid(rand(), true)),
                    'userID'    => isset($userID) ? $userID : null,
                    'brand_id'  => $brand_id,
                    'branch_id' => $branch_id,
                    'cart_type' => $cart_type,

                ]
            );
        } else {
            $cart = $cart_item;

        }
        return response()->json(
            [
                'status'    => 1,
                'message'   => 'done',
                'cartToken' => $cart->id,
                'cartKey'   => $cart->key,
            ],
            201
        );

    }

    /**
     * Display the specified Cart.
     *
     * @param \App\Cart $cart
     *
     * @return \Illuminate\Http\Response
     */

    public function show(Cart $cart, Request $request) {
        $this->validate(
            $request,
            [
                'cartKey' => 'required',
            ]
        );
        /* $validator = Validator::make( $request->all(), [
            'cartKey' => 'required',
        ] );

        if ( $validator->fails() ) {
            return response()->json( [
                'errors' => $validator->errors(),
            ], 400 );
        } */

        $cartKey = $request->input('cartKey');
        if ($cart->key == $cartKey) {
            $total_quantity = CartItem::where(['cart_id' => $cart->getKey()])->sum('quantity');
            $total_price    = CartItem::where(['cart_id' => $cart->getKey()])->sum('price');
            return response()->json(
                [
                    'status'         => 1,
                    'cart'           => $cart->id,
                    'total_quantity' => (string)$total_quantity,
                    'total_price'    => 'AED ' . $total_price,
                    'data'           => new CartItemCollection($cart->items),
                ],
                200
            );

        } else {

            return response()->json(
                [
                    'status'  => 0,
                    'message' => 'The CarKey you provided does not match the Cart Key for this Cart.',
                ],
                400
            );
        }

    }

    /**
     * Remove the specified Cart from storage.
     *
     * @param \App\Cart $cart
     *
     * @return \Illuminate\Http\Response
     */

    public function destroy(Cart $cart, Request $request) {
        $this->validate(
            $request,
            [
                'cartKey' => 'required',
            ]
        );
        /* $validator = Validator::make( $request->all(), [
            'cartKey' => 'required',
        ] );

        if ( $validator->fails() ) {
            return response()->json( [
                'errors' => $validator->errors(),
            ], 400 );
        } */

        $cartKey = $request->input('cartKey');

        if ($cart->key == $cartKey) {
            $cart->delete();
            return response()->json(['status' => 1, 'message' => 'done'], 204);
        } else {

            return response()->json(
                [
                    'status'  => 0,
                    'message' => 'The CarKey you provided does not match the Cart Key for this Cart.',
                ],
                400
            );
        }

    }

    /**
     * Adds Products to the given Cart;
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Cart $cart
     *
     * @return void
     */

    public function addProducts(Cart $cart, Request $request) {
        $this->validate(
            $request,
            [
                'cartKey'  => 'required',
                'item_id'  => 'required',
                'quantity' => 'required|numeric|min:0',
                'size'     => 'required|numeric',
            ]
        );

        /* $validator = Validator::make( $request->all(), [
            'cartKey' => 'required',
            'item_id' => 'required',
            'quantity' => 'required|numeric|min:1|max:10',
            'size' => 'required|numeric',
        ] );

        if ( $validator->fails() ) {
            return response()->json( [
                'errors' => $validator->errors(),
            ], 400 );
        } */

        $cartKey  = $request->input('cartKey');
        $item_id  = $request->input('item_id');
        $quantity = $request->input('quantity');
        $size     = $request->input('size');

        //Check if the CarKey is Valid
        if ($cart->key == $cartKey) {
            //Check if the proudct exist or return 404 not found.
            try {
                $Product      = Product::findOrFail($item_id);
                $size_details = $Product->sizes()->where('shop_product_attribute.id', $size)->first();

                $price = $quantity * $size_details->price;
            } catch (ModelNotFoundException $e) {
                return response()->json(
                    [
                        'status'  => 0,
                        'message' => 'The Item you\'re trying to add does not exist.',
                    ],
                    404
                );
            }

            //check if the the same product is already in the Cart, if true update the quantity, if not create a new one.
            $cartItem = CartItem::where(['cart_id' => $cart->getKey(), 'product_id' => $item_id])->first();

            if ($cartItem) {
                if ($quantity == 0) {
                    $del = CartItem::where(['cart_id' => $cart->getKey(), 'product_id' => $item_id])->delete();
                } else {
                    $cartItem->quantity = $quantity;
                    CartItem::where(['cart_id' => $cart->getKey(), 'product_id' => $item_id])->update(['quantity' => $quantity, 'size_id' => $size, 'price' => $price, 'size_title' => $size_details->name]);
                }
            } else {
                if ($quantity > 0) {
                    CartItem::create(['cart_id' => $cart->getKey(), 'product_id' => $item_id, 'quantity' => $quantity, 'size_id' => $size, 'price' => $price, 'size_title' => $size_details->name]);
                }
            }

            $total_quantity = CartItem::where(['cart_id' => $cart->getKey()])->sum('quantity');
            $total_price    = CartItem::where(['cart_id' => $cart->getKey()])->sum('price');
            //2 items AED 122
            return response()->json(
                [
                    'status'         => 1,
                    'total_quantity' => "$total_quantity",
                    'total_price'    => 'AED ' . $total_price,
                    'message'        => (isset($del)) ? 'Item Removed from cart successfully' : 'The Cart was updated with the given product information successfully'
                ],
                200
            );

        } else {

            return response()->json(
                [
                    'status'  => 0,
                    'message' => 'The CarKey you provided does not match the Cart Key for this Cart.',
                ],
                400
            );
        }

    }

    public function delete($id) {
        $products = session('cart');
        foreach ($products as $key => $value) {
            if ($value['id'] == $id) {
                unset($products [$key]);
            }
        }
        //cart = ShoppingCart::removeItem($id);
        //put back in session array without deleted item
        $request->session()->push('cart', $products);

    }

    /**
     * checkout the cart Items and create and order.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Cart $cart
     *
     * @return void
     */
    public function checkout(Cart $cart, Request $request) {

        if (Auth::guard('api')->check()) {
            $userID = auth('api')->user()->getKey();
        }
        $input = $request->only(
            [
                'cartKey',
                'customer_name',
                'mobile',
                'email',
                'address_id',
                'address_text',
                'address_lat',
                'address_long',
                'address_landmark',
                'transaction_id',

            ]
        );

        //todo: validate if order type piickup diffrernt fields on delievry

        $this->validate(
            $request,
            [
                'cartKey'        => 'required',
                'customer_name'  => 'required',
                /* 'mobile' => 'required', */
                'address_id'     => 'required_without:address_text',
                'address_text'   => 'required_without:address_id',
                'transaction_id' => 'required',

            ]
        );


        $cartKey = $request->input('cartKey');
        if ($cart->key == $cartKey) {
            $total_quantity = CartItem::where(['cart_id' => $cart->getKey()])->sum('quantity');
            if (!$total_quantity) {
                return response()->json(
                    [
                        'status'  => 0,
                        'message' => 'Shopping Cart is empty.',
                    ],
                    400
                );
            }
            $order['user_id']    = $cart->userID;
            $name                = explode(' ', $input['customer_name']);
            $order['first_name'] = $name[0];
            $order['last_name']  = $name[1] ?? " ";

            $order['address_id']    = $input['address_id'] ?? null;
            $order['address1']      = $input['address_text'] ?? null;
            $order['address1_lat']  = $input['address_lat'] ?? null;
            $order['address1_long'] = $input['address_long'] ?? null;
            $order['address2']      = $input['address_landmark'] ?? null;
            $order['phone']         = $input['mobile'] ?? null;
            $order['email']         = $input['email'] ?? null;


            $order['order_type'] = $cart->cart_type;
            $order['brand_id']   = $cart->brand_id;
            $order['branch_id']  = $cart->branch_id;

            $order['status']          = 1;
            $order['payment_status']  = 3;
            $order['shipping_status'] = 1;

            $order['currency']      = 'AED';
            $order['exchange_rate'] = '1.00';

            $subtotal       = (float)0.0;
            $items          = $cart->items;
            $order['items'] = json_encode(new CartItemCollection($items));
            foreach ($items as $item) {

                $product = Product::find($item->product_id);
                $price   = $item->price / $item->quantity;
                //$inStock = $product->UnitsInStock;
                $subtotal += ($price * $item->quantity);
                $price    = 'AED ' . $price;
                /* if ($inStock >= $item->quantity) {

                    $TotalPrice = $TotalPrice + ($price * $item->quantity);

                    $product->UnitsInStock = $product->UnitsInStock - $item->quantity;
                    $product->save();
                } else {
                    return response()->json([
                        'message' => 'The quantity you\'re ordering of ' . $item->Name .
                    ' isn\'t available in stock, only ' . $inStock . ' units are in Stock, please update your cart to proceed',
                    ], 400);
                } */
            }
            $order['subtotal'] = $subtotal;
            $order['shipping'] = 0;
            $order['discount'] = 0;
            $order['tax']      = 0;


            $order['total'] = $order['subtotal'] + $order['shipping'] - $order['discount'] + $order['tax'];
            //$transactionID = md5( uniqid( rand(), true ) );
            $order['transaction'] = $request->input('transaction_id');

            /**
             * Credit Card information should be sent to a payment gateway for processing and validation,
             * the response should be dealt with here, but since this is a dummy project we'll
             * just assume that the information is sent and the payment process was done succefully,
             */

            $PaymentGatewayResponse = true;


            if ($PaymentGatewayResponse) {
                $estimated_time          = 45;//to do dynamic from store
                $order['estimated_time'] = Carbon::now()->addMinutes($estimated_time);
                $order1                  = Order::create($order);

                $cart->delete();

                return new JsonResponse(
                    [
                        'status'  => 1,
                        'message' => 'Your order has been completed successfully, please check the order status for the updates!',
                        'data'    => new OrderResource($order1),
                    ], 200
                );

            }
        } else {
            return response()->json(
                [
                    'status'  => 0,
                    'message' => 'The CarKey you provided does not match the Cart Key for this Cart.',
                ],
                400
            );
        }

    }

}
