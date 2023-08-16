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

The package also generates a swagger documentation file which you can copy and add to your projects swagger documentation. Run the command below to publish swagger documentation. It will be located in `storage/api-docs`.

```bash
php artisan vendor:publish --provider "AlhajiAki\ExchangeRate\ExchangeRateServiceProvider" --tag="exchange-rate-docs"
```

You have to copy this documentation into your projects swagger documentation.

***Note if you will have to do this everytime you regenerate your project's swagger documentation***

## Routes

This package exposes a get endpoint for getting exchange rate amount.

## Testing

```bash
composer test
```
