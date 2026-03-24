<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        // Tạo categories
        $categories = [
            ['name' => 'Thời trang Nam', 'slug' => 'thoi-trang-nam'],
            ['name' => 'Thời trang Nữ', 'slug' => 'thoi-trang-nu'],
            ['name' => 'Trẻ em', 'slug' => 'tre-em'],
            ['name' => 'Nước hoa', 'slug' => 'nuoc-hoa'],
        ];

        foreach ($categories as $cat) {
            Category::create($cat);
        }

        // Tạo 48 products (12 mỗi danh mục)
        $products = [
            // Thời trang Nam (12 sản phẩm)
            ['name' => 'Áo sơ mi nam trắng', 'slug' => 'ao-so-mi-nam-trang', 'price' => 299000, 'category_id' => 1, 'stock' => 50, 'image' => 'images/products/1.jpg'],
            ['name' => 'Quần âu nam đen', 'slug' => 'quan-au-nam-den', 'price' => 450000, 'category_id' => 1, 'stock' => 30, 'image' => 'images/products/2.jpg'],
            ['name' => 'Áo polo nam xanh', 'slug' => 'ao-polo-nam-xanh', 'price' => 350000, 'category_id' => 1, 'stock' => 40, 'image' => 'images/products/3.jpg'],
            ['name' => 'áo khoác trẻ em', 'slug' => 'ao-khoac-tre-em', 'price' => 520000, 'category_id' => 1, 'stock' => 25, 'image' => 'images/products/4.jpg'],
            ['name' => 'Áo khoác nam', 'slug' => 'ao-khoac-nam', 'price' => 680000, 'category_id' => 1, 'stock' => 20, 'image' => 'images/products/5.jpg'],
            ['name' => 'Quần short nam', 'slug' => 'quan-short-nam', 'price' => 180000, 'category_id' => 1, 'stock' => 35, 'image' => 'images/products/6.jpg'],
            ['name' => 'Áo thun nam', 'slug' => 'ao-thun-nam', 'price' => 150000, 'category_id' => 1, 'stock' => 60, 'image' => 'images/products/7.jpg'],
            ['name' => 'Quần kaki nam', 'slug' => 'quan-kaki-nam', 'price' => 380000, 'category_id' => 1, 'stock' => 28, 'image' => 'images/products/8.jpg'],
            ['name' => 'Áo vest nam', 'slug' => 'ao-vest-nam', 'price' => 1200000, 'category_id' => 1, 'stock' => 15],
            ['name' => 'Quần tây nam', 'slug' => 'quan-tay-nam', 'price' => 420000, 'category_id' => 1, 'stock' => 32],
            ['name' => 'Áo hoodie nam', 'slug' => 'ao-hoodie-nam', 'price' => 480000, 'category_id' => 1, 'stock' => 22],
            ['name' => 'Quần jogger nam', 'slug' => 'quan-jogger-nam', 'price' => 280000, 'category_id' => 1, 'stock' => 45],
            
            // Thời trang Nữ (12 sản phẩm)
            ['name' => 'Váy đầm công sở', 'slug' => 'vay-dam-cong-so', 'price' => 380000, 'category_id' => 2, 'stock' => 25],
            ['name' => 'Quần jean nữ skinny', 'slug' => 'quan-jean-nu-skinny', 'price' => 450000, 'category_id' => 2, 'stock' => 30],
            ['name' => 'Áo blouse nữ', 'slug' => 'ao-blouse-nu', 'price' => 320000, 'category_id' => 2, 'stock' => 35],
            ['name' => 'Chân váy midi', 'slug' => 'chan-vay-midi', 'price' => 280000, 'category_id' => 2, 'stock' => 20],
            ['name' => 'Áo kiểu nữ', 'slug' => 'ao-kieu-nu', 'price' => 250000, 'category_id' => 2, 'stock' => 40],
            ['name' => 'Quần cuốc nữ', 'slug' => 'quan-cuoc-nu', 'price' => 180000, 'category_id' => 2, 'stock' => 50],
            ['name' => 'Váy maxi nữ', 'slug' => 'vay-maxi-nu', 'price' => 420000, 'category_id' => 2, 'stock' => 18],
            ['name' => 'Áo croptop nữ', 'slug' => 'ao-croptop-nu', 'price' => 150000, 'category_id' => 2, 'stock' => 45],
            ['name' => 'Quần legging nữ', 'slug' => 'quan-legging-nu', 'price' => 120000, 'category_id' => 2, 'stock' => 55],
            ['name' => 'Áo cardigan nữ', 'slug' => 'ao-cardigan-nu', 'price' => 380000, 'category_id' => 2, 'stock' => 25],
            ['name' => 'Váy ngắn nữ', 'slug' => 'vay-ngan-nu', 'price' => 220000, 'category_id' => 2, 'stock' => 35],
            ['name' => 'Áo sơ mi nữ', 'slug' => 'ao-so-mi-nu', 'price' => 280000, 'category_id' => 2, 'stock' => 30],
            
            // Trẻ em (12 sản phẩm)
            ['name' => 'Áo thun trẻ em', 'slug' => 'ao-thun-tre-em', 'price' => 150000, 'category_id' => 3, 'stock' => 40],
            ['name' => 'Quần short trẻ em', 'slug' => 'quan-short-tre-em', 'price' => 120000, 'category_id' => 3, 'stock' => 35],
            ['name' => 'Đầm bé gái', 'slug' => 'dam-be-gai', 'price' => 200000, 'category_id' => 3, 'stock' => 25],
            ['name' => 'Bộ đồ bé trai', 'slug' => 'bo-do-be-trai', 'price' => 180000, 'category_id' => 3, 'stock' => 30],
            ['name' => 'Áo khoác trẻ em', 'slug' => 'ao-khoac-tre-em', 'price' => 250000, 'category_id' => 3, 'stock' => 20],
            ['name' => 'Quần jean trẻ em', 'slug' => 'quan-jean-tre-em', 'price' => 180000, 'category_id' => 3, 'stock' => 35],
            ['name' => 'Váy trẻ em', 'slug' => 'vay-tre-em', 'price' => 160000, 'category_id' => 3, 'stock' => 40],
            ['name' => 'Áo polo trẻ em', 'slug' => 'ao-polo-tre-em', 'price' => 140000, 'category_id' => 3, 'stock' => 45],
            ['name' => 'Quần dài trẻ em', 'slug' => 'quan-dai-tre-em', 'price' => 130000, 'category_id' => 3, 'stock' => 50],
            ['name' => 'Áo hoodie trẻ em', 'slug' => 'ao-hoodie-tre-em', 'price' => 220000, 'category_id' => 3, 'stock' => 25],
            ['name' => 'Bộ ngủ trẻ em', 'slug' => 'bo-ngu-tre-em', 'price' => 180000, 'category_id' => 3, 'stock' => 30],
            ['name' => 'Áo ba lỗ trẻ em', 'slug' => 'ao-ba-lo-tre-em', 'price' => 100000, 'category_id' => 3, 'stock' => 60],
            
            // Nước hoa (12 sản phẩm)
            ['name' => 'Nước hoa Chanel No.5', 'slug' => 'nuoc-hoa-chanel-no5', 'price' => 2500000, 'category_id' => 4, 'stock' => 15],
            ['name' => 'Nước hoa Dior Sauvage', 'slug' => 'nuoc-hoa-dior-sauvage', 'price' => 1800000, 'category_id' => 4, 'stock' => 20],
            ['name' => 'Nước hoa Calvin Klein', 'slug' => 'nuoc-hoa-calvin-klein', 'price' => 1200000, 'category_id' => 4, 'stock' => 12],
            ['name' => 'Nước hoa Versace Eros', 'slug' => 'nuoc-hoa-versace-eros', 'price' => 1500000, 'category_id' => 4, 'stock' => 18],
            ['name' => 'Nước hoa Gucci Bloom', 'slug' => 'nuoc-hoa-gucci-bloom', 'price' => 2200000, 'category_id' => 4, 'stock' => 10],
            ['name' => 'Nước hoa Tom Ford', 'slug' => 'nuoc-hoa-tom-ford', 'price' => 3500000, 'category_id' => 4, 'stock' => 8],
            ['name' => 'Nước hoa Yves Saint Laurent', 'slug' => 'nuoc-hoa-ysl', 'price' => 1900000, 'category_id' => 4, 'stock' => 15],
            ['name' => 'Nước hoa Armani Code', 'slug' => 'nuoc-hoa-armani-code', 'price' => 1600000, 'category_id' => 4, 'stock' => 22],
            ['name' => 'Nước hoa Paco Rabanne', 'slug' => 'nuoc-hoa-paco-rabanne', 'price' => 1400000, 'category_id' => 4, 'stock' => 25],
            ['name' => 'Nước hoa Burberry', 'slug' => 'nuoc-hoa-burberry', 'price' => 1700000, 'category_id' => 4, 'stock' => 18],
            ['name' => 'Nước hoa Hugo Boss', 'slug' => 'nuoc-hoa-hugo-boss', 'price' => 1300000, 'category_id' => 4, 'stock' => 20],
            ['name' => 'Nước hoa Dolce Gabbana', 'slug' => 'nuoc-hoa-dolce-gabbana', 'price' => 2000000, 'category_id' => 4, 'stock' => 12],
        ];

        foreach ($products as $product) {
            Product::create($product);
        }

        User::create([
            'name' => 'Admin',
            'email' => 'admin@unishop.com',
            'password' => bcrypt('admin123'),
            'role' => 'admin',
        ]);

        User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
            'role' => 'customer',
        ]);
    }
}