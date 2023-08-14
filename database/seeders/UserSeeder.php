<?php

namespace Database\Seeders;

use App\Models\User;
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

        User::factory()->avatar()->create([
            'email' => 'someone@somewhere.com',
        ]);

        User::factory()->avatar()->isAdmin()->create([
            'email' => 'admin@petshop.com'
        ]);
    }
}
