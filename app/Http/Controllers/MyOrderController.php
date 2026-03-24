<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;

class MyOrderController extends Controller
{
    public function index()
    {
        $orders = Order::where('user_id', auth()->id())
                      ->with('orderItems')
                      ->orderBy('created_at', 'desc')
                      ->paginate(10);
        
        return view('my-orders', compact('orders'));
    }

    public function show($id)
    {
        $order = Order::where('user_id', auth()->id())
                     ->where('id', $id)
                     ->with('orderItems')
                     ->firstOrFail();
        
        return view('order-detail', compact('order'));
    }

    public function cancel($id)
    {
        $order = Order::where('user_id', auth()->id())
                     ->where('id', $id)
                     ->firstOrFail();
        
        if ($order->status !== 'pending') {
            return back()->with('error', 'Chỉ có thể hủy đơn hàng đang chờ xác nhận!');
        }
        
        $order->status = 'cancelled';
        $order->save();
        
        return back()->with('success', 'Đã hủy đơn hàng thành công!');
    }
}
