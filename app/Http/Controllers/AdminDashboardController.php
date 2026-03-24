<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\User;
use App\Models\Product;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminDashboardController extends Controller
{
    public function index()
    {
        // Chỉ tính doanh thu từ đơn hàng đã hoàn thành VÀ đã thanh toán
        $totalRevenue = Order::where('status', 'completed')
            ->where('payment_status', 'paid')
            ->sum('total_amount');
        $totalOrders = Order::count();
        $totalCustomers = User::where('role', 'user')->count();
        if ($totalCustomers == 0) {
            $totalCustomers = User::count() - 1; // Trừ admin
        }
        $totalProducts = Product::count();
        
        // Doanh thu hôm nay (chỉ đơn hoàn thành VÀ đã thanh toán)
        $todayRevenue = Order::where('status', 'completed')
            ->where('payment_status', 'paid')
            ->whereDate('created_at', today())
            ->sum('total_amount');
        
        // Doanh thu tháng này (chỉ đơn hoàn thành VÀ đã thanh toán)
        $monthRevenue = Order::where('status', 'completed')
            ->where('payment_status', 'paid')
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->sum('total_amount');
        
        $pendingOrders = Order::where('status', 'pending')->count();
        $shippingOrders = Order::where('status', 'shipping')->count();
        
        $ordersByStatus = Order::select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->get()
            ->pluck('total', 'status');
        
        $recentOrders = Order::with('user')->latest()->take(5)->get();
        
        // Top sản phẩm bán chạy (chỉ tính đơn hàng hoàn thành VÀ đã thanh toán)
        $topProducts = OrderItem::join('orders', 'order_items.order_id', '=', 'orders.id')
            ->where('orders.status', 'completed')
            ->where('orders.payment_status', 'paid')
            ->select('order_items.product_id', 'order_items.product_name', DB::raw('SUM(order_items.quantity) as total_sold'))
            ->groupBy('order_items.product_id', 'order_items.product_name')
            ->orderBy('total_sold', 'desc')
            ->take(10)
            ->get();
        
        // Biểu đồ doanh thu 7 ngày (chỉ đơn hoàn thành VÀ đã thanh toán)
        $revenueChart = Order::where('status', 'completed')
            ->where('payment_status', 'paid')
            ->where('created_at', '>=', now()->subDays(7))
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('SUM(total_amount) as total'))
            ->groupBy('date')
            ->orderBy('date')
            ->get();
        
        return view('admin.dashboard', compact(
            'totalRevenue', 'totalOrders', 'totalCustomers', 'totalProducts',
            'todayRevenue', 'monthRevenue', 'pendingOrders', 'shippingOrders',
            'ordersByStatus', 'recentOrders', 'topProducts', 'revenueChart'
        ));
    }
}
