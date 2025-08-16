@extends('layouts.app')

@section('content')
<div class="container my-4">
    <h1 class="mb-4">Customers</h1>

    <div class="d-flex flex-wrap gap-2 mb-4">
        <span class="badge bg-success">New: {{ $newCustomers->count() }}</span>
        <span class="badge bg-info text-dark">Returning: {{ $returningCustomers->count() }}</span>
        <span class="badge bg-danger">At-Risk: {{ $atRiskCustomers->count() }}</span>
    </div>

    <h2 class="h5">New Customers</h2>
    <ul class="list-group mb-4">
        @forelse($newCustomers as $customer)
            <li class="list-group-item">{{ $customer->name }}</li>
        @empty
            <li class="list-group-item text-muted">No new customers</li>
        @endforelse
    </ul>

    <h2>Returning Customers</h2>
    <ul>
        @forelse($returningCustomers as $customer)
            <li>{{ $customer->name }}</li>
        @empty
            <li class="list-group-item text-muted">No returning customers</li>
        @endforelse
    </ul>

    <h2 class="h5">Returning Customers</h2>
    <ul class="list-group mb-4">
        @forelse($atRiskCustomers as $customer)
            <li class="list-group-item d-flex justify-content-between align-items-center">
                <span>{{ $customer->name }}</span>
                <div class="btn-group btn-group-sm">
                    <a href="#" class="btn btn-outline-primary">Contact</a>
                    <a href="#" class="btn btn-outline-secondary">Notes</a>
                </div>
            </li>
        @empty
            <li class="list-group-item text-muted">No at-risk customers</li>
        @endforelse
    </ul>
</div>
@endsection
