<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $fillable = [
    'shop_name',
    'discount_percent',
    'currency'
];

}
