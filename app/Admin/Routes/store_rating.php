<?php
$router->group(['prefix' => 'store_rating'], function ($router) {
    $router->get('/{id}', 'StoreRatingsController@index')->name('admin_store_rating.index');
    $router->get('/edit/{id}', 'StoreRatingsController@edit')->name('admin_store_rating.edit');
    $router->post('/update_status', 'StoreRatingsController@updateStatus')->name('admin_store_rating.update_status');
});
