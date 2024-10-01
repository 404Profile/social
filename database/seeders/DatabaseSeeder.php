<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::factory()->create([
            'name' => 'Admin',
            'surname' => 'Admin',
            'email' => 'admin@admin.com',
            'age' => 20,
            'gender' => 'M',
        ]);

        User::factory()->create([
            'name' => 'a',
            'surname' => 'a',
            'email' => 'a@a.com',
            'age' => 20,
            'gender' => 'M',
        ]);
    }
}
