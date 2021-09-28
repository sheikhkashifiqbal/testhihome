<?php
$router->group(['prefix' => 'store'], function ($router) {
    $router->get('/', 'AdminStoreController@index')->name('admin_store.index');
    $router->post('/delete', 'AdminStoreController@delete')->name('admin_store.delete');
    $router->post('/update_info', 'AdminStoreController@updateInfo')->name('admin_store.update');
    $router->get('create', 'AdminStoreController@create')->name('admin_store.create');
    $router->post('/create', 'AdminStoreController@postCreate')->name('admin_store.create');
    $router->get('/seller_details/{id}', 'AdminStoreController@seller_details')->name('admin_store.seller_details');
    $router->get('/edit/{id}', 'AdminStoreController@edit')->name('admin_store.edit');
    $router->post('/edit/{id}', 'AdminStoreController@postEdit')->name('admin_store.edit');
});
