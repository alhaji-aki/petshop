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
     */
    protected function registerRoutes(): void
    {
        Route::group([
            'prefix' => config('exchange-rate.prefix'),
            'middleware' => config('exchange-rate.middleware', 'web'),
        ], function (): void {
            $this->loadRoutesFrom(__DIR__ . '/../routes/web.php');
        });
    }

    /**
     * Setup the resource publishing groups for Exchange Rate.
     */
    protected function offerPublishing(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/exchange-rate.php' => config_path('exchange-rate.php'),
            ], 'exchange-rate-config');

            $this->publishes([
                __DIR__ . '/../docs/swagger.json' => storage_path('api-docs/exchange-rate.json'),
            ], 'exchange-rate-docs');

            $this->publishes([
                __DIR__ . '/../docs/swagger.yaml' => storage_path('api-docs/exchange-rate.yaml'),
            ], 'exchange-rate-docs');
        }
    }
}
