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

test('unauthenticated users cannot view an order', function () {
    $order = Order::factory()->create();

    $this->getJson(route('v1:orders:show', $order))
        ->assertStatus(JsonResponse::HTTP_UNAUTHORIZED);
});

test('users who are not admins cannot view order that does not belong to them', function () {
    $user = User::factory()->create();

    $order = Order::factory()->create();

    $this->actingAs($user)
        ->getJson(route('v1:orders:show', $order))
        ->assertStatus(JsonResponse::HTTP_NOT_FOUND);
});

test('users can view their orders', function () {
    $user = User::factory()->create();

    $order = Order::factory()->for($user)->create();

    $this->actingAs($user)
        ->getJson(route('v1:orders:show', $order))
        ->assertStatus(JsonResponse::HTTP_OK)
        ->assertJson(
            fn (AssertableJson $json) =>
            $json->hasAll(['success', 'error', 'errors', 'data', 'extra'])
                ->has('data', fn (AssertableJson $json) => $json->where('uuid', $order->uuid)->etc())
        );
});

test('users who are admins can view orders that does not belong to them', function () {
    $user = User::factory()->isAdmin()->create();

    $order = Order::factory()->create();

    $this->actingAs($user)
        ->getJson(route('v1:orders:show', $order))
        ->assertStatus(JsonResponse::HTTP_OK)
        ->assertJson(
            fn (AssertableJson $json) =>
            $json->hasAll(['success', 'error', 'errors', 'data', 'extra'])
                ->has('data', fn (AssertableJson $json) => $json->where('uuid', $order->uuid)->etc())
        );
});
