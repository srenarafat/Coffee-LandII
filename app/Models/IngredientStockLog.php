<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IngredientStockLog extends Model
{
    protected $fillable = [
        'ingredient_id',
        'type',
        'quantity',
        'stock_after',
        'unit',
        'note',
        'user_id',
    ];

    protected $casts = [
        'quantity'    => 'float',
        'stock_after' => 'float',
    ];

    public function ingredient()
    {
        return $this->belongsTo(Ingredient::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}