<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    // include shop_id if you set it via mass assignment
    protected $fillable = ['shop_id', 'name', 'parent_id', 'is_active'];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function parent()
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    public function childrenRecursive()
    {
        return $this->children()
            ->orderBy('name')
            ->with(['childrenRecursive' => function ($q) {
                $q->orderBy('name');
            }]);
    }

    public function shop()
    {
        return $this->belongsTo(Shop::class);
    }

    /** Query scopes */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /** Optional: active only if all ancestors are active */
    public function isTreeActive(): bool
    {
        return (bool) $this->is_active && (!$this->parent || $this->parent->isTreeActive());
    }
}
