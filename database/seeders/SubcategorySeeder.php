<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SubcategorySeeder extends Seeder
{
    public function run(): void
    {
        $category = DB::table('categories')->where('slug', 'thoi-trang-nu')->first();
        
        if ($category) {
            DB::table('subcategories')->insert([
                ['category_id' => $category->id, 'name' => 'Váy', 'slug' => 'vay', 'created_at' => now(), 'updated_at' => now()],
                ['category_id' => $category->id, 'name' => 'Áo', 'slug' => 'ao', 'created_at' => now(), 'updated_at' => now()],
                ['category_id' => $category->id, 'name' => 'Quần', 'slug' => 'quan', 'created_at' => now(), 'updated_at' => now()],
            ]);
        }
    }
}
