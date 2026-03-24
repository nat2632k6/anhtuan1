<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\Banner;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class HomeController extends Controller
{
    public function index(Request $request)
    {
        if (Category::count() == 0) {
            Category::insert([
                ['name' => 'Thời trang Nam', 'slug' => 'thoi-trang-nam', 'created_at' => now(), 'updated_at' => now()],
                ['name' => 'Thời trang Nữ', 'slug' => 'thoi-trang-nu', 'created_at' => now(), 'updated_at' => now()],
                ['name' => 'Trẻ em', 'slug' => 'tre-em', 'created_at' => now(), 'updated_at' => now()],
                ['name' => 'Nước hoa', 'slug' => 'nuoc-hoa', 'created_at' => now(), 'updated_at' => now()],
            ]);
        }
        
        if (Product::count() == 0) {
            Product::insert([
                ['name' => 'Áo sơ mi nam', 'slug' => 'ao-so-mi-nam', 'price' => 299000, 'category_id' => 1, 'stock' => 50, 'created_at' => now(), 'updated_at' => now()],
                ['name' => 'Váy đầm nữ', 'slug' => 'vay-dam-nu', 'price' => 450000, 'category_id' => 2, 'stock' => 30, 'created_at' => now(), 'updated_at' => now()],
                ['name' => 'Áo thun trẻ em', 'slug' => 'ao-thun-tre-em', 'price' => 150000, 'category_id' => 3, 'stock' => 40, 'created_at' => now(), 'updated_at' => now()],
                ['name' => 'Nước hoa Chanel', 'slug' => 'nuoc-hoa-chanel', 'price' => 2500000, 'category_id' => 4, 'stock' => 15, 'created_at' => now(), 'updated_at' => now()],
                ['name' => 'Quần jean nam', 'slug' => 'quan-jean-nam', 'price' => 520000, 'category_id' => 1, 'stock' => 25, 'created_at' => now(), 'updated_at' => now()],
                ['name' => 'Áo blouse nữ', 'slug' => 'ao-blouse-nu', 'price' => 320000, 'category_id' => 2, 'stock' => 35, 'created_at' => now(), 'updated_at' => now()],
            ]);
        }
        
        $categories = Category::take(8)->get();
        $banners = Banner::where('is_active', true)->orderBy('order')->get();
        $query = Product::with('category');
        
        if ($request->search) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }
        
        if ($request->category) {
            $query->where('category_id', $request->category);
        }
        
        if ($request->sort) {
            switch($request->sort) {
                case 'price_asc':
                    $query->orderBy('price', 'asc');
                    break;
                case 'price_desc':
                    $query->orderBy('price', 'desc');
                    break;
                case 'name':
                    $query->orderBy('name', 'asc');
                    break;
                default:
                    $query->latest();
            }
        } else {
            $query->latest();
        }
        
        if ($request->min_price) {
            $query->where('price', '>=', $request->min_price);
        }
        
        if ($request->max_price) {
            $query->where('price', '<=', $request->max_price);
        }
        
        $menId = Category::where('slug', 'thoi-trang-nam')->value('id');
$womenId = Category::where('slug', 'thoi-trang-nu')->value('id');
$kidsId = Category::where('slug', 'tre-em')->value('id');
$perfumeId = Category::where('slug', 'nuoc-hoa')->value('id');

$men = Product::where('category_id', $menId)->inRandomOrder()->take(2)->get();
$women = Product::where('category_id', $womenId)->inRandomOrder()->take(2)->get();
$kids = Product::where('category_id', $kidsId)->inRandomOrder()->take(2)->get();
$perfume = Product::where('category_id', $perfumeId)->inRandomOrder()->take(2)->get();

$latestProducts = $men
    ->merge($women)
    ->merge($kids)
    ->merge($perfume)
    ->shuffle();
        
        // Fetch best selling products
        $bestSellingIds = DB::table('products')
            ->join('order_items', 'products.id', '=', 'order_items.product_id')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->where('orders.status', 'completed')
            ->select('products.id', DB::raw('SUM(order_items.quantity) as total_sold'))
            ->groupBy('products.id')
            ->orderByDesc('total_sold')
            ->limit(8)
            ->pluck('id')
            ->toArray();
        
        if (empty($bestSellingIds)) {
            $bestSellingProducts = collect();
        } else {
            $caseStatement = 'CASE id';
            foreach ($bestSellingIds as $index => $id) {
                $caseStatement .= ' WHEN ' . $id . ' THEN ' . $index;
            }
            $caseStatement .= ' END';
            
            $bestSellingProducts = Product::with('category')
                ->whereIn('id', $bestSellingIds)
                ->orderByRaw(DB::raw($caseStatement))
                ->get();
        }
        
        return view('home', compact('categories', 'latestProducts', 'banners', 'bestSellingProducts'));
    }

    public function products(Request $request)
    {
        $categories = Category::all();
        $query = Product::with('category');
        
        if ($request->search) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }
        
        if ($request->category) {
            $query->where('category_id', $request->category);
        }
        
        if ($request->sort) {
            switch($request->sort) {
                case 'price_asc':
                    $query->orderBy('price', 'asc');
                    break;
                case 'price_desc':
                    $query->orderBy('price', 'desc');
                    break;
                case 'name':
                    $query->orderBy('name', 'asc');
                    break;
                default:
                    $query->latest();
            }
        } else {
            $query->latest();
        }
        
        if ($request->min_price) {
            $query->where('price', '>=', $request->min_price);
        }
        
        if ($request->max_price) {
            $query->where('price', '<=', $request->max_price);
        }
        
        $products = $query->paginate(12);
        
        return view('products', compact('products', 'categories'));
    }

    public function shop(Request $request)
    {
        $query = Product::with('category');
        
        if ($request->has('category')) {
            $category = Category::where('slug', $request->category)->first();
            if ($category) {
                $query->where('category_id', $category->id);
            }
        }
        
        $products = $query->paginate(12);
        $categories = Category::all();
        
        return view('shop', compact('products', 'categories'));
    }
}