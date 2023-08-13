<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Secret Key
    |--------------------------------------------------------------------------
    |
    | This value controls the your jwt secret
    |
    */

    'secret' => env('JWT_SECRET'),

    /*
    |--------------------------------------------------------------------------
    | Expiration Minutes
    |--------------------------------------------------------------------------
    |
    | This value controls the number of minutes until an issued jwt token will be
    | considered expired. This will be used to calculate the token's
    | "expires_at" attribute. Default is 1 day
    |
    */

    'expiration' => env('JWT_EXPIRATION', 60 * 24),
];
