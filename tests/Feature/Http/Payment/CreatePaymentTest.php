<?php

namespace Tests\Feature\Http\Payment;

use App\Enums\PaymentTypeEnum;
use Illuminate\Http\JsonResponse;
use Illuminate\Foundation\Testing\WithFaker;
use App\Models\User;
use Illuminate\Testing\Fluent\AssertableJson;

uses(
    WithFaker::class,
);

beforeEach(function () {
    $this->user = User::factory()->create();
});

test('unauthenticated users cannot create payments', function () {
    $data = [
        'type' => PaymentTypeEnum::CashOnDelivery,
        'details' => [
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'address' => fake()->address(),
        ],
    ];

    $this->postJson(route('v1:payments:store'), $data)
        ->assertStatus(JsonResponse::HTTP_UNAUTHORIZED);
});

test('it can store cash on delivery payment', function () {
    $data = [
        'type' => PaymentTypeEnum::CashOnDelivery,
        'details' => [
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'address' => fake()->address(),
        ],
    ];

    $this->actingAs($this->user)
        ->postJson(route('v1:payments:store'), $data)
        ->assertStatus(JsonResponse::HTTP_OK)
        ->assertJson(
            fn (AssertableJson $json) =>
            $json->hasAll(['success', 'error', 'errors', 'extra', 'data', 'data.uuid'])
        );

    $this->assertDatabaseHas('payments', [
        'user_id' => $this->user->id,
        'details->first_name' => $data['details']['first_name'],
        'details->last_name' => $data['details']['last_name'],
        'details->address' => $data['details']['address'],
    ]);
});

test('it can store bank transfer payment', function () {
    $data = [
        'type' => PaymentTypeEnum::BankTransfer,
        'details' => [
            'swift' => fake()->swiftBicNumber(),
            'iban' => fake()->iban(),
            'name' => fake()->name(),
        ],
    ];

    $this->actingAs($this->user)
        ->postJson(route('v1:payments:store'), $data)
        ->assertStatus(JsonResponse::HTTP_OK)
        ->assertJson(
            fn (AssertableJson $json) =>
            $json->hasAll(['success', 'error', 'errors', 'extra', 'data', 'data.uuid'])
        );

    $this->assertDatabaseHas('payments', [
        'user_id' => $this->user->id,
        'details->swift' => $data['details']['swift'],
        'details->iban' => $data['details']['iban'],
        'details->name' => $data['details']['name'],
    ]);
});

test('it can store credit card payment', function () {
    $data = [
        'type' => PaymentTypeEnum::CreditCard,
        'details' => [
            'holder_name' => fake()->name(),
            'number' => fake()->creditCardNumber(),
            'ccv' => fake()->numberBetween(100, 999),
            'expire_date' => now()->addMonth()->format('m/y'),
        ],
    ];

    $this->actingAs($this->user)
        ->postJson(route('v1:payments:store'), $data)
        ->assertStatus(JsonResponse::HTTP_OK)
        ->assertJson(
            fn (AssertableJson $json) =>
            $json->hasAll(['success', 'error', 'errors', 'extra', 'data', 'data.uuid'])
        );

    $this->assertDatabaseHas('payments', [
        'user_id' => $this->user->id,
        'details->holder_name' => $data['details']['holder_name'],
        'details->number' => $data['details']['number'],
        'details->ccv' => $data['details']['ccv'],
        'details->expire_date' => $data['details']['expire_date'],
    ]);
});

test('it fails to store with invalid data', function () {
    $data = [
        'type' => PaymentTypeEnum::CreditCard, // Invalid type for the provided details
        'details' => [
            'holder_name' => fake()->name(),
            'number' => fake()->creditCardNumber(),
            'ccv' => fake()->numberBetween(100, 999),
            'expire_date' => fake()->date('m/y', 'past'), // Invalid expiration date
        ],
    ];

    $this->actingAs($this->user)
        ->postJson(route('v1:payments:store'), $data)
        ->assertStatus(JsonResponse::HTTP_UNPROCESSABLE_ENTITY);
});
