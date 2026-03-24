<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\Banner;
use App\Models\Subcategory;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    // amazonq-ignore-next-line
    public function show($slug, Request $request)
    {
        $category = Category::where('slug', $slug)->firstOrFail();
        $query = Product::where('category_id', $category->id);
        
        // Lọc theo subcategory cho thời trang nữ
        if ($category->slug === 'thoi-trang-nu' && $request->has('subcategory')) {
            $subcategory = Subcategory::where('slug', $request->query('subcategory'))->first();
            if ($subcategory) {
                $query->where('subcategory_id', $subcategory->id);
            }
        }
        
        $products = $query->take(12)->get();
        $subcategories = Subcategory::where('category_id', $category->id)->get();
        $banners = Banner::where('category_id', $category->id)
                        ->where('is_active', true)
                        ->orderBy('order')
                        ->get();
        
        return view('category-products', compact('category', 'products', 'subcategories', 'banners'));
    }
}