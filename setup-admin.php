$admin = \App\Models\User::where('email', 'admin@unishop.com')->first();

if (!$admin) {
    echo "❌ Không tìm thấy admin. Đang tạo mới...\n";
    $admin = \App\Models\User::create([
        'name' => 'Admin',
        'email' => 'admin@unishop.com',
        'password' => \Illuminate\Support\Facades\Hash::make('admin123'),
        'role' => 'admin'
    ]);
    echo "✅ Đã tạo admin mới!\n";
} else {
    echo "✅ Admin đã tồn tại!\n";
}

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "Email: admin@unishop.com\n";
echo "Password: admin123\n";
echo "Role: " . $admin->role . "\n";
