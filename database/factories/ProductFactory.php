<?php

namespace Database\Factories;

use App\Models\Brand;
use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => fake()->unique()->sentence(4),
            'category_id' => Category::factory(),
            'price' => fake()->randomFloat(2, 100, 1000),
            'description' => fake()->paragraph(),
            'brand_id' => Brand::factory(),
            'metadata' => []
        ];
    }
}
