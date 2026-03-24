<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UpdateProductTypeSeeder extends Seeder
{
    public function run(): void
    {
        // Thêm cột type nếu chưa có
        if (!DB::getSchemaBuilder()->hasColumn('products', 'type')) {
            DB::statement('ALTER TABLE products ADD COLUMN type VARCHAR(255) NULL AFTER category_id');
        }

        // Lấy category thời trang nữ
        $category = DB::table('categories')->where('slug', 'thoi-trang-nu')->first();
        
        if ($category) {
            // Cập nhật sản phẩm với type
            $products = DB::table('products')->where('category_id', $category->id)->get();
            
            foreach ($products as $product) {
                $type = $this->getTypeByName($product->name);
                DB::table('products')->where('id', $product->id)->update(['type' => $type]);
            }
        }
    }

    private function getTypeByName($name): string
    {
        $name = strtolower($name);
        
        if (strpos($name, 'váy') !== false || strpos($name, 'vay') !== false) {
            return 'vay';
        } elseif (strpos($name, 'áo') !== false || strpos($name, 'ao') !== false) {
            return 'ao';
        } elseif (strpos($name, 'quần') !== false || strpos($name, 'quan') !== false) {
            return 'quan';
        }
        
        return 'ao'; // Mặc định là áo
    }
}
