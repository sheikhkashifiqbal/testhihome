<?php

namespace App\Modules\SellersApp\Http\Controllers;

use App\Core\MyBaseApiController;
use App\Models\AdminStore;
use App\Modules\Auth\Models\ShopUser;
use App\Modules\Auth\Transformers\UserTransformer;
use App\Modules\Auth\Transformers\SellerTransformer;
use App\Modules\Sellers\Models\ShopBrand;
use App\Modules\RateReview\Services\StoreRatingService;
use App\Models\ShopOrder as Order;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Image;
use Carbon\Carbon;
use App\Notifications\PasswordResetRequest;
use App\Notifications\PasswordResetSuccess;
use App\PasswordReset;

class AuthController extends MyBaseApiController
{

    public function login(Request $request): JsonResponse
    {

        try {
            $this->validateApiRequest(
                ['device_id', 'password'],
                [
                    'email' => 'required_without:phone|email',
                    'phone' => 'required_without:email',
                ]
            );
            $credentials = [
                'password' => $request->password,
                'role' => 'seller'
            ];

            if ($request->has('email')) {
                $credentials['email'] = $request->email;
            }
            if ($request->has('phone')) {
                $credentials['phone'] = $request->phone;
            }
            if (Auth::attempt($credentials)) {
                $user = Auth::user();
                $store = AdminStore::find($user->store_id);
                if($store->approval == 1){
                  $user->update(['device_id' => $request->device_id, 'real_password' => $request->password]);
                  if ($request->has('device_udid')) {
                      $user->update(['device_udid' => $request->device_udid]);
                  }
                  $token = $user->createToken('MyApp')->accessToken;
                  $data = new UserTransformer($user, $token);
                  $data['token'] = $token;
                  return $this->successResponseWithData($data);
                }else{
                  $error = trans('auth.seller can not login');
                }

            }else{
              $error = trans('auth.Wrong email or password');
            }

            return $this->errorResponse($error, [], 400);
        } catch (\Exception $e) {
            if (app()->environment('local')) {
                $message = $e->getMessage() . ' file : ' . $e->getFile() . ' In Line : ' . $e->getLine();
            } else {
                $message = trans('common.Something Went Wrong');
            }
            return $this->errorResponse($message);
        }
    }

    public function register(Request $request): JsonResponse
    {

        $license_photo_file_name = '';
        try {
            DB::beginTransaction();
            $this->validateApiRequest(
                [
                    'first_name',
                    'last_name',
                    'email',
                    'password',
                    'c_password',
                    'legal_business_name',
                    'legal_business_email',
                    'c_legal_business_email',
                    'legal_business_phone',
                    'seller_eid',
                    'address',
                    'license_id',
                    'license_start_date',
                    'license_end_date',
                    'city',
                    'emirates_id',
                    'pincode',
                    'contact_us_first_name',
                    'contact_us_last_name',
                    'contact_us_email',
                    'license_photo',
                    //'logo',
                    'accept_orders',
                    'lat',
                    'long',
                ],
                [
                    'first_name' => 'string',
                    'last_name' => 'string',
                    'email' => 'email|unique:shop_user',
                    'c_password' => 'same:password',
                    'c_legal_business_email' => 'same:legal_business_email',
                    'accept_orders' => 'in:0,1',
                    'lat' => 'string',
                    'long' => 'string',
                ]
            );

            $brand = ShopBrand::create(
                [
                    'name' => $request->legal_business_name,
                    'alias' => str_slug($request->legal_business_name)
                ]
            );

            $store_inputs = $request->only(
                [
                    'legal_business_email',
                    'legal_business_phone',
                    'seller_eid',
                    'emirates_id',
                    'address',
                    'license_id',
                    'license_start_date',
                    'license_end_date',
                    'city',
                    'pincode',
                    'contact_us_first_name',
                    'contact_us_last_name',
                    'contact_us_email',
                    'phone',
                    'accept_orders',
                    'lat',
                    'long',
                ]
            );
            $store_inputs['brand_id'] = $brand->id;

            if ($request->hasFile('license_photo')) {
                $image = $request->file('license_photo');
                $extension = $image->extension();
                $file_name = env('AWS_LICENSE_FOLDER_PATH').'/license_photo_' . time() . '.' . $extension;
                $path = $request->file('license_photo')->storeAs('', $file_name,'s3');
                $store_inputs['license_photo'] = $file_name;
            }

            if ($request->hasFile('logo')) {
                $image = $request->file('logo');
                $extension = $image->extension();
                $file_name = env('AWS_LOGOS_FOLDER_PATH').'/logo_photo_' . time() . '.' . $extension;
                $path = $request->file('logo')->storeAs('', $file_name,'s3');
                $store_inputs['logo'] = $file_name;
                $store_inputs['logo2'] = $file_name;
            }

            $store = AdminStore::create($store_inputs);

            $details['lang'] = 'en';
            $details['title'] = $request->legal_business_name;
            $store->descriptions()->create($details);


            $user_inputs = $request->only(
                [
                    'first_name',
                    'last_name',
                    'email',
                    'phone',
                    'password',
                    'device_udid'
                ]
            );

            $user_inputs['real_password'] = $user_inputs['password'];
            $user_inputs['password'] = bcrypt($user_inputs['password']);

            $user_inputs['brand_id'] = $brand->id;
            $user_inputs['store_id'] = $store->id;
            $user_inputs['role'] = 'seller';
            $user = ShopUser::create($user_inputs);

            $token = $user->createToken('MyApp')->accessToken;
            $data = new UserTransformer($user, $token);

            DB::commit();
            return $this->successResponseWithData($data);
        } catch (\Exception $e) {
            DB::rollBack();
            if (file_exists(public_path('/data/seller/licenses/' . $license_photo_file_name))) {
                // unlink(public_path('/data/seller/licenses/' . $license_photo_file_name));
            }
            if (app()->environment('local')) {
                $message = $e->getMessage() . ' file : ' . $e->getFile() . ' In Line : ' . $e->getLine();
            } else {
                $message = trans('common.Something Went Wrong');
            }
            return $this->errorResponse($message);
        }
    }

