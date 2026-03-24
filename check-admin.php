$admin = \App\Models\User::where('email', 'admin@unishop.com')->first();
if ($admin) {
    echo "✅ ADMIN ĐÃ TỒN TẠI TRONG DATABASE\n";
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
    echo "ID: " . $admin->id . "\n";
    echo "Name: " . $admin->name . "\n";
    echo "Email: " . $admin->email . "\n";
    echo "Role: " . $admin->role . "\n";
    echo "Created: " . $admin->created_at . "\n";
    echo "\n";
    echo "Test isAdmin(): " . ($admin->isAdmin() ? '✅ TRUE' : '❌ FALSE') . "\n";
} else {
    echo "❌ KHÔNG TÌM THẤY ADMIN\n";
}
