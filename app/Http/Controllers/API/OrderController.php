<?php

namespace App\Http\Controllers\API;
use App\Http\Controllers\Controller;
use App\Http\Resources\Order2Resource as OrderResource;
use App\Http\Resources\OrderCollection as OrderCollection;
use App\Models\ShopOrder as Order;

class OrderController extends Controller
{
    /**
     * Display a listing of the User orders.
     *
     * @return \Illuminate\Http\Response
     */

    public function index()
 {
        $userOrders = Order::where( 'user_id', auth()->id() )->orderBy( 'created_at', 'desc' )->limit( 10 )->get();
        return response()->json( [
            'status'=>1,

            'data'=>new OrderCollection( $userOrders ),
        ] ) ;
    }

    /**
    * Display the specified resource.
    *
    * @param  \App\Order  $order
    * @return \Illuminate\Http\Response
    */

    public function show( Order $order )
 {
        if ( $order->user_id == auth()->id() ) {
            return response()->json( [
                'status'=>1,
                'data'=>new OrderResource( $order ),
            ] ) ;
           // return new OrderResource( $order );
        } else {
            return response()->json( [
                'status'=>0,
                'message' => 'The order you\'re trying to view doesn\'t seem to be yours.',
            ], 403 );
        }

    }

}
