<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Sale;
use App\Models\Product;
use App\Models\Category;

class SuperAdminController extends Controller
{
    public function dashboard()
    {
        $productCount = Product::count();
        $categoryCount = Category::count();
        $salesTotal = Sale::sum('total');
        $invoiceCount = Sale::count();
        $recentSales = Sale::withCount('items')
                           ->latest()
                           ->take(9)
                           ->get();
        // Count total users
        $totalUsers = User::count();
        $chartLabels = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
        $chartData = [0, 0, 0, 0, 500, 0, 0]; // Placeholder data

        return view('superadmin.dashboard', compact(
            'productCount',
            'categoryCount',
            'salesTotal',
            'invoiceCount',
            'recentSales',
            'totalUsers',
            'chartLabels',
            'chartData'
        ));
    }
}
