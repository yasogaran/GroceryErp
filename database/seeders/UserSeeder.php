<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create initial admin user
        $admin = User::create([
            'name' => 'Administrator',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        // Create manager user
        User::create([
            'name' => 'Store Manager',
            'email' => 'manager@example.com',
            'password' => Hash::make('password'),
            'role' => 'manager',
            'is_active' => true,
            'email_verified_at' => now(),
            'created_by' => $admin->id,
        ]);

        // Create cashier user
        User::create([
            'name' => 'Cashier One',
            'email' => 'cashier@example.com',
            'password' => Hash::make('password'),
            'role' => 'cashier',
            'is_active' => true,
            'email_verified_at' => now(),
            'created_by' => $admin->id,
        ]);

        // Create store keeper user
        User::create([
            'name' => 'Store Keeper',
            'email' => 'keeper@example.com',
            'password' => Hash::make('password'),
            'role' => 'store_keeper',
            'is_active' => true,
            'email_verified_at' => now(),
            'created_by' => $admin->id,
        ]);

        // Create accountant user
        User::create([
            'name' => 'Accountant',
            'email' => 'accountant@example.com',
            'password' => Hash::make('password'),
            'role' => 'accountant',
            'is_active' => true,
            'email_verified_at' => now(),
            'created_by' => $admin->id,
        ]);
    }
}
