HƯỚNG DẪN CÀI ĐẶT VÀ TÀI KHOẢN ĐỒ ÁN UNISHOP

---

1. CÀI ĐẶT DỰ ÁN

Bước 1: Giải nén source code

Bước 2: Mở terminal tại thư mục project và chạy:
composer install

Bước 3: Tạo file môi trường:
cp .env.example .env

Bước 4: Tạo key cho Laravel:
php artisan key:generate

Bước 5: Cấu hình database trong file .env:

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=unishop
DB_USERNAME=root
DB_PASSWORD=

Bước 6: Import database:

* Import file: unishop.sql vào MySQL
  (Hoặc chạy: php artisan migrate --seed nếu có)

Bước 7: Tạo bảng session (do dùng SESSION_DRIVER=database):
php artisan session:table
php artisan migrate

Bước 8: Tạo link hiển thị ảnh:
php artisan storage:link

Bước 9: Chạy project:
php artisan serve

Truy cập: http://127.0.0.1:8000

---

2. TÀI KHOẢN ĐĂNG NHẬP

Admin:
Email: [admin@unishop.com](mailto:admin@unishop.com)
Password: admin123

User:
Email: [tuanvip022@gmail.com](mailto:tuanvip022@gmail.com)
Password: 1234567890

---

3. LƯU Ý

* Nếu lỗi thiếu thư viện → chạy: composer install
* Nếu lỗi key → chạy: php artisan key:generate
* Nếu lỗi database → kiểm tra file .env
* Nếu không hiển thị ảnh → chạy: php artisan storage:link

