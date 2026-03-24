# 🧪 Security Testing Guide

## 1️⃣ Race Condition Testing

### Scenario: 2 người mua cùng lúc sản phẩm cuối cùng

**Setup:**
- Tạo sản phẩm với stock = 1
- Mở 2 browser khác nhau

**Test Steps:**
1. Browser 1: Thêm sản phẩm vào giỏ
2. Browser 2: Thêm sản phẩm vào giỏ
3. Browser 1: Checkout
4. Browser 2: Checkout

**Expected Result:**
- Browser 1: Checkout thành công
- Browser 2: Lỗi "Không đủ số lượng trong kho"

**Verify:**
```bash
# Kiểm tra database
php artisan tinker
>>> Product::find(1)->stock
=> 0
>>> Order::count()
=> 1
```

---

## 2️⃣ Validation Testing

### Test 2.1: Quantity Validation

**Test Cases:**
```
POST /cart/add
{
  "product_id": 1,
  "quantity": -5
}
```

**Expected:** 400 error - "quantity must be at least 1"

```
POST /cart/add
{
  "product_id": 1,
  "quantity": 101
}
```

**Expected:** 400 error - "quantity may not be greater than 100"

### Test 2.2: Phone Validation

**Test Cases:**
```
POST /checkout/process
{
  "phone": "abc"
}
```

**Expected:** 422 error - "phone must be 10-11 digits"

```
POST /checkout/process
{
  "phone": "0123456789"
}
```

**Expected:** 200 success

### Test 2.3: Email Validation

**Test Cases:**
```
POST /checkout/process
{
  "email": "invalid-email"
}
```

**Expected:** 422 error - "email must be valid"

---

## 3️⃣ XSS Protection Testing

### Test 3.1: Comment XSS

**Test Steps:**
1. Đăng nhập
2. Viết review với comment: `<script>alert('XSS')</script>`
3. Xem review

**Expected:** Script không chạy, hiển thị text bình thường

**Verify:**
```bash
# Kiểm tra database
php artisan tinker
>>> Review::latest()->first()->comment
=> "&lt;script&gt;alert('XSS')&lt;/script&gt;"
```

### Test 3.2: Name XSS

**Test Steps:**
1. Cập nhật profile với name: `<img src=x onerror=alert('XSS')>`
2. Xem profile

**Expected:** Hình ảnh không load, không có alert

---

## 4️⃣ SQL Injection Testing

### Test 4.1: Search SQL Injection

**Test Cases:**
```
GET /admin/orders?search='; DROP TABLE orders; --
```

**Expected:** 
- Không có error
- Dữ liệu không bị xóa
- Hiển thị kết quả search bình thường

**Verify:**
```bash
php artisan tinker
>>> Order::count()
=> [số lượng orders vẫn nguyên]
```

### Test 4.2: LIKE Injection

**Test Cases:**
```
GET /admin/products?search=%' OR '1'='1
```

**Expected:** Không có error, kết quả search bình thường

---

## 5️⃣ Coupon Exploit Testing

### Test 5.1: Per-User Usage Limit

**Setup:**
- Tạo coupon với `usage_per_user = 1`

**Test Steps:**
1. Lần 1: Apply coupon → Thành công
2. Lần 2: Apply coupon → Lỗi "Đã dùng hết lượt"

**Expected:** Lỗi "Mã giảm giá không hợp lệ hoặc đã dùng hết lượt"

**Verify:**
```bash
php artisan tinker
>>> Coupon::find(1)->usage_per_user
=> 1
```

### Test 5.2: Global Usage Limit

**Setup:**
- Tạo coupon với `usage_limit = 2`

**Test Steps:**
1. User A: Apply coupon → Thành công
2. User B: Apply coupon → Thành công
3. User C: Apply coupon → Lỗi

**Expected:** User C nhận lỗi "Mã giảm giá không hợp lệ"

---

## 6️⃣ Authorization Testing

### Test 6.1: Order Ownership

