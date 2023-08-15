<?php

use App\Actions\Order\CreateOrderAction;
use App\Models\Order;
use App\Models\OrderStatus;
use App\Models\Payment;
use App\Models\Product;
use App\Models\User;
use Database\Seeders\OrderStatusSeeder;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

uses(
    TestCase::class,
    LazilyRefreshDatabase::class,
    WithFaker::class
);

it('creates an order successfully', function () {
    $this->seed(OrderStatusSeeder::class);

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

    $action = new CreateOrderAction();

    $order = $action->execute($user, $data);

    expect($order)->toBeInstanceOf(Order::class);
    expect($order->user_id)->toBe($user->id);
    expect($order->order_status_id)->toBe($orderStatus->id);
    expect($order->payment_id)->toBe($payment->id);
    expect($order->address)->toEqual($data['address']);
    expect((float)$order->amount)->toBe($product->price * $data['products'][0]['quantity']);
    expect($order->shipped_at)->toBeNull();

    $this->assertDatabaseHas('order_products', [
        'order_id' => $order->id,
        'product_id' => $product->id,
    ]);
});

it('throws an exception if invalid order status is provided', function () {
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
            'address' => fake()->address(),
        ],
    ];

    $action = new CreateOrderAction();

    $action->execute($user, $data);
})->throws(RuntimeException::class, 'Failed to create order. Invalid order status submitted');

it('throws an exception if invalid payment is provided', function () {
    $this->seed(OrderStatusSeeder::class);

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
            'address' => fake()->address(),
        ],
    ];

    $action = new CreateOrderAction();

    $action->execute($user, $data);
})->throws(RuntimeException::class, 'Failed to create order. Invalid payment submitted');
