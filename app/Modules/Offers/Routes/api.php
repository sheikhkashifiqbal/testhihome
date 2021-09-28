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
        Route::get('get-offers', 'OffersController@getOffers');
        Route::get('offer-sellers', 'OffersController@getSellersByOfferId');
        Route::get('offer-seller-products', 'OffersController@getSellerProductsByOfferId');
        Route::get('seller-offers', 'OffersController@getOfferForSeller');
        Route::get('validate-offer', 'OffersController@validateOffer');

    }
);
