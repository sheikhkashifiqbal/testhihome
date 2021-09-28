<?php

/*
|--------------------------------------------------------------------------
| Cashier Routes
|--------------------------------------------------------------------------
|
| Here is where you can register Cashier routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "cashier" middleware group. Enjoy building your API!
|
 */

// Route::middleware('auth:api')->get('/user', function (Request $request) {
//     return $request->user();
// });
Route::post('login', 'Cashier\UserController@login');




Route::group(['middleware' => 'auth:api'], function(){

    Route::post('profile', 'Cashier\UserController@profile');
    Route::get('logout', 'Cashier\UserController@logout');
    Route::post('dashboard', 'Cashier\UserController@dashboard');

    Route::post('menu', 'Cashier\ProductController@index');
    Route::post('list-items', 'Cashier\ProductController@list_items');
    Route::post('item', 'Cashier\ProductController@show');
    Route::post('item-action', 'Cashier\ProductController@actions');
    Route::post('increment-shares', 'Cashier\ProductController@increment_shares');
   // Route::Resource('orders', 'Cashier\OrderController')->except(['index','update', 'destroy','store']);
    Route::post('orders', 'Cashier\OrderController@index');
    Route::post('previous_orders', 'Cashier\OrderController@previous');
    Route::post('canceled_orders', 'Cashier\OrderController@canceled');
//    Route::post('change_order_status', 'Cashier\OrderController@change_status');

    Route::get('new_orders_notification', 'Cashier\OrderController@new_orders_notification');
    Route::get('branch', 'Cashier\BranchController@index');
    Route::post('change_accept_orders_status', 'Cashier\BranchController@change_accept_orders_status');

});
/* Route::fallback(function(){
    return response()->json([
        'status'=>0,
        'message' => 'Not Found!'], 404);
}); */
