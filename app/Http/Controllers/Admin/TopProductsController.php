<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SaleItem;

class TopProductsController extends Controller
{
    public function week()
    {
        $topProducts = SaleItem::selectRaw('product_id, SUM(quantity) as qty, SUM(total) as revenue')
            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->whereBetween('sales.created_at', [now()->subDays(6)->startOfDay(), now()->endOfDay()])
            ->with('product.category')
            ->groupBy('product_id')
            ->orderByDesc('qty')
            ->limit(10)
            ->get();

        return view('admin.reports.top-products-week', compact('topProducts'));
    }
}
