<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    /**
     * Mass assignable attributes.
     */
    protected $fillable = ['name', 'parent_id', 'is_active'];

    /**
     * Parent category relationship.
     */
    public function parent()
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    /**
     * Children categories relationship.
     */
    public function children()
    {
        return $this->hasMany(self::class, 'parent_id');
    }
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}

