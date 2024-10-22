<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = [
            [
                'id' => 1,
                'title' => 'Администратор',
            ],
            [
                'id' => 2,
                'title' => 'Пользователь',
            ],
        ];

        Role::insert($roles);
    }
}
