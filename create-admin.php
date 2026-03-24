<?php

use Illuminate\Support\Facades\Hash;
use App\Models\User;

// Xóa user admin cũ nếu có
User::where('email', 'admin@unishop.com')->delete();

// Tạo admin mới
$admin = User::create([
    'name' => 'Admin',
    'email' => 'admin@unishop.com',
    'password' => Hash::make('admin123'),
    'role' => 'admin'
]);

echo "✅ Tạo tài khoản admin thành công!\n";
echo "Email: admin@unishop.com\n";
echo "Password: admin123\n";
echo "Role: " . $admin->role . "\n";
