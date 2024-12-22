<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class UserSeeder extends Seeder
{
    public function run()
    {
        // Admin User
        if (!User::where('email', 'admin@example.com')->exists()) {
            User::create([
                'name' => 'Admin',
                'email' => 'admin@example.com',
                'password' => Hash::make('password'),
                'role' => 'admin',
            ]);
        }

        // Cashier User
        if (!User::where('email', 'kasir1@example.com')->exists()) {
            User::create([
                'name' => 'Kasir 1',
                'email' => 'kasir1@example.com',
                'password' => Hash::make('password'),
                'role' => 'cashier',
            ]);
        }
    }
}