    public function updateProfile(Request $request): JsonResponse
    {

        $license_photo_file_name = '';
        try {
            DB::beginTransaction();
            $this->validateApiRequest(
                [
                    'user_id',
                    'first_name',
                    'last_name',
                    'email',
                    /* 'password',
                  'c_password', */
                    'legal_business_name',
                    'legal_business_email',
                    'c_legal_business_email',
                    'legal_business_phone',
                    'emirates_id',
                    'address',
                    'license_id',
                    'license_start_date',
                    'license_end_date',
                    'city',
                    'pincode',
                    'contact_us_first_name',
                    'contact_us_last_name',
                    'contact_us_email',
                    //  'license_photo',
                    'accept_orders',
                ],
                [
                    'user_id' => 'exists:shop_user,id',
                    'first_name' => 'string',
                    'last_name' => 'string',
                    //                    'email'                  => 'email|unique:shop_user',
                    //     'c_password'             => 'same:password',
                    //      'c_legal_business_email' => 'same:legal_business_email',
                    'accept_orders' => 'in:0,1',
                ],
                [
                    'user_id',
                    'emirates_id',
                    'license_id'
                ]
            );

            $user = ShopUser::find($request->user_id);

            $brand = ShopBrand::find($user->brand_id);
            $brand->update(
                [
                    'name' => $request->legal_business_name,
                    'alias' => str_slug($request->legal_business_name)
                ]
            );

            $store_inputs = $request->only(
                [
                    'legal_business_email',
                    'legal_business_phone',
                    'emirates_id',
                    'address',
                    'license_id',
                    'license_start_date',
                    'license_end_date',
                    'city',
                    'pincode',
                    'contact_us_first_name',
                    'contact_us_last_name',
                    'contact_us_email',
                    'accept_orders',
                ]
            );
            $store_inputs['brand_id'] = $brand->id;

            if ($request->hasFile('license_photo')) {
                $image = $request->file('license_photo');
                $extension = $image->extension();
                $file_name = env('AWS_LICENSE_FOLDER_PATH').'/license_photo_' . time() . '.' . $extension;
                $path = $request->file('license_photo')->storeAs('', $file_name,'s3');
                $store_inputs['license_photo'] = $file_name;
            }

            if ($request->hasFile('logo')) {
                $image = $request->file('logo');
                $extension = $image->extension();
                $file_name = env('AWS_LOGOS_FOLDER_PATH').'/logo_photo_' . time() . '.' . $extension;
                $path = $request->file('logo')->storeAs('', $file_name,'s3');
                $store_inputs['logo'] = $file_name;
                $store_inputs['logo2'] = $file_name;
            }


            $store = AdminStore::find($user->store_id);
            $store->update($store_inputs);

            $details['lang'] = 'en';
            $details['title'] = $request->legal_business_name;
            $store->descriptions()->where('lang', 'en')->update($details);

            $user_inputs = $request->only(
                [
                    'first_name',
                    'last_name',
                    'email',
                    'phone',
                    //    'password'
                ]
            );

            // $user_inputs['password'] = bcrypt($user_inputs['password']);
            $user_inputs['brand_id'] = $brand->id;
            $user_inputs['store_id'] = $store->id;
            $user_inputs['role'] = 'seller';
            $user->update($user_inputs);

            $token = $user->createToken('MyApp')->accessToken;
            $data = new UserTransformer($user, $token);

            DB::commit();
            return $this->successResponseWithData($data);
        } catch (\Exception $e) {
            DB::rollBack();
            if (file_exists(public_path('/data/seller/licenses/' . $license_photo_file_name))) {
                unlink(public_path('/data/seller/licenses/' . $license_photo_file_name));
            }
            if (file_exists(public_path('/data/logo/thumbs/' . $logo_name))) {
                unlink(public_path('/data/logo/thumbs/' . $logo_name));
            }
            if (file_exists(public_path('/data/logo/' . $logo_name))) {
                unlink(public_path('/data/logo/' . $logo_name));
            }
            if (app()->environment('local')) {
                $message = $e->getMessage() . ' file : ' . $e->getFile() . ' In Line : ' . $e->getLine();
            } else {
                $message = trans('common.Something Went Wrong');
            }
            return $this->errorResponse($message);
        }
    }

