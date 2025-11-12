<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Role;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $adminRole = Role::where('name', 'admin')->first();
        $csRole = Role::where('name', 'customer_service')->first();

        // Admin user
        User::updateOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Admin',
                'password' => Hash::make('admin123'),
                'role_id' => $adminRole->id,
                'counter_id' => null,
            ]
        );

        // Customer Service user
        User::updateOrCreate(
            ['email' => 'cs@example.com'],
            [
                'name' => 'Customer Service',
                'password' => Hash::make('cs123456'),
                'role_id' => $csRole->id,
                'counter_id' => null,
            ]
        );
    }
}
