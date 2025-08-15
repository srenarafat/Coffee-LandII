<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Product;
use App\Models\Setting;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class SuperAdminController extends Controller
{
    public function dashboard()
    {
        $recentSales = Sale::with(['user'])->withCount('items')
                           ->latest()->take(9)->get();
        
        $startOfDay = Carbon::now()->startOfDay();
        $endOfDay = Carbon::now()->endOfDay();

        $todaySalesTotal = Sale::whereBetween('created_at', [$startOfDay, $endOfDay])->sum('total');
        $todayOrderCount = Sale::whereBetween('created_at', [$startOfDay, $endOfDay])->count();
        $todayItemsSold = SaleItem::whereBetween('created_at', [$startOfDay, $endOfDay])->sum('quantity');
        $todayAverageOrderValue = $todayOrderCount ? $todaySalesTotal / $todayOrderCount : 0;

        $weekSalesTotal = Sale::whereBetween('created_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()])->sum('total');

        $threshold = Setting::value('low_stock_threshold') ?? 5;
        $lowStockCount = Product::where('stock', '<=', $threshold)->count();
        // Calculate sales stats for the current week
        $startDate = Carbon::now()->startOfWeek();
        $endDate = Carbon::now()->endOfWeek();

        $sales = Sale::whereBetween('created_at', [$startDate, $endDate])
                      ->selectRaw('DATE(created_at) as date, SUM(total) as total, COUNT(*) as orders')
                      ->groupBy('date')
                      ->orderBy('date')
                      ->get()
                      ->keyBy('date');

        $itemsQuery = SaleItem::whereBetween('created_at', [$startDate, $endDate])
            ->selectRaw('DATE(created_at) as date, SUM(quantity) as qty')
            ->groupBy('date')
            ->orderBy('date')
            ->pluck('qty', 'date');

        $chartLabels = $chartData = $chartOrders = $chartItems = $chartAov = [];

        for ($date = $startDate->copy(); $date->lte($endDate); $date->addDay()) {
            $key = $date->format('Y-m-d');
            $total  = $sales[$key]->total   ?? 0;
            $orders = $sales[$key]->orders  ?? 0;
            $items  = $itemsQuery[$key]     ?? 0;

            $chartLabels[] = $date->format('l');
            $chartData[]   = $total;
            $chartOrders[] = $orders;
            $chartItems[]  = $items;
            $chartAov[]    = $orders ? $total / $orders : 0;
        }

        // Today's metrics
        $todayTotalSales = Sale::whereBetween('created_at', [$startOfDay, $endOfDay])->sum('total');
        $ordersToday = Sale::whereBetween('created_at', [$startOfDay, $endOfDay])->count();
        $itemsSoldToday = SaleItem::whereHas('sale', fn($q) => $q->whereBetween('created_at', [$startOfDay, $endOfDay]))
                                    ->sum('quantity');
        $avgOrderValue = $ordersToday > 0 ? $todayTotalSales / $ordersToday : 0;
        $threshold = Setting::value('low_stock_threshold') ?? 5;
        $lowStockCount = Product::where('stock', '<=', $threshold)->count();


        $weekAgo = Carbon::now()->subDays(7);
        $topProductsWeekCount = SaleItem::where('created_at', '>=', $weekAgo)
            ->select('product_id', DB::raw('SUM(quantity) as total_quantity'))
            ->groupBy('product_id')
            ->orderByDesc('total_quantity')
            ->limit(10)
            ->get()
            ->count();

        $cutoff = Carbon::now()->subDays(30);

        $noRecentSales = Product::leftJoin('sale_items', function ($join) use ($cutoff) {
                $join->on('products.id', '=', 'sale_items.product_id')
                    ->where('sale_items.created_at', '>=', $cutoff);
            })
            ->whereNull('sale_items.id')
            ->pluck('products.id');

        $bottom10 = SaleItem::leftJoin('products', 'sale_items.product_id', '=', 'products.id')
            ->select('products.id', DB::raw('SUM(sale_items.quantity) as total_quantity'))
            ->groupBy('products.id')
            ->orderBy('total_quantity')
            ->limit(10)
            ->pluck('products.id');

        $slowMoversCount = $noRecentSales->merge($bottom10)->unique()->count();

        return view('superadmin.dashboard', compact(
            'recentSales',
            'chartLabels',
            'chartData',
            'chartOrders',
            'chartItems',
            'chartAov',
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
                    ->selectRaw('HOUR(created_at) as hour, SUM(total) as total, COUNT(*) as orders')
                    ->groupBy('hour')
                    ->orderBy('hour')
                    ->get()
                    ->keyBy('hour');
                $itemsQuery = SaleItem::whereBetween('created_at', [$start, $end])
                    ->selectRaw('HOUR(created_at) as hour, SUM(quantity) as qty')
                    ->groupBy('hour')
                    ->orderBy('hour')
                    ->pluck('qty', 'hour');

                $labels = $totals = $orders = $items = [];
                for ($h = 0; $h < 24; $h++) {
                    $labels[] = Carbon::createFromTime($h)->format('H:00');
                    $totals[] = $sales[$h]->total ?? 0;
                    $orders[] = $sales[$h]->orders ?? 0;
                    $items[]  = $itemsQuery[$h] ?? 0;
                }
                break;

            case 'month':
                $start = Carbon::now()->startOfMonth();
                $end   = Carbon::now()->endOfMonth();
                $sales = Sale::whereBetween('created_at', [$start, $end])
                    ->selectRaw('DATE(created_at) as date, SUM(total) as total, COUNT(*) as orders')
                    ->groupBy('date')
                    ->orderBy('date')
                    ->get()
                    ->keyBy('date');

                $itemsQuery = SaleItem::whereBetween('created_at', [$start, $end])
                    ->selectRaw('DATE(created_at) as date, SUM(quantity) as qty')
                    ->groupBy('date')
                    ->orderBy('date')
                    ->pluck('qty', 'date');

                $labels = $totals = $orders = $items = [];
                for ($date = $start->copy(); $date->lte($end); $date->addDay()) {
                    $key = $date->format('Y-m-d');
                    $labels[] = $date->format('M d');
                    $totals[] = $sales[$key]->total ?? 0;
                    $orders[] = $sales[$key]->orders ?? 0;
                    $items[]  = $itemsQuery[$key] ?? 0;
                }
                break;

            default: // week
                $start = Carbon::now()->startOfWeek();
                $end = Carbon::now()->endOfWeek();
                $sales = Sale::whereBetween('created_at', [$start, $end])
                    ->selectRaw('DATE(created_at) as date, SUM(total) as total, COUNT(*) as orders')
                    ->groupBy('date')
                    ->orderBy('date')
                    ->get()
                    ->keyBy('date');

                $itemsQuery = SaleItem::whereBetween('created_at', [$start, $end])
                    ->selectRaw('DATE(created_at) as date, SUM(quantity) as qty')
                    ->groupBy('date')
                    ->orderBy('date')
                    ->pluck('qty', 'date');

                $labels = $totals = $orders = $items = [];
                for ($date = $start->copy(); $date->lte($end); $date->addDay()) {
                    $labels[] = $date->format('l');
                    $totals[] = $sales[$key]->total ?? 0;
                    $orders[] = $sales[$key]->orders ?? 0;
                    $items[]  = $itemsQuery[$key] ?? 0;
                }
                break;
        }

        return response()->json([
            'labels' => $labels,
            'totals' => $totals,
            'orders' => $orders,
            'items'  => $items,
        ]);
    }
    
    public function todaySalesTotal()
    {
        $total = Sale::whereBetween('created_at', [Carbon::now()->startOfDay(), Carbon::now()->endOfDay()])->sum('total');

        return response()->json(['total' => $total]);
    }
}
