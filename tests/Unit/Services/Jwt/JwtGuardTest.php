<?php

namespace Tests\Unit\Services\Jwt;

use App\Services\Jwt\JwtService;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;
use App\Services\Jwt\JwtGuard;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

uses(
    TestCase::class,
    LazilyRefreshDatabase::class
);

it('returns null when no bearer token is provided', function () {
    $jwtGuard = new JwtGuard(Auth::getFacadeRoot(), 'users');
    $request = new Request();

    $result = $jwtGuard($request);

    expect($result)->toBeNull();
});

it('returns null when bearer token cannot be validated', function () {
    $jwtService = app(JwtService::class);
    $jwtGuard = new JwtGuard(Auth::getFacadeRoot(), 'users');
    $request = new Request();
    $request->headers->add(['Authorization' => 'Bearer invalid_token']);

    $result = $jwtGuard($request);

    expect($result)->toBeNull();
});

it('returns null when JwtToken does not exist', function () {
    $jwtService = app(JwtService::class);
    $jwtGuard = new JwtGuard(Auth::getFacadeRoot(), 'users');
    $request = new Request();
    $request->headers->add(['Authorization' => 'Bearer valid_token']);

    $result = $jwtGuard($request);

    expect($result)->toBeNull();
});

it('returns null when JwtToken has expired', function () {
    $user = User::factory()->create();
    $jwtService = app(JwtService::class);
    $jwtGuard = new JwtGuard(Auth::getFacadeRoot(), 'users');



    // Generate an expired token for the user
    $this->travelTo(now()->subMinutes(config('jwt.expiration') + 1));
    $expiredToken = $jwtService->generate($user);
    $this->travelBack();

    // Set the Authorization header with the expired token
    $request = new Request();
    $request->headers->add(['Authorization' => 'Bearer ' . $expiredToken->toString()]);

    $result = $jwtGuard($request);

    expect($result)->toBeNull();
});

it('returns null when user UUID does not match', function () {
    $user = User::factory()->create();
    $jwtService = app(JwtService::class);
    $jwtGuard = new JwtGuard(Auth::getFacadeRoot(), 'users');

    // Generate a token for a different user
    $otherUser = User::factory()->create();
    $token = $jwtService->generate($otherUser);

    // Set the Authorization header with the token
    $request = new Request();
    $request->headers->add(['Authorization' => 'Bearer ' . $token->toString()]);

    $result = $jwtGuard($request);

    expect($result)->toBeNull();
});
