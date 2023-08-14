<?php

namespace Database\Factories;

use App\Enums\PaymentTypeEnum;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Payment>
 */
class PaymentFactory extends Factory
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
            'type' => fake()->randomElement(PaymentTypeEnum::cases()),
            'details' => function (array $attributes) {
                return match ($attributes['type']) {
                    PaymentTypeEnum::CashOnDelivery => [
                        "first_name" => fake()->firstName(),
                        "last_name" => fake()->lastName(),
                        "address" => fake()->address(),
                    ],
                    PaymentTypeEnum::BankTransfer => [
                        "swift" => fake()->swiftBicNumber(),
                        "iban" => fake()->iban(),
                        "name" => fake()->name(),
                    ],
                    PaymentTypeEnum::CreditCard => [
                        "holder_name" => fake()->name(),
                        "number" => fake()->creditCardNumber(),
                        "ccv" => fake()->numerify('###'),
                        "expire_date" => fake()->creditCardExpirationDate()
                    ],
                };
            }
        ];
    }
}
