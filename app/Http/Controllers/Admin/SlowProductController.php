<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\SaleItem;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class SlowProductController extends Controller
{
    public function index(): View
    {
        $cutoff = now()->subDays(30);

        $noRecentSales = Product::leftJoin('sale_items', function ($join) use ($cutoff) {
                $join->on('products.id', '=', 'sale_items.product_id')
                    ->where('sale_items.created_at', '>=', $cutoff);
            })
            ->whereNull('sale_items.id')
            ->select('products.*')
            ->with('category')
            ->get()
            ->map(function ($product) {
                $lastSale = $product->saleItems()->latest('created_at')->first();
                $product->last_sale_at = $lastSale? $lastSale->created_at: null;
                $product->days_since_last_sale = $product->last_sale_at ? $product->last_sale_at->diffInDays(now()) : null;
                return $product;
            });

        $bottom10 = SaleItem::leftJoin('products', 'sale_items.product_id', '=', 'products.id')
            ->select('products.id', DB::raw('SUM(sale_items.quantity) as total_quantity'), DB::raw('MAX(sale_items.created_at) as last_sale_at'))
            ->groupBy('products.id')
            ->orderBy('total_quantity')
            ->limit(10)
            ->get();

        $bottomProducts = Product::with('category')
            ->whereIn('id', $bottom10->pluck('id'))
            ->get()
            ->map(function ($product) use ($bottom10) {
                $match = $bottom10->firstWhere('id', $product->id);
                $product->last_sale_at = $match && $match->last_sale_at ? \Carbon\Carbon::parse($match->last_sale_at) : null;
                $product->days_since_last_sale = $product->last_sale_at ? $product->last_sale_at->diffInDays(now()) : null;
                return $product;
            });

        $products = $noRecentSales->merge($bottomProducts)->unique('id');

        return view('admin.reports.slow-products', ['products' => $products]);
    }

    public function promote(Product $product): RedirectResponse
    {
        $product->update(['promotion_flag' => true]);

        return back()->with('status', 'Product marked for promotion');
    }
}
