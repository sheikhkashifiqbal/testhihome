<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Feedback config.
    |--------------------------------------------------------------------------
    */

    // Feedbacks table name
    'table' => 'feedbacks',

    // API URI
    'uri' => '/api/sellers/feedbacks',

    // Feedback validation rule
    'rule' => 'required|string|min:5|max:1000',

    // Feedback controller middleware
    'middleware' => 'api',

    // Whether to email developer
    'mail_to_developer' => true,

    // Mail subject (to dev)
    'mail_subject' => 'Received a feedback',

    // developer info
    'developer' => [

        'address' => env('MAIL_FROM_ADDRESS'),

        'name' => env('MAIL_FROM_NAME'),

    ],

];
