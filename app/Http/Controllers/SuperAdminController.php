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
        
        $startOfDay = Carbon::now()->startOfDay();
        $endOfDay = Carbon::now()->endOfDay();

        $todaySalesTotal = Sale::whereBetween('created_at', [$startOfDay, $endOfDay])->sum('total');
        $todayOrderCount = Sale::whereBetween('created_at', [$startOfDay, $endOfDay])->count();
        $todayItemsSold = SaleItem::whereBetween('created_at', [$startOfDay, $endOfDay])->sum('quantity');
        $todayAverageOrderValue = $todayOrderCount ? $todaySalesTotal / $todayOrderCount : 0;

        $weekSalesTotal = Sale::whereBetween('created_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()])->sum('total');

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
        $todayTotalSales = Sale::whereBetween('created_at', [$startOfDay, $endOfDay])->sum('total');
        $ordersToday = Sale::whereBetween('created_at', [$startOfDay, $endOfDay])->count();
        $itemsSoldToday = SaleItem::whereHas('sale', fn($q) => $q->whereBetween('created_at', [$startOfDay, $endOfDay]))
                                    ->sum('quantity');
        $avgOrderValue = $ordersToday > 0 ? $todayTotalSales / $ordersToday : 0;
        $threshold = Setting::value('low_stock_threshold') ?? 5;
        $lowStockCount = Product::where('stock', '<=', $threshold)->count();


        return view('superadmin.dashboard', compact(
            'recentSales',
            'chartLabels',
            'chartData',
            'weekSalesTotal',
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
    
    public function todaySalesTotal()
    {
        $total = Sale::whereBetween('created_at', [Carbon::now()->startOfDay(), Carbon::now()->endOfDay()])->sum('total');

        return response()->json(['total' => $total]);
    }
}
