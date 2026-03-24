<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Coupon;

class CouponController extends Controller
{
    public function apply(Request $request)
    {
        $request->validate([
            'code' => 'required|string|max:50'
        ]);
        
        $code = strtoupper(trim($request->code));
        $coupon = Coupon::where('code', $code)->first();
        
        if (!$coupon) {
            return back()->with('error', 'Mã giảm giá không tồn tại');
        }
        
        $userId = auth()->check() ? auth()->id() : null;
        if (!$coupon->isValid($userId)) {
            return back()->with('error', 'Mã giảm giá không hợp lệ, đã hết hạn hoặc đã dùng hết lượt');
        }
        
        $cart = session('cart', []);
        if (empty($cart)) {
            return back()->with('error', 'Giỏ hàng trống');
        }

        $total = array_sum(array_map(fn($item) => $item['price'] * $item['quantity'], $cart));
        
        if ($total < $coupon->min_order) {
            return back()->with('error', 'Đơn hàng chưa đủ giá trị tối thiểu: ' . number_format($coupon->min_order) . 'đ');
        }
        
        session(['coupon' => [
            'id' => $coupon->id,
            'code' => $coupon->code,
            'discount' => $coupon->calculateDiscount($total)
        ]]);
        
        return back()->with('success', 'Áp dụng mã giảm giá thành công');
    }
    
    public function remove()
    {
        session()->forget('coupon');
        return back()->with('success', 'Đã xóa mã giảm giá');
    }
}