    public function loginSocial(Request $request): JsonResponse
    {
        try {
            $this->validateApiRequest(
                ['source', 'social_media_id'],
                [
                    'source' => 'in:facebook,instagram,google',
                    'social_media_id' => 'max:255',
                ],
                ['social_media_id']
            );

            $credentials = [
                'source' => $request->source,
                'social_media_id' => $request->social_media_id,
            ];

            $user = ShopUser::where($credentials)->first();

            if (!$user) {
                return $this->errorResponse('user not found', [], Response::HTTP_NOT_FOUND);
            }

            if (Auth::loginUsingId($user->id)) {
                $user = Auth::user();
                $user->update(['device_id' => $request->device_id]);
                $token = $user->createToken('MyApp')->accessToken;
                $data = new UserTransformer($user, $token);
                $data['token'] = $token;
                return $this->successResponseWithData($data);
            }

            if ($request->has('email')) {

                $error = trans('auth.Wrong email or password');
            } else {
                $error = trans('auth.Wrong phone or password');
            }

            return $this->errorResponse($error, [], 400);
        } catch (\Exception $e) {
            if (app()->environment('local')) {
                $message = $e->getMessage() . ' file : ' . $e->getFile() . ' In Line : ' . $e->getLine();
            } else {
                $message = trans('common.Something Went Wrong');
            }
            return $this->errorResponse($message);
        }
    }

    public function registerSocial(Request $request): JsonResponse
    {
        try {
            $this->validateApiRequest(
                [
                    'source',
                    'social_media_id',
                    'first_name',
                    'email',
                ],
                [
                    'source' => 'in:facebook,instagram,google',
                    'social_media_id' => 'max:255|unique:shop_user',
                    'first_name' => 'string',
                    'last_name' => 'string',
                    'email' => 'email|unique:shop_user',
                ]
            );

            $inputs = $request->only(
                [
                    'source',
                    'social_media_id',
                    'first_name',
                    'last_name',
                    'email',
                ]
            );

            $rand = substr(str_shuffle("123456789abcdefghijkmnopqrstuvwxyzABCDEFGHJKLMNPQRSTUVWXYZ"), 0, 10);
            $inputs['password'] = bcrypt($rand);
            $user = ShopUser::create($inputs);
            $token = $user->createToken('MyApp')->accessToken;
            $data = new UserTransformer($user, $token);

            return $this->successResponseWithData($data);
        } catch (\Exception $e) {
            if (app()->environment('local')) {
                $message = $e->getMessage() . ' file : ' . $e->getFile() . ' In Line : ' . $e->getLine();
            } else {
                $message = trans('common.Something Went Wrong');
            }
            return $this->errorResponse($message);
        }
    }

