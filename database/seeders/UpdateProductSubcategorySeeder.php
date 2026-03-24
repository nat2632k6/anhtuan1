<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UpdateProductSubcategorySeeder extends Seeder
{
    public function run(): void
    {
        $category = DB::table('categories')->where('slug', 'thoi-trang-nu')->first();
        
        if ($category) {
            $products = DB::table('products')->where('category_id', $category->id)->get();
            
            foreach ($products as $product) {
                $subcategorySlug = $this->getSubcategoryByName($product->name);
                $subcategory = DB::table('subcategories')->where('slug', $subcategorySlug)->first();
                
                if ($subcategory) {
                    DB::table('products')->where('id', $product->id)->update(['subcategory_id' => $subcategory->id]);
                }
            }
        }
    }

    private function getSubcategoryByName($name): string
    {
        $name = strtolower($name);
        
        if (strpos($name, 'váy') !== false || strpos($name, 'vay') !== false) {
            return 'vay';
        } elseif (strpos($name, 'quần') !== false || strpos($name, 'quan') !== false) {
            return 'quan';
        }
        
        return 'ao';
    }
}
