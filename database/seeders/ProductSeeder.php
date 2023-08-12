<?php

namespace Database\Seeders;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = Category::query()->get();

        $brands = Brand::query()->get();

        Product::factory()
            ->image()
            ->count(100)
            ->recycle($categories)
            ->recycle($brands)
            ->create();
    }
}