    public function forgetPassword(Request $request)
    {
        try {
            $this->validateApiRequest(
                ['email'],
                [
                    'email' => 'email|exists:shop_user,email|max:250'
                ]
            );

            $user = ShopUser::where('email', $request->email)
                ->where('role', 'seller')
                ->firstOrFail();

                $passwordReset = PasswordReset::updateOrCreate(
                    ['email' => $user->email],
                    [
                        'email' => $user->email,
                        'token' => str_random(60)
                    ]
                );


            if ($user && $passwordReset)
                $user->notify(
                    new PasswordResetRequest($passwordReset->token, $user->role)
                );

            return $this->successEmptyResponse(trans('passwords.sent'));
        } catch (ModelNotFoundException $e) {
            if (app()->environment('local')) {
                $message = 'User Not Found';
            } else {
                $message = trans('common.Something Went Wrong');
            }
            return $this->errorResponse($message);
        } catch (\Exception $e) {
            if (app()->environment('local')) {
                $message = $e->getMessage() . ' file : ' . $e->getFile() . ' In Line : ' . $e->getLine();
            } else {
                $message = trans('common.Something Went Wrong');
            }
            return $this->errorResponse($message);
        }
    }

    public function getToken(Request $request): JsonResponse
    {
        try {
            $this->validateApiRequest(['device_id'], [], ['device_id']);
            $input = $request->only(
                [
                    'device_id'
                ]
            );

            $user = ShopUser::firstOrNew(['device_id' => $input['device_id']]);

            if ($user->id) {
                $user->api_token = $user->createToken('MyApp')->accessToken;
                $user->save();
            } else {
                $user->first_name = 'Guest';
                $user->last_name = 'User';
                $user->email = '';
                $user->password = '';
                $user->phone = '';
                $user->is_guest = 1;
                $user->api_token = $user->createToken('MyApp')->accessToken;
                $user->save();
            }
            //            if ($user->cart->count()) {
            //                $cart = $user->cart;
            //            } else {
            //                $user['cart'] = [];
            //            }
            $data = new UserTransformer($user, $user->api_token);
            $data['token'] = $user->api_token;
            return $this->successResponseWithData($data);
        } catch (\Exception $e) {
            if (app()->environment('local')) {
                $message = $e->getMessage() . ' file : ' . $e->getFile() . ' In Line : ' . $e->getLine();
            } else {
                $message = trans('common.Something Went Wrong');
            }
            return $this->errorResponse($message);
        }
    }

    public function profile(): JsonResponse
    {
        try {
            $user = ShopUser::With('seller')->findOrFail(Auth::user()->id);

            $data = new SellerTransformer($user);
            return $this->successResponseWithData($data);
        } catch (\Exception $e) {
            if (app()->environment('local')) {
                $message = $e->getMessage() . ' file : ' . $e->getFile() . ' In Line : ' . $e->getLine();
            } else {
                $message = trans('common.Something Went Wrong');
            }
            return $this->errorResponse($message);
        }
    }

    public function change_password(Request $request)
    {
        $this->validateApiRequest(
            [
                //'current_password',
                'new_password',
                'new_confirm_password'
            ],
            [
                'current_password' => 'MatchOldPassword',
                'new_confirm_password' => 'same:new_password'
            ]
        );


        try {
            ShopUser::find(Auth::user()->id)->update(['password' => Hash::make($request->new_password), 'real_password' => $request->new_password]);

            return $this->successResponseWithData([], trans('passwords.success'));
        } catch (\Exception $e) {
            if (app()->environment('local')) {
                $message = $e->getMessage() . ' file : ' . $e->getFile() . ' In Line : ' . $e->getLine();
            } else {
                $message = trans('common.Something Went Wrong');
            }
            return $this->errorResponse($message);
        }
    }

