@extends('layouts.app')

@section('content')
<div class="container-fluid mt-0">
    <div class="card shadow-sm">
    <div class="card-body print-area">
        @include('partials.sales-print', [
            'sales' => $sales,
            'totalAmount' => $totalAmount,
            'exportRoute' => auth()->user()->role === 'superadmin'
                ? route('superadmin.sales.report', array_merge(request()->except('page'), ['export' => 'csv']))
                : route('admin.sales.report', array_merge(request()->except('page'), ['export' => 'csv'])),
            'printRoute' => auth()->user()->role === 'superadmin'
                ? route('superadmin.sales.report', array_merge(request()->all(), ['print' => 1]))
                : route('admin.sales.report', array_merge(request()->all(), ['print' => 1])),
            'filter' => view('admin.sales.filter', ['users' => $users, 'categories' => $categories])->render(),
        ])
    </div>
</div>
</div>
@endsection

