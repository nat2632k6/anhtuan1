<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductImage;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AdminProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::with('category');
        
        if ($request->has('search') && !empty($request->search)) {
            $query->where('name', 'like', '%' . addslashes($request->search) . '%');
        }
        
        if ($request->has('category') && !empty($request->category)) {
            $query->where('category_id', $request->category);
        }
        
        if ($request->has('product_type') && !empty($request->product_type)) {
            if ($request->product_type == 'new') {
                $query->where('created_at', '>=', now()->subDays(30));
            } elseif ($request->product_type == 'old') {
                $query->where('created_at', '<', now()->subDays(30));
            }
        }
        
        $products = $query->paginate(10);
        $categories = Category::all();
        
        return view('admin-products-index', compact('products', 'categories'));
    }

    public function create()
    {
        $categories = Category::all();
        return view('admin-products-create', compact('categories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|max:255|string',
            'price' => 'required|numeric|min:0|max:999999999',
            'discount_amount' => 'nullable|numeric|min:0|max:999999999',
            'category_id' => 'required|integer|exists:categories,id',
            'stock' => 'required|integer|min:0|max:999999',
            'description' => 'nullable|string|max:5000',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        try {
            $data = $request->all();
            $data['slug'] = Str::slug($request->name);

            if ($request->hasFile('image')) {
                $image = $request->file('image');
                $imageName = time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();
                $image->move(public_path('images/products'), $imageName);
                $data['image'] = 'images/products/' . $imageName;
            }

            $product = Product::create($data);

            if ($request->hasFile('images')) {
                $order = 0;
                $isFirst = true;
                foreach ($request->file('images') as $file) {
                    $imageName = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                    $file->move(public_path('images/products'), $imageName);
                    ProductImage::create([
                        'product_id' => $product->id,
                        'image' => 'images/products/' . $imageName,
                        'is_main' => $isFirst,
                        'order' => $order++
                    ]);
                    $isFirst = false;
                }
            }

            return redirect()->route('admin.products.index')->with('success', 'Sản phẩm đã được tạo thành công!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Lỗi khi tạo sản phẩm: ' . $e->getMessage());
        }
    }

    public function show(Product $product)
    {
        return view('admin-products-show', compact('product'));
    }

    public function edit(Product $product)
    {
        $categories = Category::all();
        return view('admin-products-edit', compact('product', 'categories'));
    }

    public function update(Request $request, Product $product)
    {
        $request->validate([
            'name' => 'required|max:255|string',
            'price' => 'required|numeric|min:0|max:999999999',
            'discount_amount' => 'nullable|numeric|min:0|max:999999999',
            'category_id' => 'required|integer|exists:categories,id',
            'stock' => 'required|integer|min:0|max:999999',
            'description' => 'nullable|string|max:5000',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        try {
            $data = $request->all();
            $data['slug'] = Str::slug($request->name);

            if ($request->hasFile('image')) {
                if ($product->image && file_exists(public_path($product->image))) {
                    @unlink(public_path($product->image));
                }
                
                $image = $request->file('image');
                $imageName = time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();
                $image->move(public_path('images/products'), $imageName);
                $data['image'] = 'images/products/' . $imageName;
            }

            $product->update($data);

            if ($request->hasFile('images')) {
                $order = $product->images()->max('order') ?? 0;
                foreach ($request->file('images') as $file) {
                    $imageName = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                    $file->move(public_path('images/products'), $imageName);
                    ProductImage::create([
                        'product_id' => $product->id,
                        'image' => 'images/products/' . $imageName,
                        'is_main' => false,
                        'order' => ++$order
                    ]);
                }
            }

            return redirect()->route('admin.products.index')->with('success', 'Sản phẩm đã được cập nhật thành công!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Lỗi khi cập nhật sản phẩm: ' . $e->getMessage());
        }
    }

    public function destroy(Product $product)
    {
        try {
            if ($product->image && file_exists(public_path($product->image))) {
                @unlink(public_path($product->image));
            }
            
            foreach ($product->images as $img) {
                if (file_exists(public_path($img->image))) {
                    @unlink(public_path($img->image));
                }
            }
            
            $product->delete();

            return redirect()->route('admin.products.index')->with('success', 'Sản phẩm đã được xóa thành công!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Lỗi khi xóa sản phẩm: ' . $e->getMessage());
        }
    }
}
