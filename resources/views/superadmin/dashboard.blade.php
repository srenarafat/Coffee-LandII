@extends('layouts.app')

@section('content')
    @include('dashboard.overview', [
        'routePrefix' => 'superadmin',
        'recentSales' => $recentSales,
        'chartLabels' => $chartLabels,
        'chartData' => $chartData,
    ])
@endsection

