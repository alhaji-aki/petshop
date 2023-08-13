<?php

namespace Tests\Unit\Actions\Authentication;

use App\Actions\Authentication\LoginAction;
use App\Actions\JwtToken\CreateJwtTokenAction;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\Request;
use Tests\TestCase;

uses(
    TestCase::class,
    LazilyRefreshDatabase::class,
    WithFaker::class
);

test('user can log in and receive a JWT token', function () {
    $user = User::factory()->create();

    $createJwtTokenAction = mock(CreateJwtTokenAction::class);
    $createJwtTokenAction->shouldReceive('execute')->once()->andReturn('fake_token');

    $action = new LoginAction($createJwtTokenAction);

    $request = new Request([
        'email' => $user->email,
        'password' => 'password',
    ]);

    $token = $action->execute($request, false);

    expect($token)->toBe('fake_token');
});

test('user login attempt is rate-limited', function () {
    $user = User::factory()->create();

    RateLimiter::shouldReceive('tooManyAttempts')->once()->andReturn(true);
    RateLimiter::shouldReceive('availableIn')->once()->andReturn(60);

    $action = new LoginAction(mock(CreateJwtTokenAction::class));

    $request = new Request([
        'email' => $user->email,
        'password' => 'password',
    ]);

    $this->expectException(ValidationException::class);
    $this->expectExceptionMessage(trans('auth.throttle', [
        'seconds' => 60,
        'minutes' => 1,
    ]));

    $action->execute($request, false);
});

test('user cannot log in with invalid credentials', function () {
    $user = User::factory()->create();

    $createJwtTokenAction = mock(CreateJwtTokenAction::class);
    $createJwtTokenAction->shouldReceive('execute')->never();

    $action = new LoginAction($createJwtTokenAction);

    $request = new Request([
        'email' => $user->email,
        'password' => 'wrongpassword',
    ]);

    $this->expectException(ValidationException::class);
    $this->expectExceptionMessage(trans('auth.failed'));

    $action->execute($request, false);
});
