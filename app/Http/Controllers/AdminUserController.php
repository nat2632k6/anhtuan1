<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Order;
use Illuminate\Http\Request;

class AdminUserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::where('role', '!=', 'admin')->orWhereNull('role')->withCount('orders');
        
        if ($request->search) {
            $query->where(function($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                  ->orWhere('email', 'like', "%{$request->search}%")
                  ->orWhere('phone', 'like', "%{$request->search}%");
            });
        }
        
        if ($request->sort) {
            if ($request->sort == 'spending') {
                $query->withSum(['orders as total_spent' => function($q) {
                    $q->where('status', '!=', 'cancelled');
                }], 'total_amount')
                ->orderBy('total_spent', 'desc');
            }
        } else {
            $query->latest();
        }
        
        $users = $query->paginate(20);
        
        $totalCustomers = User::where('role', '!=', 'admin')->orWhereNull('role')->count();
        $activeCustomers = User::where('role', '!=', 'admin')->orWhereNull('role')->whereHas('orders', function($q) {
            $q->whereIn('status', ['completed', 'delivered']);
        })->count();
        
        return view('admin.users.index', compact('users', 'totalCustomers', 'activeCustomers'));
    }

    public function show(User $user)
    {
        $user->load('orders.orderItems');
        $totalSpent = $user->orders()->where('status', '!=', 'cancelled')->sum('total_amount');
        
        return view('admin.users.show', compact('user', 'totalSpent'));
    }
}
