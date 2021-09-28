<?php

namespace App\Modules\Auth\Http\Controllers;

use App\Core\MyBaseApiController;
use App\Modules\Auth\Models\ShopUser;
use Carbon\Carbon;
use App\Notifications\PasswordResetRequest;
use App\Notifications\PasswordResetSuccess;
use App\PasswordReset;
use App\Modules\Auth\Transformers\UserTransformer;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Image;

class AuthController extends MyBaseApiController
{

    public function login(Request $request): JsonResponse
    {
        try {
            $this->validateApiRequest(
                ['device_udid', 'device_id', 'password'],
                [
                    'email' => 'required_without:phone|email',
                    'phone' => 'required_without:email',
                ]
            );
            $credentials = [
                'password' => $request->password,
                'role' => null
            ];

            if ($request->has('email')) {
                $credentials['email'] = $request->email;
            }
            if ($request->has('phone')) {
                $credentials['phone'] = $request->phone;
            }

            if (Auth::attempt($credentials)) {
                $user = Auth::user();
                if($user->status){
                  $user->update(['device_id' => $request->device_id]);
                  if ($request->has('device_udid')) {
                      $user->update(['device_udid' => $request->device_udid]);
                  }
                  $token = $user->createToken('MyApp')->accessToken;

                  $data = new UserTransformer($user, $token);
                  $data['token'] = $token;
                  return $this->successResponseWithData($data);
                }else{
                  return $this->errorResponse(trans('auth.account_blocked'), [], 400);
                }

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

    public function register(Request $request): JsonResponse
    {
        try {
            $this->validateApiRequest(
                [
                    'first_name',
                    'last_name',
                    'email',
                    'password',
                    'c_password'
                ],
                [
                    'first_name' => 'string',
                    'last_name' => 'string',
                    'email' => 'email|unique:shop_user',
                    'c_password' => 'same:password',
                ]
            );

            $inputs = $request->only(
                [
                    'first_name',
                    'last_name',
                    'email',
                    'phone',
                    'password'
                ]
            );

            $inputs['password'] = bcrypt($inputs['password']);
            $inputs['status'] = 1;
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
                if ($request->has('device_udid')) {
                    $user->update(['device_udid' => $request->device_udid]);
                }
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
            $inputs['status'] = 1;
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
            $request->validate([
                'email' => '',
            ]);
            $this->validateApiRequest(
                ['email'],
                ['email' => 'required|string|email']
            );
            $user = ShopUser::where('email', $request->email)->where('role', null)->first();

            if (!$user)
                return response()->json([
                    'status' => 0,
                    'message' => trans('passwords.email_not_found')
                ], 404);
            $passwordReset = PasswordReset::updateOrCreate(
                ['email' => $user->email],
                [
                    'email' => $user->email,
                    'token' => str_random(60)
                ]
            );

            if ($user && $passwordReset)
                $user->notify(
                    new PasswordResetRequest($passwordReset->token, 'NULL')
                );
            return response()->json([
                'status' => 1,
                'message' => trans('passwords.sent')
            ]);
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
                    'device_id', 'device_udid'
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
            if ($request->has('device_udid')) {
                $user->update(['device_udid' => $input['device_udid']]);
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
            ShopUser::find(Auth::user()->id)->update(['password' => Hash::make($request->new_password)]);

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

    public function profile(): JsonResponse
    {
        try {
            $user = ShopUser::findOrFail(Auth::user()->id);
            $data = new UserTransformer($user);
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

    public function updateProfile(Request $request)
    {
      try {
          $user = Auth::user();
          $this->validateApiRequest(
              [
                  'first_name',
                  'last_name',
                  'email'
              ],
              [
                  'first_name' => 'string',
                  'last_name' => 'string',
                  'email' => 'email|unique:shop_user,email,' .$user->id
              ]
          );

          $inputs = $request->only(
              [
                  'first_name',
                  'last_name',
                  'email',
                  'phone'
              ]
          );

          if ($request->hasFile('profile_picture')) {
              $image = $request->file('profile_picture');
              $extension = $image->extension();
              $profile_photo = 'profile_photo_' . time() . '.' . $extension;
              $image->move(public_path('/data/customer'), $profile_photo);
              $inputs['profile_picture'] = '/data/customer/' . $profile_photo;
          }

          $user->update($inputs);
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
}