**Setup:**
- User A có order ID 1
- User B có order ID 2

**Test Steps:**
1. Đăng nhập User A
2. Truy cập `/my-orders/2`

**Expected:** Lỗi 403 "Không có quyền truy cập"

**Verify:**
```bash
# Kiểm tra middleware
php artisan tinker
>>> Order::find(2)->user_id
=> 2
>>> auth()->id()
=> 1
```

### Test 6.2: Address Ownership

**Test Steps:**
1. Đăng nhập User A
2. Xóa address của User B

**Expected:** Lỗi "Địa chỉ không tồn tại"

---

## 7️⃣ Rate Limiting Testing

### Test 7.1: Request Limit

**Test Steps:**
```bash
# Gửi 11 requests trong 1 phút
for i in {1..11}; do
  curl -X POST http://localhost:8000/checkout/process
done
```

**Expected:** Request ke-11 nhận 429 status code

---

## 8️⃣ Exception Handling Testing

### Test 8.1: File Upload Error

**Test Steps:**
1. Upload file > 2MB
2. Upload file format không hợp lệ

**Expected:** Lỗi validation, không crash

### Test 8.2: Database Error

**Test Steps:**
1. Disconnect database
2. Thực hiện action (checkout, update profile)

**Expected:** Lỗi "Lỗi khi xử lý", không crash

---

## 9️⃣ Input Sanitization Testing

### Test 9.1: HTML Tags Removal

**Test Steps:**
1. Update profile với name: `John <b>Doe</b>`
2. Xem profile

**Expected:** Hiển thị "John Doe" (không có tag)

**Verify:**
```bash
php artisan tinker
>>> User::find(1)->name
=> "John Doe"
```

### Test 9.2: Special Characters

**Test Steps:**
1. Update address với: `123 Main St'; DROP TABLE--`
2. Xem address

**Expected:** Hiển thị bình thường, không có error

---

## 🔟 Audit Logging Testing

### Test 10.1: Order Creation Log

**Test Steps:**
1. Tạo order
2. Kiểm tra audit log

**Expected:**
```bash
tail -f storage/logs/audit.log
# Sẽ thấy:
# [timestamp] order_created - Order ID: 1, User: 1, Amount: 100000
```

### Test 10.2: Product Update Log

**Test Steps:**
1. Update product
2. Kiểm tra audit log

**Expected:**
```bash
tail -f storage/logs/audit.log
# Sẽ thấy:
# [timestamp] product_updated - Product ID: 1, Changes: {...}
```

---

## 📊 Test Results Template

```
Test Date: ___________
Tester: ___________

| Test | Status | Notes |
|------|--------|-------|
| Race Condition | ✅/❌ | |
| Quantity Validation | ✅/❌ | |
| Phone Validation | ✅/❌ | |
| XSS Protection | ✅/❌ | |
| SQL Injection | ✅/❌ | |
| Coupon Exploit | ✅/❌ | |
| Authorization | ✅/❌ | |
| Rate Limiting | ✅/❌ | |
| Exception Handling | ✅/❌ | |
| Input Sanitization | ✅/❌ | |
| Audit Logging | ✅/❌ | |

Overall Status: ✅/❌
```

---

## 🐛 Debugging Tips

### Xem SQL Query
```bash
php artisan tinker
>>> DB::enableQueryLog()
>>> Order::all()
>>> dd(DB::getQueryLog())
```

### Xem Request Data
```php
// Trong controller
dd($request->all());
```

### Xem Middleware Execution
```bash
php artisan tinker
>>> auth()->user()
>>> auth()->check()
```

---

## 📝 Checklist

- [ ] Race Condition test
- [ ] Validation tests
- [ ] XSS tests
- [ ] SQL Injection tests
- [ ] Coupon tests
- [ ] Authorization tests
- [ ] Rate Limiting tests
- [ ] Exception Handling tests
- [ ] Input Sanitization tests
- [ ] Audit Logging tests
- [ ] Tất cả tests pass ✅
