@extends('layouts.app')

@section('content')
@include('dashboard.overview', [
        'routePrefix' => 'cashier',
        'recentSales' => $recentSales,
        'chartLabels' => $chartLabels,
        'chartData' => $chartData,
    ])
@endsection
