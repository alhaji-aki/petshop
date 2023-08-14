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

test('unauthenticated users cannot view a payment', function () {
    $payment = Payment::factory()->create();

    $this->getJson(route('v1:payments:show', $payment))
        ->assertStatus(JsonResponse::HTTP_UNAUTHORIZED);
});

test('users who are not admins cannot view payment that does not belong to them', function () {
    $user = User::factory()->create();

    $payment = Payment::factory()->create();

    $this->actingAs($user)
        ->getJson(route('v1:payments:show', $payment))
        ->assertStatus(JsonResponse::HTTP_NOT_FOUND);
});

test('users can view a their payments', function () {
    $user = User::factory()->create();

    $payment = Payment::factory()->for($user)->create();

    $this->actingAs($user)
        ->getJson(route('v1:payments:show', $payment))
        ->assertStatus(JsonResponse::HTTP_OK)
        ->assertJson(
            fn (AssertableJson $json) =>
            $json->hasAll(['success', 'error', 'errors', 'data', 'extra'])
                ->has('data', fn (AssertableJson $json) => $json->where('uuid', $payment->uuid)->etc())
        );
});

test('users who are admins can view payments that does not belong to them', function () {
    $user = User::factory()->isAdmin()->create();

    $payment = Payment::factory()->create();

    $this->actingAs($user)
        ->getJson(route('v1:payments:show', $payment))
        ->assertStatus(JsonResponse::HTTP_OK)
        ->assertJson(
            fn (AssertableJson $json) =>
            $json->hasAll(['success', 'error', 'errors', 'data', 'extra'])
                ->has('data', fn (AssertableJson $json) => $json->where('uuid', $payment->uuid)->etc())
        );
});
