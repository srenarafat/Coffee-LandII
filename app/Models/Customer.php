<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    use HasFactory;
    protected $fillable = [
        'shop_id',
        'name',
        'email',
        'phone',
        'address',
    ];

    public function sales()
    {
        return $this->hasMany(Sale::class);
    }

    public function shop()
    {
        return $this->belongsTo(Shop::class);
    }
    
    public function classifyByRecency(): array
    {
        $today = Carbon::today();
        $sales = $this->relationLoaded('sales')
            ? $this->sales
            : $this->sales()->orderBy('created_at')->get();

        $firstSale = $sales->first()?->created_at;
        $lastSale = $sales->last()?->created_at;
        $category = null;

        if ($lastSale) {
            if ($lastSale->isToday()) {
                $category = $firstSale && $firstSale->isToday() ? 'new' : 'returning';
            } else {
                $category = $lastSale->diffInDays($today) > 30 ? 'at-risk' : 'returning';
            }
        }

        return [
            'first_sale' => $firstSale,
            'last_sale' => $lastSale,
            'category' => $category,
        ];
    }
}
