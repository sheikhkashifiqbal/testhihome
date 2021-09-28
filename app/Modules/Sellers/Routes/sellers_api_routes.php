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
            ['prefix' => 'sellers'],
            function () {
                Route::get('/', 'SellersApiController@listSellers');
                Route::post('menu', 'SellersApiController@menu');
                Route::post('menu-item', 'SellersApiController@menuItem');
                Route::get('fav-stores', 'SellersApiController@favStores');
                Route::post('fav-stores-actions', 'SellersApiController@favStoresActions');
                Route::get('search-stores', 'SellersApiController@searchStores');


                Route::post('rate-store', 'SellersApiController@rateStore');
            }
        );
    }
);



