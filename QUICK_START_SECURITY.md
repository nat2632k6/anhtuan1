# 🚀 Quick Start Guide - Security Fixes

## ⚡ Các bước cần làm ngay

### 1. Cập nhật Composer Autoload
```bash
composer dump-autoload
```

### 2. Chạy Migration
```bash
php artisan migrate
```

### 3. Clear Cache
```bash
php artisan config:clear
php artisan cache:clear
php artisan view:clear
```

### 4. Restart Server
```bash
php artisan serve
```

---

## ✅ Kiểm tra các fix

### Test Race Condition
```bash
# Mở 2 terminal, cùng lúc order sản phẩm cuối cùng
# Kết quả: Chỉ 1 người được order thành công
```

### Test Validation
```bash
# Test quantity âm: /cart/add?product_id=1&quantity=-5
# Kết quả: Lỗi validation

# Test phone không hợp lệ: /checkout với phone=abc
# Kết quả: Lỗi validation
```

### Test XSS Protection
```bash
# Test comment: <script>alert('XSS')</script>
# Kết quả: Script bị escape, không chạy
```

### Test Coupon Exploit
```bash
# Dùng coupon lần 1: Thành công
# Dùng coupon lần 2: Lỗi "Đã dùng hết lượt"
```

### Test Authorization
```bash
# Đăng nhập user A
# Truy cập /my-orders/2 (của user B)
# Kết quả: Lỗi "Không có quyền"
```

---

## 📊 Monitoring

### Xem Audit Log
```bash
tail -f storage/logs/audit.log
```

### Xem Error Log
```bash
tail -f storage/logs/laravel.log
```

---

## 🔧 Troubleshooting

### Lỗi: Class not found
```bash
composer dump-autoload
```

### Lỗi: Migration failed
```bash
php artisan migrate:rollback
php artisan migrate
```

### Lỗi: Permission denied
```bash
chmod -R 775 storage/
chmod -R 775 bootstrap/cache/
```

---

## 📝 Checklist

- [ ] Chạy `composer dump-autoload`
- [ ] Chạy `php artisan migrate`
- [ ] Chạy `php artisan config:clear`
- [ ] Restart server
- [ ] Test race condition
- [ ] Test validation
- [ ] Test XSS protection
- [ ] Test coupon exploit
- [ ] Test authorization
- [ ] Kiểm tra audit log
- [ ] Kiểm tra error log

---

## 📞 Support

Nếu có vấn đề, kiểm tra:
1. `storage/logs/laravel.log` - Error logs
2. `storage/logs/audit.log` - Audit trail
3. Chạy `php artisan tinker` để debug

---

## 📚 Tài liệu

- `COMPREHENSIVE_SECURITY_FIXES.md` - Chi tiết tất cả fixes
- `SECURITY_FIXES_SUMMARY.md` - Tóm tắt fixes
