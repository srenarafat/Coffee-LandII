<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sale extends Model
{
    protected $fillable = [
        'user_id',
        'shop_id',
        'subtotal',
        'discount',
        'total',
        'invoice_no',
        'table_number',
        'payment_method',
        'cash_usd',
        'cash_riel',
        'change_usd',
        'change_riel',
        'exchange_rate',
    ];

    public function items()
    {
        return $this->hasMany(SaleItem::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    
    public function shop()
    {
        return $this->belongsTo(Shop::class);
    }
}
