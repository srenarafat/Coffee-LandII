@extends('layouts.app')

@section('content')
@php
    $routePrefix = auth()->user()->role === 'superadmin' ? 'superadmin' : 'admin';
@endphp
<div class="container my-4">
    <h1 class="mb-4">{{ __('messages.customers') }}</h1>
    <div class="d-flex flex-wrap gap-2 mb-4">
        <span class="badge bg-success">{{ __('messages.new') }}: {{ $newCustomers->count() }}</span>
        <span class="badge bg-info text-dark">{{ __('messages.returning') }}: {{ $returningCustomers->count() }}</span>
        <span class="badge bg-danger">{{ __('messages.at_risk') }}: {{ $atRiskCustomers->count() }}</span>
    </div>

    <h2 class="h5">{{ __('messages.new_customers') }}</h2>
    <ul class="list-group mb-4">
        @forelse($newCustomers as $customer)
            <li class="list-group-item">{{ $customer->name }}</li>
        @empty
            <li class="list-group-item text-muted">{{ __('messages.no_new_customers') }}</li>
        @endforelse
    </ul>

    <h2>{{ __('messages.returning_customers') }}</h2>
    <ul class="list-group mb-4">
        @forelse($returningCustomers as $customer)
            <li class="list-group-item">{{ $customer->name }}</li>
        @empty
            <li class="list-group-item text-muted">{{ __('messages.no_returning_customers') }}</li>
        @endforelse
    </ul>

    <h2 class="h5">{{ __('messages.at_risk_customers') }}</h2>
    <ul class="list-group mb-4">
        @forelse($atRiskCustomers as $customer)
            <li class="list-group-item d-flex justify-content-between align-items-center">
                <span>{{ $customer->name }}</span>
                <div class="btn-group btn-group-sm">
                    <a href="{{ route($routePrefix . '.customers.contact', $customer) }}" class="btn btn-outline-primary">{{ __('messages.contact') }}</a>
                    <a href="{{ route($routePrefix . '.customers.notes', $customer) }}" class="btn btn-outline-secondary">{{ __('messages.notes') }}</a>
                </div>
            </li>
        @empty
            <li class="list-group-item text-muted">{{ __('messages.no_at_risk_customers') }}</li>
        @endforelse
    </ul>
</div>
@endsection
