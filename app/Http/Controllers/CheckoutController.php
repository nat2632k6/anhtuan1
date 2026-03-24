<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Address;
use App\Models\Coupon;
use App\Models\Notification;
use App\Models\CartItem;
use App\Services\ShippingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\DB;

class CheckoutController extends Controller
{
    public function index()
    {
        $cart = $this->getCart();
        
        if (empty($cart)) {
            return redirect()->route('cart.index')->with('error', 'Giỏ hàng trống!');
        }
        
        $total = 0;
        foreach ($cart as $item) {
            $total += $item['price'] * $item['quantity'];
        }
        
        $user = auth()->user();
        $addresses = auth()->check() ? auth()->user()->addresses : collect();
        $shippingService = new ShippingService();
        
        return view('checkout', compact('cart', 'total', 'user', 'addresses', 'shippingService'));
    }

    public function process(Request $request)
    {
        $request->validate([
            'name' => 'required|max:255',
            'email' => 'required|email',
            'phone' => 'required|regex:/^[0-9]{10,11}$/',
            'address' => 'required|max:500',
            'payment_method' => 'required|in:cod,bank_transfer'
        ]);

        $cart = $this->getCart();
        
        if (empty($cart)) {
            return redirect()->route('cart.index')->with('error', 'Giỏ hàng trống!');
        }

        try {
            $order = DB::transaction(function () use ($request, $cart) {
                $productIds = array_map(function($item) { return $item['product_id']; }, $cart);
                $products = Product::whereIn('id', $productIds)->lockForUpdate()->get();
                
                foreach ($cart as $item) {
                    $product = $products->find($item['product_id']);
                    if (!$product || $product->stock < $item['quantity']) {
                        throw new \Exception("Sản phẩm {$item['name']} không đủ số lượng trong kho!");
                    }
                }

                $total = 0;
                foreach ($cart as $item) {
                    $total += $item['price'] * $item['quantity'];
                }
                
                $discount = 0;
                $couponId = null;
                if (session('coupon')) {
                    $couponData = session('coupon');
                    $coupon = Coupon::find($couponData['id']);
                    if ($coupon && $coupon->isValid()) {
                        $discount = $coupon->calculateDiscount($total);
                        $couponId = $coupon->id;
                    } else {
                        session()->forget('coupon');
                    }
                }

                $shippingService = new ShippingService();
                $shippingFee = $shippingService->calculateShippingFee($request->address);

                $order = Order::create([
                    'user_id' => auth()->id(),
                    'order_code' => 'ORD' . date('Ymd') . strtoupper(substr(uniqid(), -6)),
                    'customer_name' => $request->name,
                    'customer_email' => $request->email,
                    'customer_phone' => $request->phone,
                    'shipping_address' => $request->address,
                    'payment_method' => $request->payment_method,
                    'payment_status' => 'unpaid',
                    'shipping_fee' => $shippingFee,
                    'total_amount' => $total + $shippingFee - $discount,
                    'discount' => $discount,
                    'status' => 'pending'
                ]);

                foreach ($cart as $productId => $item) {
                    OrderItem::create([
                        'order_id' => $order->id,
                        'product_id' => $item['product_id'],
                        'product_name' => $item['name'],
                        'quantity' => $item['quantity'],
                        'price' => $item['price'],
                        'color' => $item['color'],
                        'size' => $item['size']
                    ]);
                    
                    $product = $products->find($item['product_id']);
                    $product->decrement('stock', $item['quantity']);
                }
                
                if ($couponId) {
                    Coupon::find($couponId)->increment('used_count');
                    session()->forget('coupon');
                }

                if ($request->save_address && auth()->check()) {
                    Address::create([
                        'user_id' => auth()->id(),
                        'label' => $request->address_label ?? 'Địa chỉ mới',
                        'phone' => $request->phone,
                        'address' => $request->address,
                        'is_default' => $request->set_default ? true : false
                    ]);
                    
                    if ($request->set_default) {
                        Address::where('user_id', auth()->id())
                            ->where('id', '!=', Address::latest()->first()->id)
                            ->update(['is_default' => false]);
                    }
                }

                if (auth()->check()) {
                    CartItem::where('user_id', auth()->id())->delete();
                }
                Session::forget('cart');
                
                if (auth()->check()) {
                    Notification::create([
                        'user_id' => auth()->id(),
                        'type' => 'order',
                        'title' => 'Đơn hàng đã được tạo thành công',
                        'message' => 'Đơn hàng ' . $order->order_code . ' của bạn đã được tạo thành công. Tổng giá trị: ' . number_format($order->total_amount) . 'đ',
                        'link' => route('my-orders.show', $order->id),
                        'is_read' => false
                    ]);
                }

                return $order;
            });

            return redirect()->route('checkout.success', $order->id);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function success($orderId)
    {
        $order = Order::with('orderItems')->findOrFail($orderId);
        return view('checkout-success', compact('order'));
    }
    
    private function getCart()
    {
        if (auth()->check()) {
            $cartItems = CartItem::where('user_id', auth()->id())
                                ->with('product')
                                ->get();
            
            $cart = [];
            foreach ($cartItems as $item) {
                $key = "{$item->product_id}_{$item->color}_{$item->size}";
                $cart[$key] = [
                    'product_id' => $item->product_id,
                    'name' => $item->product->name,
                    'price' => $item->product->price,
                    'image' => $item->product->image,
                    'quantity' => $item->quantity,
                    'color' => $item->color,
                    'size' => $item->size
                ];
            }
            return $cart;
        }
        
        return Session::get('cart', []);
    }
}
