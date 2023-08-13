<?php

namespace Tests\Unit\Services\Jwt;

use App\Services\Jwt\JwtService;
use App\Models\User;
use Lcobucci\JWT\Token\Plain;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

uses(
    TestCase::class,
    LazilyRefreshDatabase::class
);

it('can generate a valid JWT token', function () {
    $user = User::factory()->create();
    $jwtService = app(JwtService::class);

    $token = $jwtService->generate($user);

    expect($token)->toBeInstanceOf(Plain::class);
});

it('can validate a valid JWT token', function () {
    $user = User::factory()->create();
    $jwtService = app(JwtService::class);

    $token = $jwtService->generate($user);
    $bearerToken = $token->toString();

    $validatedToken = $jwtService->validate($bearerToken);

    expect($validatedToken)->toBeInstanceOf(Plain::class);
    expect($validatedToken->claims()->get('uid'))->toBe($user->uuid);
});

it('returns null when validating an invalid JWT token', function () {
    $jwtService = app(JwtService::class);
    $invalidToken = 'invalid_token';

    $validatedToken = $jwtService->validate($invalidToken);

    expect($validatedToken)->toBeNull();
});

it('returns null when validating a token with unsupported structure', function () {
    $jwtService = app(JwtService::class);
    $invalidToken = 'invalid_structure_token';

    $validatedToken = $jwtService->validate($invalidToken);

    expect($validatedToken)->toBeNull();
});

it('returns null when validating a token with cannot decode content', function () {
    $jwtService = app(JwtService::class);
    $invalidToken = 'invalid_content_token';

    $validatedToken = $jwtService->validate($invalidToken);

    expect($validatedToken)->toBeNull();
});

it('returns null when validating a token with unsupported header', function () {
    $jwtService = app(JwtService::class);
    $invalidToken = 'unsupported_header_token';

    $validatedToken = $jwtService->validate($invalidToken);

    expect($validatedToken)->toBeNull();
});
