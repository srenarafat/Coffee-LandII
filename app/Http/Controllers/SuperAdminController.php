<?php

namespace App\Http\Controllers;

use App\Models\Sale;

class SuperAdminController extends Controller
{
    public function dashboard()
    {
        $recentSales = Sale::withCount('items')
                           ->latest()
                           ->take(9)
                           ->get();
        $chartLabels = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
        $chartData = [0, 0, 0, 0, 500, 0, 0]; // Placeholder data

        return view('superadmin.dashboard', compact(
            'recentSales',
            'chartLabels',
            'chartData'
        ));
    }
}
