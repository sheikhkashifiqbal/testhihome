<?php
namespace App\Http\Controllers\Cashier;
use App\Admin\Models\AdminUser as User;
use App\Http\Controllers\Controller;
use App\Http\Resources\CashierResource as CashierResource;
use App\Models\ShopOrder as Order;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;


class UserController extends Controller
{

    /**
     * login api
     *
     * @return \Illuminate\Http\Response
     */
    public function login(Request $request): JsonResponse {
        $input = $request->all();

        $this->validate(
            $request,
            [
                'email'    => 'required|email',
                'password' => 'required',
            ]
        );

        if (Auth::attempt(['email' => request('email'), 'password' => request('password'), 'role' => 'cashier', 'status' => 1])) {
            $user_data = Auth::user();

            $token = $user_data->createToken('MyApp')->accessToken;

            $user = new CashierResource($user_data, $token);
            /*  dd($user);
             $user['token']=$token; */
            //dd($user);
            /* $tokenResult = $user->createToken('Personal Access Token');
        $token = $tokenResult->token;
        if ($request->remember_me) {
            $token->expires_at = Carbon::now()->addWeeks(4);
        }

        $token->save(); */
            return new JsonResponse([
                                        'status'  =>1,
                                        'message' => 'Successfully Login!',
                                        'data'    => $user,
                                    ], 200
            );
        }
        return new JsonResponse(
            [
                'status'  => 0,
                'message' => 'Wrong email or password.',
                'error'   => [],
            ], 400
        );
    }

    /**
     * Register api
     *
     * @return \Illuminate\Http\Response
     */
    public function register(Request $request): JsonResponse {
        $input = $request->only(
            [
                'first_name',
                'last_name',
                'email',
                'phone',
                'password'
            ]
        );

        $this->validate(
            $request,
            [
                'first_name' => 'required|string',
                'last_name'  => 'required|string',
                'email'      => 'required|email|unique:shop_user',
                'password'   => 'required',
                'c_password' => 'required|same:password',
            ]
        );


        $input['password'] = bcrypt($input['password']);

        $user  = User::create($input);
        $token = $user->createToken('MyApp')->accessToken;

        $user          = $user->toArray();
        $user['token'] = $token;

        return new JsonResponse(
            [
                'status'  => 1,
                'message' => 'Successfully register!',
                'data'    => $user,
            ]
        );
    }

    /**
     * profile api
     *
     * @return \Illuminate\Http\Response
     */
    public function profile(): JsonResponse {

        $user = User::findOrFail(Auth::user()->id);
        return new JsonResponse(
            [
                'status'  => 1,
                'message' => 'success',
                'data'    => $user,
            ]
        );

    }
    public function dashboard(Request $request){

        $user = Auth::user();
        $branch_id = $user->branch_id;

        $input = $request->only( [
            'date',
        ] );
        $this->validate( $request, [
            'date' => 'date|date_format:Y-m-d',
        ] );
        if(isset($input['date'])){
            $date=$input['date'];
        }else{
            $date=Carbon::today();
        }
        //$orders=Order::where( 'branch_id', $branch_id )->whereDate( 'created_at', $date );


        $today_orders = Order::where( 'branch_id', $branch_id )->whereDate( 'created_at', $date )->count();
        $revenue = Order::where( 'branch_id', $branch_id )->whereDate( 'created_at', $date )->where('status','!=' ,8)->sum('total');

        $waiting_to_accept_orders = Order::where( 'branch_id', $branch_id )->whereDate( 'created_at', $date )->where('status',1)->count();
        /* $accepted_orders = Order::where( 'branch_id', $branch_id )->whereDate( 'created_at', $date )->where('status',2)->count(); */
        $inKitchen_orders = Order::where( 'branch_id', $branch_id )->whereDate( 'created_at', $date )->where('status',3)->count();
        $ready_for_pickup_orders = Order::where( 'branch_id', $branch_id )->whereDate( 'created_at', $date )->where('status',4)->count();
        $out_for_delivery_orders = Order::where( 'branch_id', $branch_id )->whereDate( 'created_at', $date )->where('status',5)->count();
        $pickedup_orders = Order::where( 'branch_id', $branch_id )->whereDate( 'created_at', $date )->where('status',6)->count();
        $delivered_orders = Order::where( 'branch_id', $branch_id )->whereDate( 'created_at', $date )->where('status',7)->count();

        $cancelled_orders = Order::where( 'branch_id', $branch_id )->whereDate( 'created_at', $date )->where('status',8)->count();


        /* Total orders and revenue
        revenue =  total cost of all the orders placed for that day including delivery fee */

        return new JsonResponse([
            'status'=>1,
            'data' => [
                'today_orders'=>$today_orders,
                'revenue'=>$revenue,
                //'accepted_orders'=>$accepted_orders,
                'waiting_to_accept_orders'=>$waiting_to_accept_orders,
                'inKitchen_orders'=>$inKitchen_orders,
                'ready_for_pickup_orders'=>$ready_for_pickup_orders,
                'out_for_delivery_orders'=>$out_for_delivery_orders,
                'pickedup_orders'=>$pickedup_orders,
                'delivered_orders'=>$delivered_orders,
                'cancelled_orders'=>$cancelled_orders,

            ],
            ] );
    }
    /**
     * Logout.
     *
     * @param Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout(Request $request)
    {
        Auth::user()->accessTokens()->delete();

        return new JsonResponse([
            'status'=>1,
            'message' => 'Successfully logout.',
            ] );
    }
    /*  try {
     // some code
    } catch (Exception $e) {
        abort(500,$e->getMessage());
        return response()->json(['error' => $e->getMessage()], 500);
    } */
}
