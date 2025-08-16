@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Customers</h1>

    <h2>New Customers</h2>
    <ul>
        @forelse($newCustomers as $customer)
            <li>{{ $customer->name }}</li>
        @empty
            <li>No new customers</li>
        @endforelse
    </ul>

    <h2>Returning Customers</h2>
    <ul>
        @forelse($returningCustomers as $customer)
            <li>{{ $customer->name }}</li>
        @empty
            <li>No returning customers</li>
        @endforelse
    </ul>

    <h2>At-Risk Customers</h2>
    <ul>
        @forelse($atRiskCustomers as $customer)
            <li>{{ $customer->name }}</li>
        @empty
            <li>No at-risk customers</li>
        @endforelse
    </ul>
</div>
@endsection