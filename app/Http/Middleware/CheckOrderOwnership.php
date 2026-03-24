<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckOrderOwnership
{
    public function handle(Request $request, Closure $next): Response
    {
        $order = $request->route('order') ?? $request->route('id');
        
        if ($order && auth()->check()) {
            if ($order->user_id !== auth()->id() && !auth()->user()->isAdmin()) {
                return redirect()->route('home')->with('error', 'Bạn không có quyền truy cập đơn hàng này!');
            }
        }

        return $next($request);
    }
}
