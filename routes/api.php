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

// Route::middleware('auth:api')->get('/user', function (Request $request) {
//     return $request->user();
// });

//Route::get('import', 'API\ProductController@import');




Route::group(['middleware' => 'auth:api'], function(){
    Route::Resource('carts', 'API\CartController')->except(['update', 'index']);
    Route::Resource('address', 'API\AddressController')->except(['update']);
    Route::Resource('orders', 'API\OrderController')->except(['update', 'destroy','store'])->middleware('auth:api');
    Route::post('/carts/{cart}', 'API\CartController@addProducts');
    Route::post('/carts/{cart}/checkout', 'API\CartController@checkout');

    Route::post('profile', 'API\ShopUserController@profile');

    Route::post('brands', 'API\BrandController@index');
    Route::post('branches', 'API\BranchController@index');
    Route::post('brands-to-pickup', 'API\BranchController@pickup');
    Route::post('menu', 'API\ProductController@index');
    Route::post('list-items', 'API\ProductController@list_items');
    Route::post('item', 'API\ProductController@show');
    Route::post('item-action', 'API\ProductController@actions');
    Route::post('increment-shares', 'API\ProductController@increment_shares');
    Route::post('add_to_cart', 'API\ShopCart@addToCart')->name('cart.add_ajax');

});
/* Route::fallback(function(){
    return response()->json([
        'status'=>0,
        'message' => 'Not Found!'], 404);
}); */
