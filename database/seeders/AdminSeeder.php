<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        // Tạo tài khoản admin
        User::create([
            'name' => 'Admin',
            'email' => 'admin@unishop.com',
            'password' => Hash::make('admin123'),
            'role' => 'admin'
        ]);

        // Tạo tài khoản user thường
        User::create([
            'name' => 'User Test',
            'email' => 'user@unishop.com',
            'password' => Hash::make('user123'),
            'role' => 'user'
        ]);
    }
}
