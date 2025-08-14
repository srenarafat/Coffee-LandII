<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Product;
use App\Models\Setting;
use Carbon\Carbon;

class SuperAdminController extends Controller
{
    public function dashboard()
    {
        $recentSales = Sale::with(['user'])->withCount('items')
                           ->latest()->take(9)->get();
        
        $today = Carbon::today();
        $todaySalesTotal = Sale::whereDate('created_at', $today)->sum('total');
        $todayOrderCount = Sale::whereDate('created_at', $today)->count();
        $todayItemsSold = SaleItem::whereDate('created_at', $today)->sum('quantity');
        $todayAverageOrderValue = $todayOrderCount ? $todaySalesTotal / $todayOrderCount : 0;

        $threshold = Setting::value('low_stock_threshold') ?? 5;
        $lowStockCount = Product::where('stock', '<=', $threshold)->count();

        // Calculate sales for the past 7 days
        $startDate = Carbon::now()->subDays(6)->startOfDay();
        $endDate = Carbon::now()->endOfDay();

        $sales = Sale::whereBetween('created_at', [$startDate, $endDate])
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
        $todayTotalSales = Sale::whereDate('created_at', Carbon::today())->sum('total');
        $ordersToday = Sale::whereDate('created_at', Carbon::today())->count();
        $itemsSoldToday = SaleItem::whereHas('sale', fn($q) => $q->whereDate('created_at', Carbon::today()))
                                    ->sum('quantity');
        $avgOrderValue = $ordersToday > 0 ? $todayTotalSales / $ordersToday : 0;
        $threshold = Setting::value('low_stock_threshold') ?? 5;
        $lowStockCount = Product::where('stock', '<=', $threshold)->count();


        return view('superadmin.dashboard', compact(
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
}
