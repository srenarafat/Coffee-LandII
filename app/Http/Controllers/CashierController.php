<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Product;
use App\Models\Setting;
use Carbon\Carbon;

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


        // Send data to the dashboard view
        return view('cashier.dashboard', compact(
            'recentSales',
            'chartLabels',
            'chartData',
            'todaySalesTotal',
            'todayOrderCount',
            'todayItemsSold',
            'todayAverageOrderValue',
            'lowStockCount'
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
