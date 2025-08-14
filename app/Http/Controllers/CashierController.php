<?php

namespace App\Http\Controllers;

use App\Models\Sale;
class CashierController extends Controller
{
    public function dashboard()
    {
        // Fetch latest 5 sales with item count
        $recentSales = Sale::with(['user'])
                           ->withCount('items')
                           ->latest()
                           ->take(9)
                           ->get();

        $chartLabels = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
        $chartData = [0, 0, 0, 0, 500, 0, 0]; // Replace with real sales data later

        // Send data to the dashboard view
        return view('cashier.dashboard', compact(
            'recentSales',
            'chartLabels',
            'chartData'
        ));
    }
}
