# HƯỚNG DẪN TÌM KIẾM SẢN PHẨM THEO ID

## Cách hoạt động:

### 1. Form tìm kiếm (trong layouts/app.blade.php)
```html
<form action="{{ route('home') }}" method="GET" class="relative">
    <input type="text" 
           name="search" 
           placeholder="Tìm kiếm..."
           value="{{ request('search') }}"
           class="...">
</form>
```

**Giải thích:**
- `name="search"` → Khi submit form, dữ liệu sẽ được gửi với tên là "search"
- `method="GET"` → Dữ liệu gửi qua URL (ví dụ: ?search=1)
- `action="{{ route('home') }}"` → Gửi về trang chủ (HomeController@index)

---

### 2. Xử lý trong Controller (HomeController.php)

```php
public function index(Request $request)
{
    // Bước 1: Tạo query builder
    $query = Product::with('category');
    
    // Bước 2: Kiểm tra có tìm kiếm không
    if ($request->has('search') && $request->search != '') {
        $search = $request->search;
        
        // Bước 3: Phân biệt tìm theo ID hay tên
        if (is_numeric($search)) {
            // Nếu là số → Tìm theo ID
            $query->where('id', $search);
        } else {
            // Nếu là chữ → Tìm theo tên
            $query->where('name', 'like', '%' . $search . '%');
        }
    }
    
    // Bước 4: Lấy kết quả
    $latestProducts = $query->latest()->take(8)->get();
    
    return view('home', compact('categories', 'latestProducts'));
}
```

---

## Chi tiết từng bước:

### Bước 1: Nhận dữ liệu từ form
```php
$request->has('search')  // Kiểm tra có tham số 'search' không
$request->search         // Lấy giá trị của 'search'
```

**Ví dụ:**
- URL: `http://localhost/?search=1`
- `$request->search` = `"1"`

---

### Bước 2: Kiểm tra kiểu dữ liệu
```php
is_numeric($search)  // Kiểm tra có phải là số không
```

**Ví dụ:**
- `is_numeric("1")` → `true` (là số)
- `is_numeric("áo")` → `false` (không phải số)

---

### Bước 3: Tìm kiếm theo ID
```php
$query->where('id', $search);
```

**SQL tương đương:**
```sql
SELECT * FROM products WHERE id = 1
```

**Ví dụ:**
- Nhập `1` → Tìm sản phẩm có `id = 1`
- Nhập `5` → Tìm sản phẩm có `id = 5`

---

### Bước 4: Tìm kiếm theo tên
```php
$query->where('name', 'like', '%' . $search . '%');
```

**SQL tương đương:**
```sql
SELECT * FROM products WHERE name LIKE '%áo%'
```

**Ví dụ:**
- Nhập `áo` → Tìm tất cả sản phẩm có chữ "áo" trong tên
- Nhập `chanel` → Tìm sản phẩm có chữ "chanel" trong tên

---

## Cách sử dụng:

### 1. Tìm theo ID:
- Vào trang web
- Nhập số ID vào ô tìm kiếm (ví dụ: `1`, `2`, `3`)
- Nhấn Enter hoặc click nút tìm kiếm
- Kết quả: Hiển thị sản phẩm có ID đó

### 2. Tìm theo tên:
- Vào trang web
- Nhập tên sản phẩm (ví dụ: `áo`, `váy`, `nước hoa`)
- Nhấn Enter
- Kết quả: Hiển thị tất cả sản phẩm có tên chứa từ khóa đó

---

## Kiểm tra ID sản phẩm trong database:

### Cách 1: Qua phpMyAdmin
1. Mở phpMyAdmin (http://localhost/phpmyadmin)
2. Chọn database `unishop`
3. Chọn bảng `products`
4. Xem cột `id` để biết ID của từng sản phẩm

### Cách 2: Qua MySQL Command Line
```sql
USE unishop;
SELECT id, name FROM products;
```

### Cách 3: Hiển thị ID trên trang web
Thêm vào view để hiển thị ID:
```php
<p>ID: {{ $product->id }}</p>
```

---

## Ví dụ thực tế:

Giả sử database có:
```
id | name              | price
---|-------------------|--------
1  | Áo sơ mi nam      | 299000
2  | Váy đầm nữ        | 450000
3  | Áo thun trẻ em    | 150000
4  | Nước hoa Chanel   | 2500000
```

**Tìm kiếm:**
- Nhập `1` → Kết quả: "Áo sơ mi nam"
- Nhập `4` → Kết quả: "Nước hoa Chanel"
- Nhập `áo` → Kết quả: "Áo sơ mi nam", "Áo thun trẻ em"
- Nhập `chanel` → Kết quả: "Nước hoa Chanel"

---

## Lưu ý:

1. **Phân biệt chữ hoa/thường:**
   - Tìm theo ID: Không phân biệt (vì là số)
   - Tìm theo tên: MySQL mặc định không phân biệt

2. **Không tìm thấy:**
   - Nếu không có kết quả, trang sẽ hiển thị "Chưa có sản phẩm nào"

3. **Tìm nhiều từ khóa:**
   - Hiện tại chỉ hỗ trợ 1 từ khóa
   - Muốn tìm nhiều từ, cần cải tiến thêm

---

## Mở rộng (nếu cần):

### Tìm theo cả ID và tên cùng lúc:
```php
if (is_numeric($search)) {
    $query->where('id', $search)
          ->orWhere('name', 'like', '%' . $search . '%');
} else {
    $query->where('name', 'like', '%' . $search . '%');
}
```

### Tìm theo nhiều trường:
```php
$query->where(function($q) use ($search) {
    $q->where('name', 'like', '%' . $search . '%')
      ->orWhere('description', 'like', '%' . $search . '%');
});
```
