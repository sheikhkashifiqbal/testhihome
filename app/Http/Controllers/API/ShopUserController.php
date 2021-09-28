<?php
namespace App\Http\Controllers\API;
use App\Http\Controllers\Controller;
use App\Models\ShopUser;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ShopUserController extends Controller
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

        if (Auth::attempt(['email' => request('email'), 'password' => request('password')])) {
            $user = Auth::user();

            $token = $user->createToken('MyApp')->accessToken;
            if ($user->cart->count()) {
                $cart = $user->cart;
            } else {
                $user['cart'] = [];

            }
            $user          = $user->toArray();
            $user['token'] = $token;
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

        $user  = ShopUser::create($input);
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

    public function getToken(Request $request): JsonResponse {
        $input = $request->only(
            [
                'device_id'
            ]
        );
        /* $input = $request->all();  */
        $this->validate(
            $request,
            [
                'device_id' => 'required'
            ]
        );


        $user = ShopUser::firstOrNew(['device_id' => $input['device_id']]);
        //$accessTokens=$user->accessTokens();

        if($user->id) {
            $user->api_token= $token = $user->createToken('MyApp')-> accessToken;
            $user->save();
            /* if($user->api_token){
                $token = $user->api_token;
            }else{
                $user->api_token= $token = $user->createToken('MyApp')-> accessToken;
                $user->save();
            } */
        }else {

            $user->first_name = 'Guest';
            $user->last_name  = 'User';
            $user->email      = '';
            $user->password   = '';
            $user->phone      = '';
            $user->is_guest   = 1;
            //$user->api_token= $token;

            $user->save();
            $token           = $user->createToken('MyApp')->accessToken;
            $user->api_token = $token;

            $user->save();
        }
        if ( $user->cart->count() ) {
            $cart = $user->cart;
        } else {
            $user['cart']=[];

        }

        $user          = $user->toArray();
        $user['token'] = $token;

        return new JsonResponse(
            [
                'status'  => 1,
                'message' => 'done',
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

        $user = ShopUser::findOrFail(Auth::user()->id);
        return new JsonResponse(
            [
                'status'  => 1,
                'message' => 'success',
                'data'    => $user,
            ]
        );

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
