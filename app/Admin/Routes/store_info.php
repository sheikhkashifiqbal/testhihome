<?php
$router->group(['prefix' => 'store_info'], function ($router) {
    $router->get('/', 'AdminStoreController@index')->name('admin_store_info.index');
    $router->post('/delete', 'AdminStoreInfoController@deleteList')->name('admin_store_info.delete');
    $router->post('/update_info', 'AdminStoreController@updateInfo')->name('admin_store_info.update');
    $router->post('/update_banners/{id}', 'AdminStoreController@updateBanners')->name('admin_store_info.update-banners');
});
