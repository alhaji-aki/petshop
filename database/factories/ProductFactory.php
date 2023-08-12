<?php

namespace Database\Factories;

use App\Actions\File\UploadFileAction;
use App\Models\Brand;
use App\Models\Category;
use App\Models\File;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Http\File as HttpFile;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File as FacadesFile;
use Illuminate\Support\Facades\Storage;

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

    /**
     * Save product image.
     */
    public function image(): static
    {
        return $this->afterCreating(function (Product $product) {
            app(UploadFileAction::class)->execute(
                $product,
                UploadedFile::fake()->image("{$product->uuid}.jpg", 500, 500)
            );
        });
    }
}
