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
            ['prefix' => 'orders'],
            function () {
                Route::get('/', 'OrdersApiController@index');
                Route::post('create', 'OrdersApiController@create');
                Route::post('update', 'OrdersApiController@update');
                Route::post('remove', 'OrdersApiController@destroy');
            }
        );

    }
);
