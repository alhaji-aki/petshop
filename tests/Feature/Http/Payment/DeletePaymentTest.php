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

beforeEach(function () {
    $this->user = User::factory()->create();
});

test('unauthenticated users cannot delete payments', function () {
    $payment = Payment::factory()->create();

    $this->deleteJson(route('v1:payments:delete', $payment))
        ->assertStatus(JsonResponse::HTTP_UNAUTHORIZED);
});

test('users cannot delete payment that does not belong to them', function () {
    $payment = Payment::factory()->create();

    $this->actingAs($this->user)
        ->deleteJson(route('v1:payments:delete', $payment))
        ->assertStatus(JsonResponse::HTTP_NOT_FOUND);

    $this->assertModelExists($payment);
});

test('users can delete their payments', function () {
    $payment = Payment::factory()->for($this->user)->create();

    $this->actingAs($this->user)
        ->deleteJson(route('v1:payments:delete', $payment))
        ->assertStatus(JsonResponse::HTTP_OK)
        ->assertJson(
            fn (AssertableJson $json) =>
            $json->hasAll(['success', 'error', 'errors', 'extra', 'data', 'data.uuid'])
        );

    $this->assertModelMissing($payment);
});
