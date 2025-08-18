<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'price', 'category_id', 'image', 'stock',
        'promotion_flag', 'is_active',
    ];

    protected $casts = [
        'promotion_flag' => 'boolean',
        'is_active'      => 'boolean',
    ];

    /* ---------------- Relationships ---------------- */

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function saleItems()
    {
        return $this->hasMany(SaleItem::class);
    }

    /* ---------------- Scopes & Helpers ---------------- */

    /** Only products explicitly marked active */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Products that can be shown in the POS grid/search.
     * UI-friendly filter: category must be active and (if it has a parent) the parent is active.
     * (For deeper trees, server-side guard `isSellable()` is authoritative.)
     */
    public function scopeSellable($query)
    {
        return $query->active()->whereHas('category', function ($q) {
            $q->where('is_active', 1)
              ->where(function ($q) {
                  $q->whereNull('parent_id')
                    ->orWhereHas('parent', fn($p) => $p->where('is_active', 1));
              });
        });
    }

    /**
     * Authoritative check used on add-to-cart/checkout:
     * true only if the product’s category AND all ancestors are active.
     */
    public function isSellable(): bool
    {
        // Pull a couple ancestor levels; recursion in Category will handle the rest.
        $category = $this->relationLoaded('category')
            ? $this->category
            : $this->category()->with('parent.parent')->first();

        return $category ? $category->isTreeActive() : false;
    }
}
