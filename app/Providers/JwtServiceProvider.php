<?php

namespace App\Providers;

use App\Services\Jwt\JwtGuard;
use Illuminate\Auth\RequestGuard;
use Illuminate\Contracts\Auth\Factory;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\ServiceProvider;

class JwtServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        Auth::resolved(function ($auth) {
            $auth->extend('jwt', function ($app, $name, array $config) use ($auth) {
                return tap($this->createGuard($auth, $config), function ($guard) {
                    app()->refresh('request', $guard, 'setRequest');
                });
            });
        });
    }

    /**
     * @param \Illuminate\Contracts\Auth\Factory $auth
     */
    private function createGuard($auth, array $config): RequestGuard
    {
        return new RequestGuard(
            new JwtGuard($auth, $config['provider']),
            request(),
            $auth->createUserProvider($config['provider'] ?? null)
        );
    }
}
