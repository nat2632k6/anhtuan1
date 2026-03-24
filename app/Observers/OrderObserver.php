<?php

namespace App\Observers;

use App\Models\Order;
use App\Models\Product;

class OrderObserver
{
    public function updating(Order $order)
    {
        // Kiểm tra nếu trạng thái thay đổi sang "cancelled"
        if ($order->isDirty('status') && $order->status === 'cancelled') {
            // Hoàn lại số lượng sản phẩm vào kho
            foreach ($order->orderItems as $item) {
                $product = Product::find($item->product_id);
                if ($product) {
                    $product->increment('stock', $item->quantity);
                }
            }
        }
    }
}
