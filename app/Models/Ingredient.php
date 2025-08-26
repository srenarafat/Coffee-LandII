<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ingredient extends Model
{
    protected $fillable = [
        'name',
        'unit',
        'stock',
        // 'shop_id', // uncomment if you use multi-tenant shops
    ];

    protected $casts = [
        'stock' => 'float',
    ];

    protected $attributes = [
        'stock' => 0,
    ];

    /**
     * Relationship: all stock movement logs for this ingredient.
     */
    public function stockLogs()
    {
        return $this->hasMany(IngredientStockLog::class, 'ingredient_id');
    }

    /**
     * Scope: only ingredients at or below a threshold.
     *
     * Usage:
     *   Ingredient::lowStock($threshold)->get();
     */
    public function scopeLowStock($query, $threshold)
    {
        return $query->where('stock', '<=', (float) $threshold);
    }

    /**
     * Helper: compute status label for UI badges.
     * Returns: 'out' | 'low' | 'ok'
     */
    public function statusFor(float $threshold): string
    {
        $s = (float) $this->stock;
        if ($s <= 0) return 'out';
        if ($s <= $threshold) return 'low';
        return 'ok';
    }

    /**
     * Guardrail: never persist negative stock.
     * (Controller already checks, this is just extra safety)
     */
    public function setStockAttribute($value): void
    {
        $this->attributes['stock'] = max(0, (float) $value);
    }
}
