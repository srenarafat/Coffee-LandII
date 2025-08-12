@extends('layouts.app')


@section('content')
<div class="container mt-4">
    <h2 class="mb-4 text-primary">{{ __('messages.cashier_dashboard') }}</h2>


    <div class="row">
        <div class="col-md-4">
            <div class="card bg-success text-white mb-3">
                <div class="card-body text-center">
                    <h5 class="card-title">{{ __('messages.total_products') }}</h5>
                    <h3>{{ $productCount ?? 0 }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-info text-white mb-3">
                <div class="card-body text-center">
                    <h5 class="card-title">{{ __('messages.total_sales') }}</h5>
                    <h3>{{ optional($setting)->currency ?? '$' }}{{ number_format($salesTotal ?? 0, 2) }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-warning text-white mb-3">
                <div class="card-body text-center">
                    <h5 class="card-title">{{ __('messages.total_invoices') }}</h5>
                    <h3>{{ $invoiceCount ?? 0 }}</h3>
                </div>
            </div>
        </div>
    </div>


    <div class="text-end">
        <a href="{{ route('cashier.pos.index') }}" class="btn btn-primary">{{ __('messages.go_to_pos') }}</a>
    </div>
</div>
@endsection



