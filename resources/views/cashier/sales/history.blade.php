@extends('layouts.app')

@section('content')
<div class="container-fluid mt-0">
    <div class="card shadow-sm">
    <div class="card-body print-area">
        @include('partials.sales-print', [
            'sales' => $sales,
            'totalAmount' => $totalAmount,
            'exportRoute' => route('cashier.sales.history', array_merge(request()->except('page'), ['export' => 'csv'])),
            'printRoute' => route('cashier.sales.history', array_merge(request()->all(), ['print' => 1])),
            'filter' => view('cashier.sales.filter', compact('categories'))->render(),
        ])
    </div>
</div>
</div>

@endsection