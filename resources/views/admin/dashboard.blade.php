@extends('layouts.app')

@section('content')
    @include('dashboard.overview', [
        'routePrefix' => 'admin',
        'recentSales' => $recentSales,
        'chartLabels' => $chartLabels,
        'chartData' => $chartData,
    ])  
@endsection


