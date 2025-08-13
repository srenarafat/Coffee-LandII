@extends('layouts.app')

@section('content')
<div class="container my-4">
    <div class="card shadow-sm border-0 rounded-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0 fw-bold">Low Stock Products</h5>
            <span class="text-muted">Threshold: {{ $threshold }}</span>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-striped table-hover align-middle mb-0">
                    <thead class="sticky-top" style="top:0;z-index:5;background-color:#dbeafe;">
                        <tr>
                            <th class="text-center">{{ __('messages.product') }}</th>
                            <th class="text-center">{{ __('messages.category') }}</th>
                            <th class="text-center">Stock</th>
                            <th class="text-center">{{ __('messages.actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($products as $product)
                            <tr>
                                <td class="text-start">{{ $product->name }}</td>
                                <td class="text-start">{{ $product->category->name ?? '' }}</td>
                                <td class="text-center">{{ $product->stock }}</td>
                                <td class="text-center">
                                    <a href="{{ route('admin.stock-logs.create', ['product_id' => $product->id, 'type' => 'in']) }}" class="btn btn-sm btn-primary">{{ __('messages.stock_in') }}</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center">All products have sufficient stock.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