    public function update_address(Request $request)
    {

        $this->validateApiRequest(
            [
                //'user_id',
                'lat',
                'long',
                'address'
            ],
            [
                'lat' => 'string',
                'long' => 'string',
                'address' => 'string'
            ]
        );
        try {
            //$user = ShopUser::find($request->user_id);

            $user = ShopUser::find(Auth::user()->id);

            $store = AdminStore::find($user->store_id);
            $store->update(['lat' => $request->lat, 'long' => $request->long, 'address' => $request->address]);


            return $this->successResponseWithData([], trans('auth.address change successfully.'));
        } catch (\Exception $e) {
            if (app()->environment('local')) {
                $message = $e->getMessage() . ' file : ' . $e->getFile() . ' In Line : ' . $e->getLine();
            } else {
                $message = trans('common.Something Went Wrong');
            }
            return $this->errorResponse($message);
        }
    }

    public function logout(Request $request)
    {
        try {
            Auth::user()->accessTokens()->delete();
            Auth::user()->update(['device_id' => null]);
            return $this->successEmptyResponse();
        } catch (\Exception $e) {
            if (app()->environment('local')) {
                $message = $e->getMessage() . ' file : ' . $e->getFile() . ' In Line : ' . $e->getLine();
            } else {
                $message = trans('common.Something Went Wrong');
            }
            return $this->errorResponse($message);
        }
    }
    public function dashboard(Request $request)
    {

        $user = Auth::user();
        $store_id = $user->store_id;

        $input = $request->only([
            'period'
        ]);

        $today = Carbon::today();
        $to_date = $today->toDateString();
        if (isset($input['period'])) {
            $from_date = $this->dashboardFromDate($today, $input['period']);
        } else {
           $from_date = $to_date;
        }

        $today_orders = Order::where('store_id', $store_id)->whereDate('created_at', '>=', $from_date)->whereDate('created_at', '<=', $to_date)->count();
        $revenue = Order::where('store_id', $store_id)->whereDate('created_at', '>=', $from_date)->whereDate('created_at', '<=', $to_date)->where('status', '!=', 6)->sum('total');

        $waiting_to_accept_orders = Order::where('store_id', $store_id)->whereDate('created_at', '>=', $from_date)->whereDate('created_at', '<=', $to_date)->where('status', 1)->count();
        $accepted_orders = Order::where('store_id', $store_id)->whereDate('created_at', '>=', $from_date)->whereDate('created_at', '<=', $to_date)->where('status', 2)->count();
        $inKitchen_orders = Order::where('store_id', $store_id)->whereDate('created_at', '>=', $from_date)->whereDate('created_at', '<=', $to_date)->where('status', 3)->count();

        $out_for_delivery_orders = Order::where('store_id', $store_id)->whereDate('created_at', '>=', $from_date)->whereDate('created_at', '<=', $to_date)->where('status', 4)->count();

        $delivered_orders = Order::where('store_id', $store_id)->whereDate('created_at', '>=', $from_date)->whereDate('created_at', '<=', $to_date)->where('status', 5)->count();

        $cancelled_orders = Order::where('store_id', $store_id)->whereDate('created_at', '>=', $from_date)->whereDate('created_at', '<=', $to_date)->where('status', 6)->count();


        /* Total orders and revenue
        revenue =  total cost of all the orders placed for that day including delivery fee */
        $rating = StoreRatingService::getStoreAverageRating($store_id);

        return new JsonResponse([
            'status' => 1,
            'data' => [
                'today_orders' => $today_orders,
                'revenue' => $revenue,
                'accepted_orders' => $accepted_orders,
                'waiting_to_accept_orders' => $waiting_to_accept_orders,
                'inKitchen_orders' => $inKitchen_orders,
                'out_for_delivery_orders' => $out_for_delivery_orders,
                'delivered_orders' => $delivered_orders,
                'cancelled_orders' => $cancelled_orders,
                'rating_average'  => $rating['average_rating'],
                'rating_count'    => $rating['rating_count'],

            ],
        ]);
    }

    private function dashboardFromDate($to_date, $period)
    {
        if($period == 'day')
          return $to_date->toDateString();

        if($period == 'week' )
          return  $to_date->subDays(7)->toDateString();

        if($period == 'month' )
          return  $to_date->subDays(30)->toDateString();

        if($period == 'year' )
          return  $to_date->subDays(365)->toDateString();

        return $to_date->toDateString();
    }
}
