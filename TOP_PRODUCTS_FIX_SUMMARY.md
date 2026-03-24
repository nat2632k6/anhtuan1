## Sửa chữa: Top sản phẩm bán chạy không được cập nhật đúng

### Vấn đề
Top sản phẩm bán chạy chỉ tính từ đơn hàng có `status = 'completed'` nhưng **KHÔNG kiểm tra `payment_status = 'paid'`**

Điều này dẫn đến:
- Bao gồm các đơn hoàn thành nhưng chưa thanh toán
- Số liệu không chính xác so với doanh thu thực tế

### Giải pháp

#### 1. Cập nhật AdminDashboardController.php
Thêm điều kiện `->where('orders.payment_status', 'paid')` vào query top sản phẩm

**Trước:**
```php
$topProducts = OrderItem::join('orders', 'order_items.order_id', '=', 'orders.id')
    ->where('orders.status', 'completed')
    ->select(...)
```

**Sau:**
```php
$topProducts = OrderItem::join('orders', 'order_items.order_id', '=', 'orders.id')
    ->where('orders.status', 'completed')
    ->where('orders.payment_status', 'paid')
    ->select(...)
```

#### 2. Cập nhật AdminRevenueController.php
Thêm điều kiện `->where('orders.payment_status', 'paid')` vào query top sản phẩm

**Trước:**
```php
$topProducts = DB::table('order_items')
    ->join('orders', 'order_items.order_id', '=', 'orders.id')
    ->where('orders.status', 'completed')
    ->select(...)
```

**Sau:**
```php
$topProducts = DB::table('order_items')
    ->join('orders', 'order_items.order_id', '=', 'orders.id')
    ->where('orders.status', 'completed')
    ->where('orders.payment_status', 'paid')
    ->select(...)
```

### Kết quả
✅ Top sản phẩm bán chạy chỉ tính từ đơn hàng đã hoàn thành VÀ đã thanh toán
✅ Số liệu chính xác và đồng bộ với doanh thu
✅ Hiển thị đúng trên Dashboard và Revenue Report

### Các nơi hiển thị top sản phẩm
1. **Dashboard** - Top 5 sản phẩm bán chạy
2. **Revenue Report** - Top 10 sản phẩm bán chạy
