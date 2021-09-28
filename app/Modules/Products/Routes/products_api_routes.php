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
            ['prefix' => 'products'],
            function () {
                Route::get('top-rated', 'ProductsApiController@topRated');
                Route::get('top-review', 'ProductsApiController@topReview');
                Route::get('fav-products', 'ProductsApiController@favProducts');
                Route::get('is-fav', 'ProductsApiController@productIsFav');
                Route::post('fav-products-actions', 'ProductsApiController@favProductsActions');
                Route::get('search-products', 'ProductsApiController@searchProducts');


                Route::post('rate-product', 'ProductsApiController@rateProduct');

            }
        );
    }
);
