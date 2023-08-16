<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Exchange Rate Path
    |--------------------------------------------------------------------------
    |
    | This is the URI path where Exchange Rate will be accessible from. Feel free
    | to change this path to anything you like.
    |
    */

    'prefix' => env('EXCHANGE_RATE_PREFIX', 'api/v1'),

    /*
    |--------------------------------------------------------------------------
    | Exchange Rate Route Middleware
    |--------------------------------------------------------------------------
    |
    | These middleware will get attached onto each Exchange Rate route, giving you
    | the chance to add your own middleware to this list or change any of
    | the existing middleware. Or, you can simply stick with this list.
    |
    */

    'middleware' => ['api'],
];
