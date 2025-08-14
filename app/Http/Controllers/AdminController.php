<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use Carbon\Carbon;

class AdminController extends Controller
{
    public function dashboard()
    {
        // Fetch latest 5 sales with item count
        $recentSales = Sale::with(['user'])
                           ->withCount('items')
                           ->latest()
                           ->take(9)
                           ->get();

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

        // Send data to the dashboard view
        return view('admin.dashboard', compact(
            'recentSales',
            'chartLabels',
            'chartData'
        ));
    }
}
