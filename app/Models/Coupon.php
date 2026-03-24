<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Coupon extends Model
{
    protected $fillable = ['code', 'type', 'value', 'min_order', 'usage_limit', 'used_count', 'start_date', 'end_date', 'is_active', 'usage_per_user'];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'is_active' => 'boolean'
    ];

    public function isValid($userId = null)
    {
        if (!$this->is_active) {
            return false;
        }

        if (!now()->between($this->start_date, $this->end_date)) {
            return false;
        }

        if ($this->usage_limit !== null && $this->used_count >= $this->usage_limit) {
            return false;
        }

        if ($userId && $this->usage_per_user) {
            $userUsageCount = \DB::table('orders')
                ->where('user_id', $userId)
                ->whereRaw("JSON_CONTAINS(discount_details, JSON_OBJECT('coupon_id', ?))", [$this->id])
                ->count();
            
            if ($userUsageCount >= $this->usage_per_user) {
                return false;
            }
        }

        return true;
    }

    public function calculateDiscount($orderTotal)
    {
        if ($orderTotal < $this->min_order) {
            return 0;
        }

        if ($this->type === 'fixed') {
            return min($this->value, $orderTotal);
        }

        return ($orderTotal * $this->value) / 100;
    }
}
