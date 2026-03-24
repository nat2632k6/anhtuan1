<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Notification;
use App\Models\Product;
use Illuminate\Http\Request;

class AdminOrderController extends Controller
{
    public function index(Request $request)
    {
        $query = Order::with(['user', 'items.product']);
        
        if ($request->search && !empty($request->search)) {
            $search = addslashes($request->search);
            $query->where('order_code', 'like', "%{$search}%")
                  ->orWhere('customer_name', 'like', "%{$search}%")
                  ->orWhere('customer_phone', 'like', "%{$search}%");
        }
        
        if ($request->status && in_array($request->status, ['pending', 'confirmed', 'shipping', 'delivered', 'delivery_failed', 'cancelled'])) {
            $query->where('status', $request->status);
        }
        
        if ($request->date) {
            $query->whereDate('created_at', $request->date);
        }
        
        $orders = $query->latest()->paginate(20);
        
        return view('admin.orders.index', compact('orders'));
    }

    public function show(Order $order)
    {
        $order->load(['user', 'items.product']);
        return view('admin.orders.show', compact('order'));
    }

    public function updateStatus(Request $request, Order $order)
    {
        $request->validate([
            'action' => 'required|string|in:next,cancel,delivered,delivery_failed',
            'payment_status' => 'nullable|in:unpaid,paid',
            'shipping_carrier' => 'nullable|string|max:100',
            'tracking_code' => 'nullable|string|max:100'
        ]);
        
        $action = $request->input('action');
        $oldStatus = $order->status;
        $newStatus = null;
        
        $workflow = ['pending' => 'confirmed', 'confirmed' => 'shipping'];
        $shippingActions = ['delivered' => 'completed', 'delivery_failed' => 'delivery_failed'];
        
        if ($action === 'next' && isset($workflow[$order->status])) {
            $newStatus = $workflow[$order->status];
        } elseif ($order->status === 'shipping' && isset($shippingActions[$action])) {
            $newStatus = $shippingActions[$action];
        } elseif ($action === 'cancel' && in_array($order->status, ['pending', 'confirmed'])) {
            $newStatus = 'cancelled';
        } else {
            return back()->with('error', 'Hành động không hợp lệ!');
        }
        
        $updateData = ['status' => $newStatus];
        if ($request->payment_status) {
            $updateData['payment_status'] = $request->payment_status;
        }
        if ($request->shipping_carrier) {
            $updateData['shipping_carrier'] = $request->shipping_carrier;
        }
        if ($request->tracking_code) {
            $updateData['tracking_code'] = $request->tracking_code;
        }
        
        $order->update($updateData);
        
        if ($order->user_id) {
            $statusMessages = [
                'confirmed' => 'Đơn hàng của bạn đã được xác nhận',
                'shipping' => 'Đơn hàng của bạn đang được giao',
                'completed' => 'Đơn hàng của bạn đã hoàn thành',
                'delivery_failed' => 'Giao hàng thất bại. Vui lòng liên hệ với chúng tôi',
                'cancelled' => 'Đơn hàng của bạn đã bị hủy'
            ];
            
            if (isset($statusMessages[$newStatus])) {
                Notification::create([
                    'user_id' => $order->user_id,
                    'type' => 'order',
                    'title' => 'Cập nhật đơn hàng ' . $order->order_code,
                    'message' => $statusMessages[$newStatus],
                    'link' => route('my-orders.show', $order->id),
                    'is_read' => false
                ]);
            }
        }
        
        return back()->with('success', 'Cập nhật thành công!');
    }

    public function print(Order $order)
    {
        $order->load(['user', 'items.product']);
        return view('admin.orders.print', compact('order'));
    }

    public function export(Request $request)
    {
        $request->validate([
            'status' => 'nullable|in:pending,confirmed,shipping,delivered,delivery_failed,cancelled',
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date|after_or_equal:date_from'
        ]);

        $query = Order::with(['user', 'items.product']);
        
        if ($request->status) {
            $query->where('status', $request->status);
        }
        
        if ($request->date_from) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        
        if ($request->date_to) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }
        
        $orders = $query->latest()->get();
        
        $filename = 'orders_' . date('YmdHis') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => "attachment; filename=$filename",
        ];
        
        $callback = function() use ($orders) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            fputcsv($file, ['Mã đơn', 'Khách hàng', 'SĐT', 'Email', 'Địa chỉ', 'Tổng tiền', 'Thanh toán', 'Trạng thái', 'Ngày đặt']);
            
            foreach ($orders as $order) {
                fputcsv($file, [
                    $order->order_code ?? 'ORD' . str_pad($order->id, 6, '0', STR_PAD_LEFT),
                    $order->customer_name,
                    $order->customer_phone,
                    $order->customer_email,
                    $order->shipping_address,
                    $order->total_amount,
                    $order->payment_method,
                    $order->status,
                    $order->created_at->format('d/m/Y H:i')
                ]);
            }
            
            fclose($file);
        };
        
        return response()->stream($callback, 200, $headers);
    }

    public function destroy(Order $order)
    {
        try {
            if ($order->status !== 'completed') {
                foreach ($order->orderItems as $item) {
                    $product = Product::find($item->product_id);
                    if ($product) {
                        $product->increment('stock', $item->quantity);
                    }
                }
            }
            
            if ($order->user_id) {
                Notification::create([
                    'user_id' => $order->user_id,
                    'type' => 'order',
                    'title' => 'Đơn hàng ' . $order->order_code . ' đã bị hủy',
                    'message' => 'Đơn hàng của bạn đã bị hủy bởi quản trị viên. Tổng tiền hoàn lại: ' . number_format($order->total_amount) . 'đ',
                    'link' => route('my-orders.show', $order->id),
                    'is_read' => false
                ]);
            }
            
            $order->delete();
            
            return back()->with('success', 'Đã xóa đơn hàng!');
        } catch (\Exception $e) {
            return back()->with('error', 'Lỗi khi xóa đơn hàng: ' . $e->getMessage());
        }
    }
}
