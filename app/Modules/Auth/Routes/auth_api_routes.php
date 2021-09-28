<?php

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::group(
    ['middleware' => ['global_api', 'customer_app_api']],
    function () {
        Route::post('login', 'AuthController@login');
        Route::post('login-social', 'AuthController@loginSocial');
        Route::post('register', 'AuthController@register');
        Route::post('register-social', 'AuthController@registerSocial');
        Route::post('get_token', 'AuthController@getToken');
        Route::post('forget_password', 'AuthController@forgetPassword');
    }
);

Route::group(
    ['middleware' => ['global_api', 'customer_app_api', 'auth:api']],
    function () {
        Route::get('logout', 'AuthController@logout'); //test
        Route::post('profile', 'AuthController@profile');
        Route::post('change-password', 'AuthController@change_password');
        Route::post('update-profile', 'AuthController@updateProfile');
    }
);
