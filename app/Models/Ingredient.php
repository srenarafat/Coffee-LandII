<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ingredient extends Model
{
    protected $fillable = [
        'name',
        'unit',
        'stock',
    ];

    protected $casts = [
        'stock' => 'float',
    ];

    public function stockLogs()
    {
        return $this->hasMany(IngredientStockLog::class);
    }
}