<?php

return array(

    /*
    |--------------------------------------------------------------------------
    | API request logging
    |--------------------------------------------------------------------------
    |
    | This switch will enable or disable logging of the requests made. This
    | feature needs to be enabled for API request limiting to work
    |
    */

    'logging' => true,

    /*
    |--------------------------------------------------------------------------
    | Key name
    |--------------------------------------------------------------------------
    |
    | This is the name of the variable that will provide us the API key in the
    | header
    |
    */

    'keyName' => 'X-API-KEY',

    /*
    |--------------------------------------------------------------------------
    | ApiGuard key generator route
    |--------------------------------------------------------------------------
    |
    | You can set this to false once you're done generating an initial API key
    |
    */

    'generateApiRoute' => false

);