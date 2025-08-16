<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
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
        'notes',
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
        $firstSale = $this->first_sale_at ?? $this->sales()->min('created_at');
        $lastSale = $this->last_sale_at ?? $this->sales()->max('created_at');

        $category = 'returning';

        if ($lastSale && Carbon::parse($lastSale)->diffInDays(Carbon::now()) >= 30) {
            $category = 'at-risk';
        } elseif ($firstSale && Carbon::parse($firstSale)->isSameDay(Carbon::now())) {
            $category = 'new';
        }

        return [
            'category' => $category,
            'first_sale_at' => $firstSale,
            'last_sale_at' => $lastSale,
        ];
    }
}

