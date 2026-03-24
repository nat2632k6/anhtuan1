<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Banner extends Model
{
    protected $fillable = [
        'category_id',
        'title',
        'description',
        'image',
        'link',
        'order',
        'is_active'
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }
}
