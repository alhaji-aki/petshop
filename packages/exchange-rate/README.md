# About Exchange Rate

This package exposes an api endpoint that receives an amount and an currency and converts the amount to the exchange rate amount of the currency given.

***Note: The default currency for this package is Euros.***

## Installation & Configuration

This package is in development mode and is already installed when you installed this project.

After the installation has completed, the package will automatically register itself.
Run the following to publish the migration, config and lang file

This package has a configuration file that has also already been published. You can see it in your config folder as `exchange-rate.php`. In this file you can change the route prefix and middleware for the endpoint this package publishes.

This is how it looks like by default:

```php
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

    'prefix' => env('EXCHANGE_RATE_PREFIX', ''),

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

    'middleware' => ['web'],
];

```

## Routes

This package exposes an endpoint. Checkout out its swagger documentation here `TODO: add swagger link here` for more details

## Testing

```bash
composer test
```
