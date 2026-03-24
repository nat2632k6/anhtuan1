$admin = \App\Models\User::where('email', 'admin@unishop.com')->first();
if ($admin) {
    echo "✅ Admin đã tồn tại!\n";
    echo "ID: " . $admin->id . "\n";
    echo "Email: " . $admin->email . "\n";
    echo "Role: " . $admin->role . "\n";
    echo "Test isAdmin(): " . ($admin->isAdmin() ? 'TRUE' : 'FALSE') . "\n";
} else {
    $admin = \App\Models\User::create([
        'name' => 'Admin',
        'email' => 'admin@unishop.com',
        'password' => \Illuminate\Support\Facades\Hash::make('admin123'),
        'role' => 'admin'
    ]);
    echo "✅ Tạo admin mới thành công!\n";
    echo "Email: admin@unishop.com\n";
    echo "Password: admin123\n";
}
