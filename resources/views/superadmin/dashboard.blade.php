@extends('layouts.app')

@section('content')
    @include('dashboard.overview', [
    'routePrefix' => 'superadmin',
    'recentSales' => $recentSales,
    'chartLabels' => $chartLabels,
    'chartData' => $chartData,
    'todaySalesTotal' => $todaySalesTotal,
    'todayOrderCount' => $todayOrderCount,
    'todayItemsSold' => $todayItemsSold,
    'todayAverageOrderValue' => $todayAverageOrderValue,
    'lowStockCount' => $lowStockCount,
    'topProductsWeekCount' => $topProductsWeekCount,
    'slowMoversCount' => $slowMoversCount,
    'newCustomers' => $newCustomers,
    'returningCustomers' => $returningCustomers,
    'atRiskCustomers' => $atRiskCustomers,
])
@endsection

