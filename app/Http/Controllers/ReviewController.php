<?php

namespace App\Http\Controllers;

use App\Models\Review;
use App\Models\Product;
use Illuminate\Http\Request;
use App\Helpers\SecurityHelper;

class ReviewController extends Controller
{
    public function store(Request $request, Product $product)
    {
        $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:500'
        ]);

        try {
            $existingReview = Review::where('user_id', auth()->id())
                ->where('product_id', $product->id)
                ->first();

            if ($existingReview) {
                return back()->with('error', 'Bạn đã đánh giá sản phẩm này rồi!');
            }

            Review::create([
                'user_id' => auth()->id(),
                'product_id' => $product->id,
                'rating' => (int)$request->rating,
                'comment' => $request->comment ? SecurityHelper::sanitizeWithTags($request->comment) : null
            ]);

            return back()->with('success', 'Cảm ơn bạn đã đánh giá!');
        } catch (\Exception $e) {
            return back()->with('error', 'Lỗi khi lưu đánh giá: ' . $e->getMessage());
        }
    }

    public function destroy(Review $review)
    {
        if ($review->user_id !== auth()->id() && !auth()->user()->isAdmin()) {
            return back()->with('error', 'Không có quyền xóa đánh giá này!');
        }

        try {
            $review->delete();
            return back()->with('success', 'Đã xóa đánh giá!');
        } catch (\Exception $e) {
            return back()->with('error', 'Lỗi khi xóa: ' . $e->getMessage());
        }
    }
}
