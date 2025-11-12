<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Jalankan RoleSeeder lebih dulu
        $this->call([
            RoleSeeder::class,
            UserSeeder::class,
        ]);
    }
}
