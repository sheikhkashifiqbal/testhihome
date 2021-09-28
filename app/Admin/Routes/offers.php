<?php
$router->group(['prefix' => 'offers'], function ($router) {
    $router->get('/', 'OfferController@index')->name('admin_offers.index');
    $router->get('/create', 'OfferController@create')->name('admin_offers.create');
    $router->post('/create', 'OfferController@store')->name('admin_offers.create');
    $router->post('/delete', 'OfferController@delete')->name('admin_offers.delete');
    $router->get('/edit/{id}', 'OfferController@edit')->name('admin_offers.edit');
    $router->post('/edit/{id}', 'OfferController@postEdit')->name('admin_offers.edit');
});
