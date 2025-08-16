<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Product;
use App\Models\Setting;
use App\Models\Customer;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class CashierController extends Controller
{
    public function dashboard()
    {
        // Fetch latest 5 sales with item count
        $recentSales = Sale::with(['user'])
                           ->withCount('items')
                           ->where('shop_id', auth()->user()->shop_id)
                           ->latest()
                           ->take(9)
                           ->get();

        $startOfDay = Carbon::now()->startOfDay();
        $endOfDay = Carbon::now()->endOfDay();
        $todaySalesTotal = Sale::whereBetween('created_at', [$startOfDay, $endOfDay])
                               ->where('shop_id', auth()->user()->shop_id)
                               ->sum('total');
        $todayOrderCount = Sale::whereBetween('created_at', [$startOfDay, $endOfDay])
                               ->where('shop_id', auth()->user()->shop_id)
                               ->count();
        $todayItemsSold = SaleItem::whereBetween('created_at', [$startOfDay, $endOfDay])
                                   ->whereHas('sale', fn($q) => $q->where('shop_id', auth()->user()->shop_id))
                                   ->sum('quantity');
        $todayAverageOrderValue = $todayOrderCount ? $todaySalesTotal / $todayOrderCount : 0;

        $threshold = Setting::value('low_stock_threshold') ?? 5;
        $lowStockCount = Product::where('stock', '<=', $threshold)->count();

        // Calculate sales for the past 7 days
        $startDate = Carbon::now()->subDays(6)->startOfDay();
        $endDate = Carbon::now()->endOfDay();

        $sales = Sale::whereBetween('created_at', [$startDate, $endDate])
                      ->where('shop_id', auth()->user()->shop_id)
                      ->selectRaw('DATE(created_at) as date, SUM(total) as total')
                      ->groupBy('date')
                      ->orderBy('date')
                      ->get()
                      ->pluck('total', 'date');

        $chartLabels = [];
        $chartData = [];

        for ($date = $startDate->copy(); $date->lte($endDate); $date->addDay()) {
            $chartLabels[] = $date->format('l');
            $chartData[] = $sales[$date->format('Y-m-d')] ?? 0;
        }

        // Today's metrics
        $todayTotalSales = Sale::whereBetween('created_at', [$startOfDay, $endOfDay])
                               ->where('shop_id', auth()->user()->shop_id)
                               ->sum('total');
        $ordersToday = Sale::whereBetween('created_at', [$startOfDay, $endOfDay])
                            ->where('shop_id', auth()->user()->shop_id)
                            ->count();
        $itemsSoldToday = SaleItem::whereHas('sale', fn($q) => $q->whereBetween('created_at', [$startOfDay, $endOfDay])
                                                                   ->where('shop_id', auth()->user()->shop_id))
                                    ->sum('quantity');
        $avgOrderValue = $ordersToday > 0 ? $todayTotalSales / $ordersToday : 0;
        $threshold = Setting::value('low_stock_threshold') ?? 5;
        $lowStockCount = Product::where('stock', '<=', $threshold)->count();

        $weekAgo = Carbon::now()->subDays(7);
        $topProductsWeekCount = SaleItem::where('created_at', '>=', $weekAgo)
            ->whereHas('sale', fn($q) => $q->where('shop_id', auth()->user()->shop_id))
            ->select('product_id', DB::raw('SUM(quantity) as total_quantity'))
            ->groupBy('product_id')
            ->orderByDesc('total_quantity')
            ->limit(10)
            ->get()
            ->count();

        $cutoff = Carbon::now()->subDays(30);

        $noRecentSales = Product::where('shop_id', auth()->user()->shop_id)
            ->leftJoin('sale_items', function ($join) use ($cutoff) {
                $join->on('products.id', '=', 'sale_items.product_id')
                    ->where('sale_items.created_at', '>=', $cutoff);
            })
            ->whereNull('sale_items.id')
            ->pluck('products.id');

        $bottom10 = SaleItem::leftJoin('products', 'sale_items.product_id', '=', 'products.id')
            ->where('products.shop_id', auth()->user()->shop_id)
            ->select('products.id', DB::raw('SUM(sale_items.quantity) as total_quantity'))
            ->groupBy('products.id')
            ->orderBy('total_quantity')
            ->limit(10)
            ->pluck('products.id');

        $slowMoversCount = $noRecentSales->merge($bottom10)->unique()->count();
        
        // Customer metrics
        $shopId = auth()->user()->shop_id;
        $today = Carbon::today();

        $customers = Customer::where('shop_id', $shopId)
            ->whereHas('sales', fn($q) => $q->where('shop_id', $shopId))
            ->with(['sales' => fn($q) => $q->where('shop_id', $shopId)->orderBy('created_at')])
            ->get();

        $newCustomers = $returningCustomers = $atRiskCustomers = 0;

        foreach ($customers as $customer) {
            $firstSale = $customer->sales->first()->created_at;
            $lastSale = $customer->sales->last()->created_at;

            if ($lastSale->isToday()) {
                if ($firstSale->isToday()) {
                    $newCustomers++;
                } elseif ($firstSale->lt($today)) {
                    $returningCustomers++;
                }
            } else {
                $days = $lastSale->diffInDays($today);
                if ($days > 30) {
                    $atRiskCustomers++;
                }
            }
        }



        // Send data to the dashboard view
        return view('cashier.dashboard', compact(
            'recentSales',
            'chartLabels',
            'chartData',
            'todaySalesTotal',
            'todayOrderCount',
            'todayItemsSold',
            'todayAverageOrderValue',
            'lowStockCount',
            'topProductsWeekCount',
            'slowMoversCount',
            'newCustomers',
            'returningCustomers',
            'atRiskCustomers'
        ));
    }
    
    public function todaySalesTotal()
    {
        $startOfDay = Carbon::now()->startOfDay();
        $endOfDay = Carbon::now()->endOfDay();

        $total = Sale::whereBetween('created_at', [$startOfDay, $endOfDay])
                     ->where('shop_id', auth()->user()->shop_id)
                     ->sum('total');

        return response()->json([
            'total' => $total,
        ]);
    }
}
