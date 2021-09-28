<?php
$router->group(['prefix' => 'earnings'], function ($router) {
       $router->get('/', 'OrderEarningController@earnings')->name('admin_order.earnings');
       $router->get('/details/{id}', 'OrderEarningController@earnings_details')->name('admin_order.detail_earning');
});
