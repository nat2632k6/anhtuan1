<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminRevenueController extends Controller
{
    public function index(Request $request)
    {
        $period = $request->get('period', 'week');
        
        $revenueData = $this->getRevenueByPeriod($period);
        
        $topProducts = DB::table('order_items')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->where('orders.status', 'completed')
            ->where('orders.payment_status', 'paid')
            ->select('order_items.product_name', DB::raw('SUM(order_items.quantity) as total_sold'), DB::raw('SUM(order_items.price * order_items.quantity) as revenue'))
            ->groupBy('order_items.product_name')
            ->orderBy('total_sold', 'desc')
            ->take(10)
            ->get();
        
        $totalRevenue = Order::where('status', 'completed')
            ->where('payment_status', 'paid')
            ->sum('total_amount');
        $totalOrders = Order::where('status', 'completed')
            ->where('payment_status', 'paid')
            ->count();
        $avgOrderValue = $totalOrders > 0 ? $totalRevenue / $totalOrders : 0;
        
        return view('admin.revenue.index', compact('revenueData', 'topProducts', 'totalRevenue', 'totalOrders', 'avgOrderValue', 'period'));
    }
    
    public function export(Request $request)
    {
        $period = $request->get('period', 'week');
        $revenueData = $this->getRevenueByPeriod($period);
        
        $filename = 'revenue_report_' . date('YmdHis') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=$filename",
        ];
        
        $callback = function() use ($revenueData, $period) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            fputcsv($file, ['Thời gian', 'Doanh thu', 'Số đơn']);
            
            foreach ($revenueData as $data) {
                fputcsv($file, [
                    $data->period,
                    $data->revenue,
                    $data->orders
                ]);
            }
            
            fclose($file);
        };
        
        return response()->stream($callback, 200, $headers);
    }
    
    private function getRevenueByPeriod($period)
    {
        $query = Order::where('status', 'completed')
            ->where('payment_status', 'paid');
        
        switch ($period) {
            case 'day':
                $query->where('created_at', '>=', now()->subDays(7));
                return $query->select(DB::raw('DATE(created_at) as period'), DB::raw('SUM(total_amount) as revenue'), DB::raw('COUNT(*) as orders'))
                    ->groupBy(DB::raw('DATE(created_at)'))
                    ->orderBy('period')
                    ->get();
            case 'week':
                $query->where('created_at', '>=', now()->subWeeks(8));
                return $query->select(DB::raw('YEARWEEK(created_at) as period'), DB::raw('SUM(total_amount) as revenue'), DB::raw('COUNT(*) as orders'))
                    ->groupBy(DB::raw('YEARWEEK(created_at)'))
                    ->orderBy('period')
                    ->get();
            case 'month':
                $query->where('created_at', '>=', now()->subMonths(12));
                return $query->select(DB::raw('DATE_FORMAT(created_at, "%Y-%m") as period'), DB::raw('SUM(total_amount) as revenue'), DB::raw('COUNT(*) as orders'))
                    ->groupBy(DB::raw('DATE_FORMAT(created_at, "%Y-%m")'))
                    ->orderBy('period')
                    ->get();
            case 'year':
                return $query->select(DB::raw('YEAR(created_at) as period'), DB::raw('SUM(total_amount) as revenue'), DB::raw('COUNT(*) as orders'))
                    ->groupBy(DB::raw('YEAR(created_at)'))
                    ->orderBy('period')
                    ->get();
            default:
                $query->where('created_at', '>=', now()->subDays(7));
                return $query->select(DB::raw('DATE(created_at) as period'), DB::raw('SUM(total_amount) as revenue'), DB::raw('COUNT(*) as orders'))
                    ->groupBy(DB::raw('DATE(created_at)'))
                    ->orderBy('period')
                    ->get();
        }
    }
}
