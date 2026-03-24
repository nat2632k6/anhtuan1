<?php

namespace App\Http\Controllers;

use App\Models\ProductImage;
use Illuminate\Http\Request;

class ProductImageController extends Controller
{
    public function upload(Request $request)
    {
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
            'product_id' => 'required|exists:products,id'
        ]);

        $file = $request->file('image');
        $imageName = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
        $file->move(public_path('images/products'), $imageName);

        ProductImage::create([
            'product_id' => $request->product_id,
            'image' => 'images/products/' . $imageName,
            'is_main' => false,
            'order' => ProductImage::where('product_id', $request->product_id)->max('order') + 1
        ]);

        return response()->json(['success' => true, 'message' => 'Ảnh đã được tải lên thành công!']);
    }

    public function setMain(ProductImage $image)
    {
        ProductImage::where('product_id', $image->product_id)->update(['is_main' => false]);
        $image->update(['is_main' => true]);

        return response()->json(['success' => true, 'message' => 'Ảnh chính đã được cập nhật!']);
    }

    public function delete(Request $request)
    {
        $image = ProductImage::findOrFail($request->image_id);
        
        if (file_exists(public_path($image->image))) {
            unlink(public_path($image->image));
        }
        
        $image->delete();

        return response()->json(['success' => true, 'message' => 'Ảnh đã được xóa!']);
    }

    public function reorder(Request $request)
    {
        $request->validate(['images' => 'required|array']);

        foreach ($request->images as $index => $imageId) {
            ProductImage::where('id', $imageId)->update(['order' => $index]);
        }

        return response()->json(['success' => true, 'message' => 'Thứ tự ảnh đã được cập nhật!']);
    }
}
