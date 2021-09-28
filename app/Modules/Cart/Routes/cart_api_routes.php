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
    ['middleware' => ['global_api', 'customer_app_api', 'auth:api']],
    function () {
        Route::group(
            ['prefix' => 'cart'],
            function () {
                Route::get('/', 'CartApiController@getUserCart');
                Route::post('add-item', 'CartApiController@addItem');
                Route::post('decrease-item', 'CartApiController@decreaseItem');
                Route::post('remove-item', 'CartApiController@removeItem');
                Route::post('checkout', 'CartApiController@checkout');
                Route::post('destroy', 'CartApiController@destroyUserCart');
            }
        );
    }
);
