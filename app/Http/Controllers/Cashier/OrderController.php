<?php

namespace App\Http\Controllers\Cashier;
use App\Http\Controllers\Controller;
use App\Http\Resources\CashierOrderCollection as OrderCollection;
use App\Http\Resources\CashierOrderResource as OrderResource;
use App\Models\ShopOrder as Order;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

//use App\Http\Resources\Order2Collection as OrderCollection;

class OrderController extends Controller
{
    /**
     * Display a listing of the User orders.
     *
     * @return \Illuminate\Http\Response
     */

    public function index(Request $request) {
        $user = Auth::user();

        $branch_id = $user->branch_id;

        $input = $request->only( [
            'order_type',
        ] );
        $this->validate( $request, [
            'order_type' => 'in:pickup,delivery',
        ] );
        /* $orders = Order::where( 'branch_id', $branch_id )->whereIn( 'status', [1, 2, 3, 4, 5] )->orderBy( 'created_at', 'desc' );
        */
        $orders = Order::where( 'branch_id', $branch_id )->whereDate( 'created_at', '=', Carbon::today() )->orderBy( 'status', 'asc' )->orderBy( 'created_at', 'desc' );

        if ( isset( $input['order_type'] ) ) {
            $orders = $orders->where( 'order_type', $input['order_type'] );
        }
        $orders = $orders->paginate();
        $orderArr = $orders->toArray();
        //return new CashierOrderCollection( $orders );
        return response()->json(
            [
                'status'=>1,
                'links' => [
                    'current_page'=>$orderArr['current_page'],
                    'last_page'=>$orderArr['last_page'],

                    'from'=>$orderArr['from'],
                    'to'=>$orderArr['to'],

                    'first_page_url'=>$orderArr['first_page_url'],
                    'prev_page_url'=>$orderArr['prev_page_url'],
                    'next_page_url'=>$orderArr['next_page_url'],
                    'last_page_url'=>$orderArr['last_page_url'],

                    'path'=>$orderArr['path'],
                    'per_page'=>$orderArr['per_page'],

                    'total'=>$orderArr['total'],
                ],
                'data'=>new OrderCollection( $orders ),

            ] ) ;
        }

        public function previous( Request $request ) {

            //1-def yesterday
            //2-this_week = 1
            //3-date App\User::whereDate( 'created_at', '2017-07-14' )->get()

            $user = Auth::user();

            $branch_id = $user->branch_id;

            $input = $request->only( [
                'order_type',
                'this_week',
                'date',
            ] );
            $this->validate( $request, [
                'order_type' => 'in:pickup,delivery',
                'this_week' => 'numeric|min:0|max:1',
                'date' => 'date|date_format:Y-m-d',
            ] );
            $orders = Order::where( 'branch_id', $branch_id );
            if ( isset( $input['order_type'] ) && $input['order_type'] ) {
                $orders = $orders->where( 'order_type', $input['order_type'] );
            }
            if ( isset( $input['this_week'] ) && $input['this_week'] ) {
                $now = Carbon::now();
                $this_week = $now->startOfWeek( Carbon::SATURDAY );

                $orders = $orders->where( 'created_at', '>=', $this_week );
            } else if ( isset( $input['date'] ) && $input['date'] ) {
                $orders = $orders->whereDate( 'created_at', $input['date'] );
            } else {
                $orders = $orders->whereDate( 'created_at', '=', Carbon::now()->addDay( -1 ) );
            }
            $orders = $orders->orderBy( 'status', 'asc' )->orderBy( 'created_at', 'desc' )->paginate();

            $orderArr = $orders->toArray();
            return response()->json(
                [
                    'status'=>1,
                    'links' => [
                        'current_page'=>$orderArr['current_page'],
                        'last_page'=>$orderArr['last_page'],

                        'from'=>$orderArr['from'],
                        'to'=>$orderArr['to'],

                        'first_page_url'=>$orderArr['first_page_url'],
                        'prev_page_url'=>$orderArr['prev_page_url'],
                        'next_page_url'=>$orderArr['next_page_url'],
                        'last_page_url'=>$orderArr['last_page_url'],

                        'path'=>$orderArr['path'],
                        'per_page'=>$orderArr['per_page'],

                        'total'=>$orderArr['total'],
                    ],
                    'data'=>new OrderCollection( $orders ),

                ] ) ;
            }

