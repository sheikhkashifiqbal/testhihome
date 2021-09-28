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

Route::get('locations', 'CommonApiController@listLocations');

Route::group(
    ['middleware' => ['global_api', 'customer_app_api', 'auth:api']],
    function () {
        Route::get('faq', 'CommonApiController@listFaq');

        Route::get('banners', 'CommonApiController@listBanners');
        Route::get('categories', 'CommonApiController@listCategories');

        Route::get('sub-locations', 'CommonApiController@listSubLocations');
        Route::post('contact-us', 'CommonApiController@storeFeedback');
        Route::group(
            ['prefix' => 'users'],
            function () {
                Route::group(
                    ['prefix' => 'addresses'],
                    function () {
                        Route::get('/', 'AddressApiController@index');
                        Route::post('/', 'AddressApiController@store');
                        Route::post('/update', 'AddressApiController@update');
                        Route::post('/remove', 'AddressApiController@remove');
                    }
                );
            }
        );
    }
);

Route::group(
        ['middleware' => ['global_api', 'seller_app_api', 'auth:api']], function () {
        Route::group(
                ['prefix' => 'sellers'], function () {
                  Route::post('contact-us', 'CommonApiController@storeFeedback');
                }
        );
    }
);
