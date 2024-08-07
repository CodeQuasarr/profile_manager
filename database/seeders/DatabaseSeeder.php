<?php

namespace Database\Seeders;

use App\Models\Users\Administrator;
use Illuminate\Database\Seeder;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Administrator::factory(10)->create();

        Administrator::factory()->create([
            'name' => 'Test Administrator',
            'email' => 'test@example.com',
        ]);
    }
}
