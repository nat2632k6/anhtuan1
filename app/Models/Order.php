<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = [
        'user_id',
        'order_code',
        'customer_name',
        'customer_phone',
        'customer_email',
        'customer_address',
        'shipping_address',
        'total_amount',
        'shipping_fee',
        'discount',
        'status',
        'payment_method',
        'payment_status',
        'shipping_carrier',
        'tracking_code',
    ];

    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($order) {
            if (empty($order->order_code)) {
                $order->order_code = 'ORD' . date('Ymd') . strtoupper(substr(uniqid(), -6));
            }
        });
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    // Alias cho orderItems
    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
