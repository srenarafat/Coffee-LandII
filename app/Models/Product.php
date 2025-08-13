<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'price', 'category_id', 'image', 'shop_id', 'stock', 'promotion_flag'];

    protected $casts = [
        'promotion_flag' => 'boolean',
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
}

