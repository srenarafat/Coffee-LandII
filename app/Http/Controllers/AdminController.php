<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Product;
use App\Models\Setting;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AdminController extends Controller
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

        $weekSalesTotal = Sale::whereBetween('created_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()])
                               ->where('shop_id', auth()->user()->shop_id)
                               ->sum('total');

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


        // Send data to the dashboard view
        return view('admin.dashboard', compact(
            'recentSales',
            'chartLabels',
            'chartData',
            'weekSalesTotal',
            'todaySalesTotal',
            'todayOrderCount',
            'todayItemsSold',
            'todayAverageOrderValue',
            'lowStockCount',
            'topProductsWeekCount',
            'slowMoversCount'
        ));
    }
    
    public function salesData($range)
    {
        switch ($range) {
            case 'today':
                $start = Carbon::today();
                $end = Carbon::today()->endOfDay();
                $sales = Sale::whereBetween('created_at', [$start, $end])
                    ->selectRaw('HOUR(created_at) as hour, SUM(total) as total')
                    ->groupBy('hour')
                    ->orderBy('hour')
                    ->pluck('total', 'hour');

                $labels = [];
                $totals = [];
                for ($h = 0; $h < 24; $h++) {
                    $labels[] = Carbon::createFromTime($h)->format('H:00');
                    $totals[] = $sales[$h] ?? 0;
                }
                break;

            case 'month':
                $start = Carbon::now()->subDays(29)->startOfDay();
                $end = Carbon::now()->endOfDay();
                $sales = Sale::whereBetween('created_at', [$start, $end])
                    ->selectRaw('DATE(created_at) as date, SUM(total) as total')
                    ->groupBy('date')
                    ->orderBy('date')
                    ->pluck('total', 'date');

                $labels = [];
                $totals = [];
                for ($date = $start->copy(); $date->lte($end); $date->addDay()) {
                    $labels[] = $date->format('M d');
                    $totals[] = $sales[$date->format('Y-m-d')] ?? 0;
                }
                break;

            default: // week
                $start = Carbon::now()->subDays(6)->startOfDay();
                $end = Carbon::now()->endOfDay();
                $sales = Sale::whereBetween('created_at', [$start, $end])
                    ->selectRaw('DATE(created_at) as date, SUM(total) as total')
                    ->groupBy('date')
                    ->orderBy('date')
                    ->pluck('total', 'date');

                $labels = [];
                $totals = [];
                for ($date = $start->copy(); $date->lte($end); $date->addDay()) {
                    $labels[] = $date->format('l');
                    $totals[] = $sales[$date->format('Y-m-d')] ?? 0;
                }
                break;
        }

        return response()->json([
            'labels' => $labels,
            'totals' => $totals,
        ]);
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
