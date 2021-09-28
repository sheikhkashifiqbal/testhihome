<?php

use Illuminate\Http\Request;

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
        Route::post('rate-seller', 'StoreRatingController@create');
        Route::get('seller-reviews', 'StoreRatingController@getSellerReviews');
        Route::get('my-reviews', 'StoreRatingController@getCustomerSellersReviews');

        Route::post('rate-order', 'OrderRatingController@create');

    }
);

Route::group(
        ['middleware' => ['global_api', 'seller_app_api', 'auth:api']], function () {
        Route::group(
                ['prefix' => 'sellers'], function () {
                  Route::get('store-reviews', 'StoreRatingController@getSellerCustomerReviews');
                  Route::get('products-ratings', 'ProductRatingController@getProductRatingList');
                }
        );
    }
);
