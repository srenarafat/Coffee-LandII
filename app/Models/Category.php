<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    /** Mass assignment */
    protected $fillable = ['shop_id', 'name', 'parent_id', 'is_active'];

    /** Casts */
    protected $casts = [
        'is_active' => 'boolean',
    ];

    /* ---------------- Relationships ---------------- */

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
            ->orderByDesc('created_at')
            ->with(['childrenRecursive' => function ($q) {
                $q->orderByDesc('created_at');
            }]);
    }

    public function shop()
    {
        return $this->belongsTo(Shop::class);
    }

    /* ---------------- Scopes & Helpers ---------------- */

    /** Only active categories (single node) */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /** Category is active AND all ancestors are active */
    public function isTreeActive(): bool
    {
        if (!$this->is_active) {
            return false;
        }

        // Avoid N+1: use loaded relation if present, otherwise fetch one hop.
        $parent = $this->relationLoaded('parent')
            ? $this->parent
            : $this->parent()->first();

        return !$parent || $parent->isTreeActive();
    }
    
    /**
     * Return the given category id plus all descendant ids.
     */
    public static function descendantsAndSelfIds(int $id): array
    {
        $ids = [];
        $queue = [$id];

        while ($queue) {
            $current = array_shift($queue);
            $ids[]   = $current;

            $children = self::where('parent_id', $current)->pluck('id');
            foreach ($children as $childId) {
                $queue[] = $childId;
            }
        }

        return $ids;
    }
}
