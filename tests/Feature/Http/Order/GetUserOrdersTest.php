<?php

namespace Tests\Feature\Http\Order;

use App\Enums\OrderTypeEnum;
use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Foundation\Testing\WithFaker;
use App\Models\User;
use Illuminate\Testing\Fluent\AssertableJson;

uses(
    WithFaker::class,
);

test('unauthenticated users cannot get their orders list', function () {
    $this->getJson(route('v1:user:orders:index'))
        ->assertStatus(JsonResponse::HTTP_UNAUTHORIZED);
});

test('users can get their orders list', function () {
    $user = User::factory()->create();

    Order::factory()->create();

    Order::factory()->for($user)->create([
        'created_at' => now()->subHours(2),
    ]);

    Order::factory()->for($user)->create([
        'created_at' => now()->subHours(1),
    ]);

    $response = $this->actingAs($user)
        ->getJson(route('v1:user:orders:index'))
        ->assertStatus(JsonResponse::HTTP_OK)
        ->assertJson(fn (AssertableJson $json) => $json->has('data', 2)->etc());

    $orders = $response->json('data');

    // Ensure the orders are in the right order (latest first)
    $this->assertTrue(
        $orders[0]['created_at'] > $orders[1]['created_at']
    );
});

test('users orders list will not contain orders of other users', function () {
    $user = User::factory()->create();

    $anotherUsersOrder = Order::factory()->create();

    $usersOrder = Order::factory()->for($user)->create([
        'created_at' => now()->subHours(2),
    ]);

    $response = $this->actingAs($user)
        ->getJson(route('v1:user:orders:index'))
        ->assertStatus(JsonResponse::HTTP_OK)
        ->assertJson(fn (AssertableJson $json) => $json->has('data', 1)->etc());

    $orders = $response->json('data');

    $this->assertTrue($orders[0]['uuid'] === $usersOrder->uuid);
    $this->assertTrue($orders[0]['uuid'] !== $anotherUsersOrder->uuid);
});


test('users can limit the number of records that are returned per page', function () {
    $user = User::factory()->isAdmin()->create();

    Order::factory()->for($user)->count(2)->create();

    $this->actingAs($user)
        ->getJson(route('v1:user:orders:index', ['limit' => 1]))
        ->assertStatus(JsonResponse::HTTP_OK)
        ->assertJson(fn (AssertableJson $json) => $json->has('data', 1)->etc());
});

test('returns a bad request response if sort by is invalid', function () {
    $user = User::factory()->isAdmin()->create();

    $this->actingAs($user)
        ->getJson(route('v1:user:orders:index', ['sortBy' => 'invalid']))
        ->assertStatus(JsonResponse::HTTP_BAD_REQUEST);
});

test('users can change the sort direction', function () {
    $user = User::factory()->isAdmin()->create();

    Order::factory()->for($user)->create([
        'created_at' => now()->subHours(2),
    ]);

    Order::factory()->for($user)->create([
        'created_at' => now()->subHours(1),
    ]);

    $response = $this->actingAs($user)
        ->getJson(route('v1:user:orders:index', ['desc' => false]))
        ->assertStatus(JsonResponse::HTTP_OK)
        ->assertJson(fn (AssertableJson $json) => $json->has('data', 2)->etc());

    $orders = $response->json('data');

    $this->assertTrue(
        $orders[0]['created_at'] < $orders[1]['created_at']
    );
});
