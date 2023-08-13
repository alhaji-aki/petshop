<?php

namespace Tests\Unit\Actions\JwtToken;

use App\Actions\JwtToken\CreateJwtTokenAction;
use App\Models\User;
use App\Services\Jwt\JwtService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, LazilyRefreshDatabase::class);

it('creates a new JWT token and returns the token string', function () {
    $user = User::factory()->create();
    $jwtService = app(JwtService::class);
    $action = new CreateJwtTokenAction($jwtService);

    $tokenTitle = 'Test Token';
    $restrictions = ['restriction1', 'restriction2'];
    $permissions = ['permission1', 'permission2'];

    Carbon::setTestNow(Carbon::now());
    $expectedExpiry = Carbon::now()->addMinutes(config('jwt.expiration'));

    $tokenString = $action->execute($user, $tokenTitle, $restrictions, $permissions);

    $this->assertDatabaseHas('jwt_tokens', [
        'user_id' => $user->id,
        'token_title' => $tokenTitle,
        'restrictions' => json_encode($restrictions),
        'permissions' => json_encode($permissions),
        'expires_at' => $expectedExpiry,
        'last_used_at' => null,
        'refreshed_at' => null,
    ]);

    // Validate that the token string can be validated by JwtService
    $validatedToken = $jwtService->validate($tokenString);
    $this->assertNotNull($validatedToken);
    $this->assertEquals($user->uuid, $validatedToken->claims()->get('uid'));
});

it('returns the token string with default empty restrictions and permissions', function () {
    $user = User::factory()->create();
    $jwtService = app(JwtService::class);
    $action = new CreateJwtTokenAction($jwtService);

    $tokenTitle = 'Test Token';

    $tokenString = $action->execute($user, $tokenTitle);

    $this->assertDatabaseHas('jwt_tokens', [
        'user_id' => $user->id,
        'token_title' => $tokenTitle,
        'restrictions' => '[]',
        'permissions' => '[]',
    ]);

    // Validate that the token string can be validated by JwtService
    $validatedToken = $jwtService->validate($tokenString);
    $this->assertNotNull($validatedToken);
    $this->assertEquals($user->uuid, $validatedToken->claims()->get('uid'));
});
