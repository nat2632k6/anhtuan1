<?php

namespace App\Http\Controllers;

use App\Models\Banner;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AdminBannerController extends Controller
{
    public function index()
    {
        $banners = Banner::with('category')->orderBy('order')->paginate(20);
        return view('admin.banners.index', compact('banners'));
    }

    public function create()
    {
        $categories = Category::all();
        return view('admin.banners.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|max:255',
            'image' => 'required|image|max:2048',
            'link' => 'nullable|url',
            'order' => 'nullable|integer',
        ]);

        $data = $request->only(['title', 'description', 'category_id', 'link', 'order']);
        $data['is_active'] = $request->has('is_active');
        $data['order'] = $data['order'] ?? Banner::max('order') + 1 ?? 1;
        
        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('banners', 'public');
        }

        Banner::create($data);
        return redirect()->route('admin.banners.index')->with('success', 'Tạo banner thành công');
    }

    public function edit(Banner $banner)
    {
        $categories = Category::all();
        return view('admin.banners.edit', compact('banner', 'categories'));
    }

    public function update(Request $request, Banner $banner)
    {
        $request->validate([
            'title' => 'required|max:255',
            'image' => 'nullable|image|max:2048',
            'link' => 'nullable|url',
            'order' => 'nullable|integer',
        ]);

        $data = $request->only(['title', 'description', 'category_id', 'link', 'order']);
        $data['is_active'] = $request->has('is_active');
        $data['order'] = $data['order'] ?? $banner->order ?? 0;
        
        if ($request->hasFile('image')) {
            if ($banner->image) {
                Storage::disk('public')->delete($banner->image);
            }
            $data['image'] = $request->file('image')->store('banners', 'public');
        }

        $banner->update($data);
        return redirect()->route('admin.banners.index')->with('success', 'Cập nhật banner thành công');
    }

    public function destroy(Banner $banner)
    {
        if ($banner->image) {
            Storage::disk('public')->delete($banner->image);
        }
        $banner->delete();
        return redirect()->route('admin.banners.index')->with('success', 'Xóa banner thành công');
    }

    public function toggleStatus(Banner $banner)
    {
        $banner->update(['is_active' => !$banner->is_active]);
        return back()->with('success', 'Cập nhật trạng thái thành công');
    }
}
