@echo off
echo ========================================
echo   KHOI DONG UNISHOP
echo ========================================
echo.

echo [1/4] Kiem tra MySQL qua phpMyAdmin...
php artisan db:show 2>nul
if errorlevel 1 (
    echo ❌ MySQL chua chay!
    echo ➡ Hay bat XAMPP Control Panel va Start MySQL
    echo ➡ Hoac mo phpMyAdmin de kiem tra
    pause
    exit
)
echo ✅ MySQL dang chay

echo.
echo [2/4] Kiem tra database 'unishop'...
php -r "try { $pdo = new PDO('mysql:host=127.0.0.1;dbname=unishop', 'root', ''); echo 'OK'; } catch(Exception $e) { echo 'FAIL'; exit(1); }" 2>nul
if errorlevel 1 (
    echo ❌ Database 'unishop' chua ton tai!
    echo ➡ Vao phpMyAdmin (http://localhost/phpmyadmin)
    echo ➡ Tao database moi ten 'unishop'
    pause
    exit
)
echo ✅ Database 'unishop' da ton tai

echo.
echo [3/4] Kiem tra admin...
type setup-admin.php | php artisan tinker

echo.
echo [4/4] Khoi dong server...
echo ✅ Server dang chay tai: http://localhost:8000
echo ✅ Admin login: http://localhost:8000/admin/login
echo ✅ phpMyAdmin: http://localhost/phpmyadmin
echo.
php artisan serve
