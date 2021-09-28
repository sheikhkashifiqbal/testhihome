<?php

use Illuminate\Support\Facades\Route;

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
        ['middleware' => ['global_api', 'seller_app_api']], function () {
    Route::group(
            ['prefix' => 'sellers'], function () {
        Route::post('login', 'AuthController@login');
        Route::post('register', 'AuthController@register');
        Route::post('update-profile', 'AuthController@updateProfile');


        Route::post('login-social', 'AuthController@loginSocial');
        Route::post('register-social', 'AuthController@registerSocial');
        Route::post('get_token', 'AuthController@getToken');
        Route::post('forget_password', 'AuthController@forgetPassword');

        Route::post('password/create', 'PasswordResetController@create');
//        Route::get('password/find/{token}', 'PasswordResetController@find');


    }
    );
}
);

Route::get('sellers/orders/list-orders-status', 'OrdersApiController@listOrdersStatus');

Route::group(
        ['middleware' => ['global_api', 'seller_app_api', 'auth:api'], 'prefix' => 'sellers'], function () {
    Route::get('logout', 'AuthController@logout'); //test
    Route::post('profile', 'AuthController@profile');
    Route::post('dashboard', 'AuthController@dashboard');
    Route::post('change-password', 'AuthController@change_password');
    Route::post('update_address', 'AuthController@update_address');
    Route::get('categories', '\App\Modules\Common\Http\Controllers\CommonApiController@listCategories');


    Route::group(
            ['prefix' => 'orders'], function () {
        Route::get('/', 'OrdersApiController@index');
        Route::get('/dashboard', 'OrdersApiController@dashboarOrders');
        Route::get('/order-details', 'OrdersApiController@getOrderDetails');
        Route::post('change_order_status', 'OrdersApiController@change_status');
    }
    );


    Route::group(
            ['prefix' => 'products'], function () {
        Route::get('/', 'ProductsApiController@index');
        Route::post('change_product_status', 'ProductsApiController@change_status');
        Route::post('out_of_stock', 'ProductsApiController@change_stock');
        Route::post('create', 'ProductsApiController@create');
        Route::post('update', 'ProductsApiController@update')->name('seller.update.product');
        Route::post('add-images', 'ProductsApiController@addProductImages');
        Route::post('remove-image', 'ProductsApiController@removeProductImage');
        Route::post('remove-main-image', 'ProductsApiController@removeProductMainImage');
        Route::post('remove', 'ProductsApiController@destroy');
    }
    );
}
);
