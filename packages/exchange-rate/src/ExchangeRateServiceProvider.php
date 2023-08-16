<?php

namespace AlhajiAki\ExchangeRate;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class ExchangeRateServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->registerRoutes();

        $this->offerPublishing();
    }

    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../config/exchange-rate.php',
            'exchange-rate'
        );

        $this->app->singleton(ExchangeRateService::class, ExchangeRateService::class);
    }

    /**
     * Register the Exchange Rate route.
     *
     * @return void
     */
    protected function registerRoutes()
    {
        Route::group([
            'prefix' => config('exchange-rate.prefix'),
            'middleware' => config('exchange-rate.middleware', 'web'),
        ], function () {
            $this->loadRoutesFrom(__DIR__ . '/../routes/web.php');
        });
    }

    /**
     * Setup the resource publishing groups for Exchange Rate.
     *
     * @return void
     */
    protected function offerPublishing()
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/exchange-rate.php' => config_path('exchange-rate.php'),
            ], 'exchange-rate-config');
        }
    }
}
