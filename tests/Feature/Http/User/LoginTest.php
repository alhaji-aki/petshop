<?php

namespace Tests\Feature\Http\User;

use App\Models\User;

test('user can log in and receive a JWT token', function () {
    $user = User::factory()->create([
        'password' => $password = 'testpassword',
    ]);

    $requestData = [
        'email' => $user->email,
        'password' => $password,
    ];

    $this->postJson(route('v1:user:login'), $requestData)
        ->assertSuccessful()
        ->assertJsonStructure(['success', 'data' => ['token'], 'error', 'errors', 'extra']);
});


test('request is validated', function () {
    $this->postJson(route('v1:user:login'))
        ->assertStatus(422)
        ->assertJsonStructure(['success', 'data', 'error', 'errors' => ['email', 'password'], 'trace']);
});

test('user cannot log in with invalid credentials', function () {
    $user = User::factory()->create();

    $requestData = [
        'email' => $user->email,
        'password' => 'wrongpassword',
    ];

    $this->postJson(route('v1:user:login'), $requestData)
        ->assertStatus(422)
        ->assertJsonStructure(['success', 'data', 'error', 'errors' => ['email'], 'trace']);
});

test('users who are admins cannot log in using this route', function () {
    $user = User::factory()->isAdmin()->create();

    $requestData = [
        'email' => $user->email,
        'password' => 'password',
    ];

    $this->postJson(route('v1:user:login'), $requestData)
        ->assertStatus(422)
        ->assertJsonStructure(['success', 'data', 'error', 'errors' => ['email'], 'trace']);
});

test('user is rate-limited after too many login attempts', function () {
    $user = User::factory()->create([
        'password' => $password = 'testpassword',
    ]);

    // Attempt to log in multiple times within a short interval
    for ($i = 0; $i < 5; $i++) {
        $requestData = [
            'email' => $user->email,
            'password' => 'wrongpassword',
        ];
        $this->postJson(route('v1:user:login'), $requestData);
    }

    // The next login attempt should be rate-limited
    $requestData = [
        'email' => $user->email,
        'password' => $password,
    ];

    $this->postJson(route('v1:user:login'), $requestData)
        ->assertStatus(429) // Too Many Requests
        ->assertJsonStructure(['success', 'data', 'error', 'errors' => ['email'], 'trace']);
});
