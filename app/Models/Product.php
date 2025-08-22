<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

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

    public function logs(): HasMany
    {
        return $this->hasMany(StockLog::class);
    }

    /* ---------------- Scopes & Helpers ---------------- */

    /** Only products explicitly marked active */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Products that can be shown in the POS grid/search.
     * Category must be active and have no inactive ancestors.
     */
    public function scopeSellable($query)
    {
        $categories = (new Category())->getTable();

        return $query->active()->whereIn('category_id', function ($sub) use ($categories) {
            $sub->select('id')->fromRaw("(
                WITH RECURSIVE ancestors AS (
                    SELECT id, parent_id, is_active, id AS root_id FROM {$categories}
                    UNION ALL
                    SELECT c.id, c.parent_id, c.is_active, a.root_id
                    FROM {$categories} c
                    JOIN ancestors a ON c.id = a.parent_id
                )
                SELECT root_id AS id FROM ancestors
                GROUP BY root_id
                HAVING MIN(is_active) = 1
            ) AS active_categories");
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
