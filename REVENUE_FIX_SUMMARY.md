## Sửa chữa: Doanh thu không tự cập nhật khi hoàn thành đơn hàng

### Vấn đề
Doanh thu không tự cập nhật vì có sự không khớp giữa trạng thái đơn hàng:
- Workflow hiện tại: pending → confirmed → shipping → **delivered**
- Nhưng code tính doanh thu tìm kiếm: **completed**

### Giải pháp

#### 1. Cập nhật AdminOrderController.php
- Thay đổi workflow: `'delivered' => 'delivered'` thành `'delivered' => 'completed'`
- Cập nhật thông báo từ 'delivered' thành 'completed'

#### 2. Cập nhật view admin/orders/show.blade.php
- Thêm case 'completed' vào switch statement
- Cập nhật nút hành động để hiển thị trạng thái 'completed'

#### 3. Tạo migration mới
- File: `2026_03_10_update_order_status_delivered_to_completed.php`
- Cập nhật tất cả đơn hàng cũ từ 'delivered' → 'completed'

#### 4. Chạy migration
```bash
php artisan migrate
```

### Kết quả
✅ Khi admin nhấn "Hoàn thành đơn", trạng thái sẽ thay đổi thành 'completed'
✅ Doanh thu sẽ tự động cập nhật trong dashboard
✅ Tất cả đơn hàng cũ đã được cập nhật

### Workflow mới
pending → confirmed → shipping → **completed** (thay vì delivered)

### Các controller sử dụng doanh thu
- AdminDashboardController: Tính doanh thu từ status='completed' + payment_status='paid'
- AdminRevenueController: Tính doanh thu từ status='completed' + payment_status='paid'
