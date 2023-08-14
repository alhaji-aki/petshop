<?php

namespace Tests\Feature\Http\Payment;

use App\Enums\PaymentTypeEnum;
use App\Models\Payment;
use Illuminate\Http\JsonResponse;
use Illuminate\Foundation\Testing\WithFaker;
use App\Models\User;
use Illuminate\Testing\Fluent\AssertableJson;

uses(
    WithFaker::class,
);

test('unauthenticated users cannot get their payments list', function () {
    $this->getJson(route('v1:user:payments:index'))
        ->assertStatus(JsonResponse::HTTP_UNAUTHORIZED);
});

test('users can get their payments list', function () {
    $user = User::factory()->create();

    Payment::factory()->create();

    Payment::factory()->for($user)->create([
        'created_at' => now()->subHours(2),
    ]);

    Payment::factory()->for($user)->create([
        'created_at' => now()->subHours(1),
    ]);

    $response = $this->actingAs($user)
        ->getJson(route('v1:user:payments:index'))
        ->assertStatus(JsonResponse::HTTP_OK)
        ->assertJsonStructure([
            'data' => [
                '*' => [
                    'uuid', 'type', 'details', 'created_at', 'updated_at'
                ]
            ]
        ])
        ->assertJson(fn (AssertableJson $json) => $json->has('data', 2)->etc());

    $payments = $response->json('data');

    // Ensure the payments are in the right order (latest first)
    $this->assertTrue(
        $payments[0]['created_at'] > $payments[1]['created_at']
    );
});

test('users payments list will not contain payments of other users', function () {
    $user = User::factory()->create();

    $anotherUsersPayment = Payment::factory()->create();

    $usersPayment = Payment::factory()->for($user)->create([
        'created_at' => now()->subHours(2),
    ]);

    $response = $this->actingAs($user)
        ->getJson(route('v1:user:payments:index'))
        ->assertStatus(JsonResponse::HTTP_OK)
        ->assertJson(fn (AssertableJson $json) => $json->has('data', 1)->etc());

    $payments = $response->json('data');

    $this->assertTrue($payments[0]['uuid'] === $usersPayment->uuid);
    $this->assertTrue($payments[0]['uuid'] !== $anotherUsersPayment->uuid);
});


test('users can limit the number of records that are returned per page', function () {
    $user = User::factory()->isAdmin()->create();

    Payment::factory()->for($user)->count(2)->create();

    $this->actingAs($user)
        ->getJson(route('v1:user:payments:index', ['limit' => 1]))
        ->assertStatus(JsonResponse::HTTP_OK)
        ->assertJson(fn (AssertableJson $json) => $json->has('data', 1)->etc());
});

test('users can change the column to sort by', function () {
    $user = User::factory()->isAdmin()->create();

    $creditCard = Payment::factory()->for($user)->create([
        'type' => PaymentTypeEnum::CreditCard,
        'created_at' => now()->subHours(2),
    ]);

    $bankTransfer = Payment::factory()->for($user)->create([
        'type' => PaymentTypeEnum::BankTransfer,
        'created_at' => now()->subHours(1),
    ]);

    $cashOnDelivery = Payment::factory()->for($user)->create([
        'type' => PaymentTypeEnum::CashOnDelivery,
        'created_at' => now()->subHours(4),
    ]);

    $response = $this->actingAs($user)
        ->getJson(route('v1:user:payments:index', ['sortBy' => 'type']))
        ->assertStatus(JsonResponse::HTTP_OK)
        ->assertJson(fn (AssertableJson $json) => $json->has('data', 3)->etc());

    $payments = $response->json('data');

    $this->assertTrue($payments[0]['uuid'] == $creditCard->uuid);
    $this->assertTrue($payments[1]['uuid'] == $cashOnDelivery->uuid);
    $this->assertTrue($payments[2]['uuid'] == $bankTransfer->uuid);
});

test('returns a bad request response if sort by is invalid', function () {
    $user = User::factory()->isAdmin()->create();

    $this->actingAs($user)
        ->getJson(route('v1:user:payments:index', ['sortBy' => 'invalid']))
        ->assertStatus(JsonResponse::HTTP_BAD_REQUEST);
});

test('users can change the sort direction', function () {
    $user = User::factory()->isAdmin()->create();

    Payment::factory()->for($user)->create([
        'created_at' => now()->subHours(2),
    ]);

    Payment::factory()->for($user)->create([
        'created_at' => now()->subHours(1),
    ]);

    $response = $this->actingAs($user)
        ->getJson(route('v1:user:payments:index', ['desc' => false]))
        ->assertStatus(JsonResponse::HTTP_OK)
        ->assertJson(fn (AssertableJson $json) => $json->has('data', 2)->etc());

    $payments = $response->json('data');

    $this->assertTrue(
        $payments[0]['created_at'] < $payments[1]['created_at']
    );
});
