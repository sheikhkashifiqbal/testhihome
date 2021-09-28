<?php
$router->group(['prefix' => 'feedbacks'], function ($router) {
    $router->get('/', 'FeedbackController@index')->name('admin_feedbacks.index');
    $router->get('/details/{id}', 'FeedbackController@details')->name('admin_feedbacks.details');
});
