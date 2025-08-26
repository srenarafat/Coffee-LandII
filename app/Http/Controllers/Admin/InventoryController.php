<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Setting;

class InventoryController extends Controller
{
    public function lowStock()
    {
        $threshold = Setting::value('low_stock_threshold') ?? 3;
        $products = Product::where('stock', '<=', $threshold)->with('category')->get();

        return view('admin.stock.low', compact('products', 'threshold'));
    }
}