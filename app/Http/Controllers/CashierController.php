<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Sale;

class CashierController extends Controller
{
    public function dashboard()
    {
        $productCount = Product::count();
        $productCount = Product::where('shop_id', auth()->user()->shop_id)->count();
        $salesTotal = Sale::where('user_id', auth()->id())->sum('total');
        $invoiceCount = Sale::where('user_id', auth()->id())->count();

        return view('cashier.dashboard', compact('productCount', 'salesTotal', 'invoiceCount'));
    }
}
