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

use Illuminate\Support\Facades\Route;

Route::group(
    ['middleware' => ['global_api', 'customer_app_api', 'auth:api']],
    function () {

        Route::group(
            ['prefix' => 'dashboard'],
            function () {
                Route::get('whats_new', 'DashboardApiController@whats_new');
                Route::get('list_products', 'DashboardApiController@listProducts');
                Route::get('list_promotion_products', 'DashboardApiController@listPromotionProducts');

            }
        );
    }
);
