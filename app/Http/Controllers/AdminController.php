<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Category;
use App\Models\Sale;
use App\Models\User;

class AdminController extends Controller
{
    public function dashboard()
    {
        // Count total products
        $productCount = Product::count();

        // Count total categories
        $categoryCount = Category::count();

        // Calculate total sales amount (sum of all sale totals)
        $salesTotal = Sale::sum('total');

        // Count total number of invoices (sales records)
        $invoiceCount = Sale::count();

        // Fetch latest 5 sales with item count
        $recentSales = Sale::withCount('items')
                           ->latest()
                           ->take(9)
                           ->get();

        // Count total users
        $totalUsers = User::count();
        $chartLabels = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
        $chartData = [0, 0, 0, 0, 500, 0, 0]; // Replace with real sales data later

        // Send data to the dashboard view
        return view('admin.dashboard', compact(
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
