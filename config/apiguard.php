<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Key name
    |--------------------------------------------------------------------------
    |
    | This is the name of the variable that will provide us the API key in the
    | header
    |
    */
    'header_key' => 'X-Authorization',

    /*
    |--------------------------------------------------------------------------
    | Authentication Provider
    |--------------------------------------------------------------------------
    |
    | Specify the provider that is used to authenticate users. Example:
    |
    | Chrisbjr\ApiGuard\Auth\Sentinel:class
    |
    | You can set up your own authentication provider here by creating a class
    | that implements the ApiGuardAuthContract interface.
    |
    */
    'auth'       => null,

    'models' => [

        'api_key' => 'Chrisbjr\ApiGuard\Models\ApiKey',

    ],

];
