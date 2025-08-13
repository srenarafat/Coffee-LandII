@extends('layouts.app')

@section('content')
<div class="container-fluid mt-0">
    <div class="card shadow-sm">
    <div class="card-body print-area">
        @include('partials.sales-print', [
            'sales' => $sales,
            'totalAmount' => $totalAmount,
            'exportRoute' => auth()->user()->role === 'superadmin'
                ? route('superadmin.reports.sales.export', request()->except('page'))
                : route('admin.reports.sales.export', request()->except('page')),
            'printRoute' => auth()->user()->role === 'superadmin'
                ? route('superadmin.reports.sales.print', request()->all())
                : route('admin.reports.sales.print', request()->all()),
            'filter' => view('admin.sales.filter', ['users' => $users, 'categories' => $categories])->render(),
        ])
    </div>
</div>
</div>
@endsection

