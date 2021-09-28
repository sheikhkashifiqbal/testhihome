<?php
namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\ShopUserAddress as Address;
use Auth;
use Illuminate\Http\Request;

class AddressController extends Controller
 {

    /**
    * Store a newly created Cart in storage and return the data to the user.
    *
    * @param  \Illuminate\Http\Request  $request
    * @return \Illuminate\Http\Response
    */
    public function index() {
        if ( Auth::guard( 'api' )->check() ) {
            $user_id = auth( 'api' )->user()->getKey();
        }


        $address = Address::where('user_id', $user_id)->get();
        return response()->json( [
            'status'=>1,
            'message' => 'done',
            'data' => $address,
        ], 201 );
    }
    public function store( Request $request ) {
        $input = $request->only( [
            'id',
            'tag',
            'address',
            'lat',
            'long',
            'landmark',
        ] );

        /* $input = $request->all();
        */
        $this->validate( $request, [
            'address' => 'required',
            'lat' => 'required',
            'long' => 'required',
        ] );
        $input['tag'] = $input['tag'] ?? 'Default Address';
        $input['id']  = $input['id'] ?? null;

        if (Auth::guard('api')->check()) {
            $user_id = auth('api')->user()->getKey();
        }
        $user = Auth::user();

        $address           = Address::firstOrNew(['id' => $input['id']]);
        $address->user_id  = $user_id;
        $address->tag      = $input['tag'];
        $address->address  = $input['address'];
        $address->lat      = $input['lat'];
        $address->long     = $input['long'];
        $address->landmark = $input['landmark'];

        $address->save();


        return response()->json(
            [
                'status'  => 1,
                'message' => 'done',
                'data'    => $address,
            ], 201 );

    }

    /**
    * Display the specified Cart.
    *
    * @param  \App\Cart  $cart
    * @return \Illuminate\Http\Response
    */

    public function show( Cart $cart, Request $request ) {
        $this->validate( $request, [
            'cartKey' => 'required',
        ] );
        /* $validator = Validator::make( $request->all(), [
            'cartKey' => 'required',
        ] );

        if ( $validator->fails() ) {
            return response()->json( [
                'errors' => $validator->errors(),
            ], 400 );
        } */

        $cartKey = $request->input( 'cartKey' );
        if ( $cart->key == $cartKey ) {

            return response()->json( [
                'cart' => $cart->id,
                'data' => new CartItemCollection( $cart->items ),
            ], 200 );

        } else {

            return response()->json( [
                'message' => 'The CarKey you provided does not match the Cart Key for this Cart.',
            ], 400 );
        }

    }

    /**
    * Remove the specified Cart from storage.
    *
    * @param  \App\Cart  $cart
    * @return \Illuminate\Http\Response
    */

    public function destroy( Cart $cart, Request $request ) {
        $this->validate( $request, [
            'cartKey' => 'required',
        ] );
        /* $validator = Validator::make( $request->all(), [
            'cartKey' => 'required',
        ] );

        if ( $validator->fails() ) {
            return response()->json( [
                'errors' => $validator->errors(),
            ], 400 );
        } */

        $cartKey = $request->input( 'cartKey' );

        if ( $cart->key == $cartKey ) {
            $cart->delete();
            return response()->json( null, 204 );
        } else {

            return response()->json( [
                'message' => 'The CarKey you provided does not match the Cart Key for this Cart.',
            ], 400 );
        }

    }

    /**
    * Adds Products to the given Cart;
    *
    * @param  \Illuminate\Http\Request  $request
    * @param  \App\Cart  $cart
    * @return void
    */

    public function addProducts( Cart $cart, Request $request ) {
        $this->validate( $request, [
            'cartKey' => 'required',
            'item_id' => 'required',
            'quantity' => 'required|numeric|min:0',
            'size' => 'required|numeric',
        ] );

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

        $cartKey = $request->input( 'cartKey' );
        $item_id = $request->input( 'item_id' );
        $quantity = $request->input( 'quantity' );
        $size = $request->input( 'size' );

        //Check if the CarKey is Valid
        if ( $cart->key == $cartKey ) {
            //Check if the proudct exist or return 404 not found.
            try {
                $Product = Product::findOrFail( $item_id );
                $size_details = $Product->sizes()->where( 'shop_product_attribute.id', $size )->first();

                $price = $quantity*$size_details->price;
            } catch ( ModelNotFoundException $e ) {
                return response()->json( [
                    'message' => 'The Item you\'re trying to add does not exist.',
                ], 404);
            }

            //check if the the same product is already in the Cart, if true update the quantity, if not create a new one.
            $cartItem = CartItem::where(['cart_id' => $cart->getKey(), 'product_id' => $item_id])->first();

            if ($cartItem) {
                if($quantity==0){
                    $del=CartItem::where(['cart_id' => $cart->getKey(), 'product_id' => $item_id])->delete();
                }else{
                    $cartItem->quantity = $quantity;
                    CartItem::where(['cart_id' => $cart->getKey(), 'product_id' => $item_id])->update(['quantity' => $quantity, 'size_id' => $size, 'price' => $price,'size_title'=>$size_details->name]);
                }
            } else if($quantity>0){
                CartItem::create(['cart_id' => $cart->getKey(), 'product_id' => $item_id, 'quantity' => $quantity, 'size_id' => $size, 'price' => $price,'size_title'=>$size_details->name]);
            }

            $total_quantity=CartItem::where(['cart_id' => $cart->getKey()])->sum('quantity');
            $total_price=CartItem::where(['cart_id' => $cart->getKey()])->sum('price');
            //2 items AED 122
            return response()->json(['total_quantity' =>"$total_quantity",'total_price' =>'AED '.$total_price
            ,'message' => (isset($del))?'Item Removed from cart successfully':'The Cart was updated with the given product information successfully'], 200);

        } else {

            return response()->json([
                'message' => 'The CarKey you provided does not match the Cart Key for this Cart.',
            ], 400);
        }

    }
    public function delete($id)
    {
        $products = session('cart');
        foreach ($products as $key => $value)
        {
            if ($value['id'] == $id) {
                unset($products [$key]);
            }
        }
        //cart = ShoppingCart::removeItem($id);
        //put back in session array without deleted item
        $request->session()->push('cart',$products);

    }
    /**
     * checkout the cart Items and create and order.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Cart  $cart
     * @return void
     */
    public function checkout(Cart $cart, Request $request)
    {

        if (Auth::guard('api')->check()) {
            $userID = auth('api')->user()->getKey();
        }
        $this->validate($request, [
            'cartKey' => 'required',
            'name' => 'required',
            'adress' => 'required',
            /* 'credit_card_number' => 'required',
            'expiration_year' => 'required',
            'expiration_month' => 'required',
            'cvc' => 'required', */
            ]);
        /* $validator = Validator::make($request->all(), [
            'cartKey' => 'required',
            'name' => 'required',
            'adress' => 'required',
            //'credit_card_number' => 'required',
            //'expiration_year' => 'required',
            //'expiration_month' => 'required',
            //'cvc' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors(),
            ], 400);
        } */

        $cartKey = $request->input('cartKey');
        if ($cart->key == $cartKey) {
            $name = $request->input('name');
            $adress = $request->input('adress');
            //$creditCardNumber = $request->input('credit_card_number');
            $TotalPrice = (float) 0.0;
            $items = $cart->items;

            foreach ($items as $item) {

                $product = Product::find($item->product_id);
                $price = $item->price/$item->quantity;
                //$inStock = $product->UnitsInStock;
                $TotalPrice = $TotalPrice + ($price * $item->quantity);
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

            /**
             * Credit Card information should be sent to a payment gateway for processing and validation,
             * the response should be dealt with here, but since this is a dummy project we'll
                    * just assume that the information is sent and the payment process was done succefully,
                    */

                    $PaymentGatewayResponse = true;
                    $transactionID = md5( uniqid( rand(), true ) );

                    if ( $PaymentGatewayResponse ) {
                        $order = Order::create( [
                            'products' => json_encode( new CartItemCollection( $items ) ),
                            'order_type'=>$cart->cart_type,
                            'totalPrice' => $TotalPrice,
                            'name' => $name,
                            'address' => $adress,
                            'userID' => isset( $userID ) ? $userID : null,
                            'transactionID' => $transactionID,
                        ] );

                        $cart->delete();

                        return response()->json( [
                            'message' => 'you\'re order has been completed succefully, thanks for shopping with us!',
                    'orderID' => $order->getKey(),
                ], 200);
            }
        } else {
            return response()->json([
                'message' => 'The CarKey you provided does not match the Cart Key for this Cart.',
                        ], 400 );
                    }

                }

            }
