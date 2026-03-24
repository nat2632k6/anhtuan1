<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    protected $fillable = [
        'order_id',
        'product_id',
        'product_name',
        'quantity',
        'price',
        'color',
        'size',
    ];

    // OrderItem thuộc về Order
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    // OrderItem thuộc về Product
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