            public function canceled( Request $request ) {
                $user = Auth::user();

                $branch_id = $user->branch_id;

                $input = $request->only( [
                    'order_type',
                ] );
                $this->validate( $request, [
                    'order_type' => 'in:pickup,delivery',
                ] );

                $orders = Order::where( 'branch_id', $branch_id )->where( 'status', 8 )->orderBy( 'created_at', 'desc' );
                if ( isset( $input['order_type'] ) ) {
                    $orders = $orders->where( 'order_type', $input['order_type'] );
                }
                $orders = $orders->paginate();
                $orderArr = $orders->toArray();
                return response()->json(
                    [
                        'status'=>1,
                        'links' => [
                            'current_page'=>$orderArr['current_page'],
                            'last_page'=>$orderArr['last_page'],

                            'from'=>$orderArr['from'],
                            'to'=>$orderArr['to'],

                            'first_page_url'=>$orderArr['first_page_url'],
                            'prev_page_url'=>$orderArr['prev_page_url'],
                            'next_page_url'=>$orderArr['next_page_url'],
                            'last_page_url'=>$orderArr['last_page_url'],

                            'path'=>$orderArr['path'],
                            'per_page'=>$orderArr['per_page'],

                            'total'=>$orderArr['total'],
                        ],
                        'data'=>new OrderCollection( $orders ),

                    ] ) ;
                }
                /**
                * Display the specified resource.
                *
                * @param  \App\Order  $order
                * @return \Illuminate\Http\Response
                */

                public function change_status( Request $request ) {
                    $user = Auth::user();

                    $branch_id = $user->branch_id;

                    $input = $request->only( [
                        'order_id',
                        'status',
                        'cancellation_comment',
                    ] );
                    $this->validate( $request, [
                        'order_id' => 'required|integer',
                        'status' => 'required|integer|between:1,8',
                    ] );
                    $order = Order::findOrFail( $input['order_id'] );

                    if ( $order->branch_id != $branch_id ) {
                        return response()->json( [
                            'status'=>0,
                            'message' => 'The order you\'re trying to view not exists.',
                        ], 403 );
                    } else {
                        $order->status= $input['status'];
                        if(isset($input['cancellation_comment'])&&$input['cancellation_comment']!=''){
                            $order->comment= $input['cancellation_comment'];
                        }

                        $order->save();
                        return response()->json( [
                            'status'=>1,
                            'data'=>new OrderResource( $order ),
                        ] ) ;
                    }

    }
    public function new_orders_notification(){
        $user = Auth::user();
        $branch_id = $user->branch_id;
        if($branch_id){
            //$orders = Order::where( 'branch_id', $branch_id )->orderBy( 'status', 'asc' )->orderBy( 'created_at', 'desc' );
            $pickup_orders = Order::where('branch_id',$branch_id)
            ->where('order_type','pickup')
            ->where( 'status',1 )
            ->whereDate( 'created_at', ' = ', Carbon::today() )
            ->count();

            $delivery_orders = Order::where('branch_id',$branch_id)
            ->where('order_type','delivery')
            ->where( 'status', 1 )
            ->whereDate( 'created_at', ' = ', Carbon::today() )
            ->count();

            return response()->json( [
                'status'=>1,
                'data'=>[
                    'delivery'=>$delivery_orders,
                    'pickup'=>$pickup_orders,
                ],
            ] ) ;
        }else{
            return response()->json( [
                'status'=>0,
                'message' => 'Not Allowed!',
                        ], 403 );

                    }

                }

            }
