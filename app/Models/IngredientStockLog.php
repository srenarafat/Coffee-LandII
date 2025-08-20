<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IngredientStockLog extends Model
{
    protected $fillable = [
        'ingredient_id',
        'type',
        'quantity',
        'unit',
        'note',
        'user_id',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
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