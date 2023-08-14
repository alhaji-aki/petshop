<?php

use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Testing\Fluent\AssertableJson;

test('authenticated user who is not an admin can retrieve their profile', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    $this->getJson(route('v1:user:show'))
        ->assertOk()
        ->assertJson(
            fn (AssertableJson $json) =>
            $json->where('success', 1)
                ->where('error', null)
                ->where('errors', [])
                ->where('extra', [])
                ->where('data.uuid', $user->uuid)
        );
});

test('unauthenticated user cannot retrieve profile', function () {
    $this->getJson(route('v1:user:show'))
        ->assertUnauthorized()
        ->assertJson([
            'success' => 0,
            'data' => [],
            'error' => 'Unauthenticated.',
            'errors' => [],
            'trace' => [],
        ]);
});

test('authenticated user who is an admin cannot retrieve profile', function () {
    $user = User::factory()->isAdmin()->create();

    $this->actingAs($user)
        ->getJson(route('v1:user:show'))
        ->assertUnauthorized()
        ->assertJson([
            'success' => 0,
            'data' => [],
            'error' => 'Unauthenticated.',
            'errors' => [],
            'trace' => [],
        ]);
});
