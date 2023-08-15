<?php

namespace Tests\Feature\Http\Order;

use App\Actions\Order\CreateOrderAction;
use App\Http\Requests\Order\StoreOrderRequest;
use App\Http\Resources\OrderResource;
use App\Models\OrderStatus;
use App\Models\Payment;
use App\Models\Product;
use App\Models\User;
use App\Services\Response\ApiResponse;
use Database\Seeders\OrderStatusSeeder;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Event;
use Illuminate\Testing\Fluent\AssertableJson;
use Mockery\MockInterface;

beforeEach(function () {
    $this->seed(OrderStatusSeeder::class);
});

test('unauthenticated users cannot create orders', function () {
    $this->postJson(route('v1:orders:store'))
        ->assertStatus(JsonResponse::HTTP_UNAUTHORIZED);
});

test('authenticated users can create an order successfully', function () {
    $user = User::factory()->create();
    $orderStatus = OrderStatus::query()->first();
    $payment = Payment::factory()->for($user)->create();
    $product = Product::factory()->create();

    $data = [
        'order_status_uuid' => $orderStatus->uuid,
        'payment_uuid' => $payment->uuid,
        'products' => [
            ['uuid' => $product->uuid, 'quantity' => 2],
        ],
        'address' => [
            'billing' => fake()->address(),
            'shipping' => fake()->address(),
        ],
    ];

    $this->actingAs($user)
        ->postJson(route('v1:orders:store'), $data)
        ->assertStatus(JsonResponse::HTTP_OK)
        ->assertJson(
            fn (AssertableJson $json) =>
            $json->hasAll(['success', 'error', 'errors', 'extra', 'data', 'data.uuid'])
        );

    $this->assertDatabaseHas('orders', [
        'user_id' => $user->id,
    ]);
});

test('user has to submit an existing order status', function () {
    $user = User::factory()->create();
    $payment = Payment::factory()->for($user)->create();
    $product = Product::factory()->create();

    $data = [
        'order_status_uuid' => 'invalid-uuid',
        'payment_uuid' => $payment->uuid,
        'products' => [
            ['uuid' => $product->uuid, 'quantity' => 2],
        ],
        'address' => [
            'billing' => fake()->address(),
            'shipping' => fake()->address(),
        ],
    ];

    $this->actingAs($user)
        ->postJson(route('v1:orders:store'), $data)
        ->assertStatus(JsonResponse::HTTP_UNPROCESSABLE_ENTITY);

    $this->assertDatabaseMissing('orders', [
        'user_id' => $user->id,
    ]);
});

test('user cannot submit an invalid payment uuid', function () {
    $user = User::factory()->create();
    $orderStatus = OrderStatus::query()->first();
    $product = Product::factory()->create();

    $data = [
        'order_status_uuid' => $orderStatus->uuid,
        'payment_uuid' => 'invalid-uuid',
        'products' => [
            ['uuid' => $product->uuid, 'quantity' => 2],
        ],
        'address' => [
            'billing' => fake()->address(),
            'shipping' => fake()->address(),
        ],
    ];

    $this->actingAs($user)
        ->postJson(route('v1:orders:store'), $data)
        ->assertStatus(JsonResponse::HTTP_UNPROCESSABLE_ENTITY);

    $this->assertDatabaseMissing('orders', [
        'user_id' => $user->id,
    ]);
});

test('user has to submit a payment that belongs to them', function () {
    $user = User::factory()->create();
    $orderStatus = OrderStatus::query()->first();
    $payment = Payment::factory()->create();
    $product = Product::factory()->create();

    $data = [
        'order_status_uuid' => $orderStatus->uuid,
        'payment_uuid' => $payment->uuid,
        'products' => [
            ['uuid' => $product->uuid, 'quantity' => 2],
        ],
        'address' => [
            'billing' => fake()->address(),
            'shipping' => fake()->address(),
        ],
    ];

    $this->actingAs($user)
        ->postJson(route('v1:orders:store'), $data)
        ->assertStatus(JsonResponse::HTTP_UNPROCESSABLE_ENTITY);

    $this->assertDatabaseMissing('orders', [
        'user_id' => $user->id,
    ]);
});

test('the products list should not contain duplicate uuids', function () {
    $user = User::factory()->create();
    $orderStatus = OrderStatus::query()->first();
    $payment = Payment::factory()->for($user)->create();
    $product = Product::factory()->create();

    $data = [
        'order_status_uuid' => $orderStatus->uuid,
        'payment_uuid' => $payment->uuid,
        'products' => [
            ['uuid' => $product->uuid, 'quantity' => 2],
            ['uuid' => $product->uuid, 'quantity' => 3],
        ],
        'address' => [
            'billing' => fake()->address(),
            'shipping' => fake()->address(),
        ],
    ];

    $this->actingAs($user)
        ->postJson(route('v1:orders:store'), $data)
        ->assertStatus(JsonResponse::HTTP_UNPROCESSABLE_ENTITY);

    $this->assertDatabaseMissing('orders', [
        'user_id' => $user->id,
    ]);
});

test('the products list should contain products that exist', function () {
    $user = User::factory()->create();
    $orderStatus = OrderStatus::query()->first();
    $payment = Payment::factory()->for($user)->create();

    $data = [
        'order_status_uuid' => $orderStatus->uuid,
        'payment_uuid' => $payment->uuid,
        'products' => [
            ['uuid' => 'invalid-product', 'quantity' => 2],
        ],
        'address' => [
            'billing' => fake()->address(),
            'shipping' => fake()->address(),
        ],
    ];

    $this->actingAs($user)
        ->postJson(route('v1:orders:store'), $data)
        ->assertStatus(JsonResponse::HTTP_UNPROCESSABLE_ENTITY);

    $this->assertDatabaseMissing('orders', [
        'user_id' => $user->id,
    ]);
});
