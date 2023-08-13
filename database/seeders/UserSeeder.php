<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (User::query()->exists()) {
            return;
        }

        User::factory()->create([
            'email' => 'someone@somewhere.com',
        ]);

        User::factory()->isAdmin()->create([
            'email' => 'admin@petshop.com'
        ]);
    }
}
