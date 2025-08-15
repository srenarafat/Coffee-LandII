@extends('layouts.app')

@section('content')
    @include('dashboard.overview', [
        'routePrefix' => 'admin',
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
    ])
@endsection


