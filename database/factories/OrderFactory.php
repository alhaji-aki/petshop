<?php

namespace Database\Factories;

use App\Models\OrderStatus;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Order>
 */
class OrderFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'order_status_id' => function (array $attributes) {
                return OrderStatus::query()->firstWhere('title', 'open')?->id;
            },
            'payment_id' => function (array $attributes) {
                return Payment::factory()->for(User::find($attributes['user_id']));
            },
            'address' => [
                'billing' => fake()->address(),
                'shipping' => fake()->address(),
            ],
            'amount' => $amount = fake()->randomFloat(2, 100, 1000),
            'delivery_fee' => $amount > 500 ? null : 15,
            'shipped_at' => null
        ];
    }
}
