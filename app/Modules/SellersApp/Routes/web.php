<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::prefix('sellersapp')->group(
    function () {
        Route::get('/', 'SellersAppController@index');
        
    }
);
//Route::prefix('seller')->group(function(){
    Route::get('password/find/{token}', 'PasswordFindController@find');
    Route::post('password/reset', 'PasswordFindController@reset')->name('reset');
    Route::get('password/success', 'PasswordResetController@success')->name('success');
//});