<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\CartItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class CartController extends Controller
{
    public function index()
    {
        $cart = $this->getCart();
        $total = 0;
        
        foreach ($cart as $item) {
            $total += $item['price'] * $item['quantity'];
        }
        
        return view('cart', compact('cart', 'total'));
    }

    public function add(Request $request)
    {
        $request->validate([
            'product_id' => 'required|integer|exists:products,id',
            'quantity' => 'required|integer|min:1|max:100',
            'color' => 'nullable|string',
            'size' => 'nullable|string'
        ]);

        $productId = $request->product_id;
        $quantity = $request->quantity;
        $color = $request->color ?? 'default';
        $size = $request->size ?? 'default';
        
        $product = Product::find($productId);
        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => 'Sản phẩm không tồn tại!'
            ], 404);
        }
        
        if (auth()->check()) {
            $cartItem = CartItem::where('user_id', auth()->id())
                                ->where('product_id', $productId)
                                ->where('color', $color)
                                ->where('size', $size)
                                ->first();
            
            $newQty = ($cartItem ? $cartItem->quantity : 0) + $quantity;
            
            if ($newQty > $product->stock) {
                return response()->json([
                    'success' => false,
                    'message' => "Chỉ còn {$product->stock} sản phẩm trong kho!"
                ], 400);
            }
            
            if ($cartItem) {
                $cartItem->update(['quantity' => $newQty]);
            } else {
                CartItem::create([
                    'user_id' => auth()->id(),
                    'product_id' => $productId,
                    'quantity' => $quantity,
                    'color' => $color,
                    'size' => $size
                ]);
            }
            
            $totalItems = CartItem::where('user_id', auth()->id())->sum('quantity');
        } else {
            $cart = Session::get('cart', []);
            $cartKey = "{$productId}_{$color}_{$size}";
            $currentQty = isset($cart[$cartKey]) ? $cart[$cartKey]['quantity'] : 0;
            $newQty = $currentQty + $quantity;
            
            if ($newQty > $product->stock) {
                return response()->json([
                    'success' => false,
                    'message' => "Chỉ còn {$product->stock} sản phẩm trong kho!"
                ], 400);
            }
            
            if (isset($cart[$cartKey])) {
                $cart[$cartKey]['quantity'] = $newQty;
            } else {
                $cart[$cartKey] = [
                    'product_id' => $productId,
                    'name' => $product->name,
                    'price' => $product->price,
                    'image' => $product->image,
                    'quantity' => $quantity,
                    'color' => $color,
                    'size' => $size
                ];
            }
            
            Session::put('cart', $cart);
            $totalItems = array_sum(array_column($cart, 'quantity'));
        }
        
        return response()->json([
            'success' => true,
            'message' => 'Đã thêm vào giỏ hàng!',
            'cart_count' => $totalItems
        ]);
    }

    public function remove($id)
    {
        $id = (int)$id;
        
        if (auth()->check()) {
            CartItem::where('user_id', auth()->id())
                    ->where('product_id', $id)
                    ->delete();
        } else {
            $cart = Session::get('cart', []);
            foreach ($cart as $key => $item) {
                if ($item['product_id'] == $id) {
                    unset($cart[$key]);
                    break;
                }
            }
            Session::put('cart', $cart);
        }
        
        return redirect()->back()->with('success', 'Đã xóa sản phẩm khỏi giỏ hàng!');
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'quantity' => 'required|integer|min:1|max:100'
        ]);

        $id = (int)$id;
        $quantity = (int)$request->quantity;
        
        if (auth()->check()) {
            $cartItem = CartItem::where('user_id', auth()->id())
                                ->where('product_id', $id)
                                ->first();
            
            if (!$cartItem) {
                return redirect()->back()->with('error', 'Sản phẩm không có trong giỏ hàng!');
            }

            $product = Product::find($id);
            
            if ($product && $quantity > $product->stock) {
                return redirect()->back()->with('error', "Chỉ còn {$product->stock} sản phẩm trong kho!");
            }
            
            if ($quantity > 0) {
                $cartItem->update(['quantity' => $quantity]);
            } else {
                $cartItem->delete();
            }
        } else {
            $cart = Session::get('cart', []);
            
            foreach ($cart as $key => $item) {
                if ($item['product_id'] == $id) {
                    $product = Product::find($id);
                    
                    if ($product && $quantity > $product->stock) {
                        return redirect()->back()->with('error', "Chỉ còn {$product->stock} sản phẩm trong kho!");
                    }
                    
                    if ($quantity > 0) {
                        $cart[$key]['quantity'] = $quantity;
                    } else {
                        unset($cart[$key]);
                    }
                    break;
                }
            }
            Session::put('cart', $cart);
        }
        
        return redirect()->back()->with('success', 'Đã cập nhật giỏ hàng!');
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
