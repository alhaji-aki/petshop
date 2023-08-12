<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'is_admin' => false,
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => null,
            'password' => 'password',
            'address' => fake()->address(),
            'phone_number' => fake()->unique()->e164PhoneNumber(),
            'is_marketing' => false,
            'remember_token' => Str::random(10),
            'last_login_at' => null,
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function verified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => now(),
        ]);
    }

    /**
     * Indicate that the model is an admin.
     */
    public function isAdmin(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_admin' => true,
        ]);
    }

    /**
     * Indicate that the model has enabled marketing preferences.
     */
    public function isMarketing(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_marketing' => true,
        ]);
    }

    /**
     * Save user avatar.
     */
    public function avatar(): static
    {
        return $this->afterCreating(function (User $user) {
            app(UploadFileAction::class)->execute(
                $user,
                UploadedFile::fake()->image("{$user->uuid}.jpg", 500, 500)
            );
        });
    }
}
