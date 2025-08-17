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
    protected $fillable = ['name', 'parent_id', 'shop_id'];

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
            ->with(['childrenRecursive' => fn ($q) => $q->orderBy('name')]);
    }
    
    public function shop()
    {
        return $this->belongsTo(Shop::class);
    }
}

