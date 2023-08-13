<?php

namespace Tests\Unit\Middleware;

use App\Enums\UserTypeEnum;
use App\Http\Middleware\UserType;
use App\Models\User;
use Closure;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Tests\TestCase;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;

uses(
    TestCase::class,
    LazilyRefreshDatabase::class,
);

it('allows access for admin user', function () {
    $user = User::factory()->isAdmin()->create();
    $request = new Request();
    $request->setUserResolver(fn () => $user);

    $middleware = new UserType();
    $response = $middleware->handle($request, Closure::fromCallable(fn () => new Response()), UserTypeEnum::Admin->value);

    expect($response)->toBeInstanceOf(Response::class);
});

it('allows access for regular user', function () {
    $user = User::factory()->create();
    $request = new Request();
    $request->setUserResolver(fn () => $user);

    $middleware = new UserType();

    $response = $middleware->handle($request, Closure::fromCallable(fn () => new Response()), UserTypeEnum::User->value);

    expect($response)->toBeInstanceOf(Response::class);
});

it('throws AuthenticationException if user is not logged in', function () {
    $request = new Request();

    $middleware = new UserType();
    $middleware->handle($request, Closure::fromCallable(fn () => new Response()), UserTypeEnum::User->value);
})->throws(AuthenticationException::class);

it('throws AuthenticationException for invalid user type', function () {
    $user = User::factory()->isAdmin()->create();
    $request = new Request();
    $request->setUserResolver(fn () => $user);

    $middleware = new UserType();
    $middleware->handle($request, Closure::fromCallable(fn () => new Response()), 'InvalidUserType');
})->throws(AuthenticationException::class);

it('throws AuthenticationException for unauthorized admin access', function () {
    $user = User::factory()->create();
    $request = new Request();
    $request->setUserResolver(fn () => $user);

    $middleware = new UserType();

    $middleware->handle($request, Closure::fromCallable(fn () => new Response()), UserTypeEnum::Admin->value);
})->throws(AuthenticationException::class);

it('throws AuthenticationException for unauthorized regular user access', function () {
    $user = User::factory()->isAdmin()->create();
    $request = new Request();
    $request->setUserResolver(fn () => $user);

    $middleware = new UserType();

    $middleware->handle($request, Closure::fromCallable(fn () => new Response()), UserTypeEnum::User->value);
})->throws(AuthenticationException::class);
