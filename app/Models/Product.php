<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'price', 'category_id', 'image', 'shop_id', 'stock', 'promotion_flag', 'is_active'];

    protected $casts = [
        'promotion_flag' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

        public function saleItems()
    {
        return $this->hasMany(SaleItem::class);
    }

    public function shop()
    {
        return $this->belongsTo(Shop::class);
    }
    
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}

