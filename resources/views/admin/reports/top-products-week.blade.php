@extends('layouts.app')

@section('content')
<div class="container">
    <h5 class="fw-bold text-center text-uppercase mb-4">Top Products This Week</h5>
    <div class="table-responsive">
        <table class="table table-bordered align-middle">
            <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>Product</th>
                    <th>Qty</th>
                    <th>Revenue</th>
                </tr>
            </thead>
            <tbody>
                @foreach($topProducts as $index => $item)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>
                            <div class="d-flex align-items-center">
                                @if($item->product->image)
                                    <img src="{{ asset('storage/' . $item->product->image) }}" alt="{{ $item->product->name }}" width="40" class="me-2">
                                @endif
                                <div>
                                    <div>{{ $item->product->name }}</div>
                                    <small class="text-muted">{{ $item->product->category->name ?? 'N/A' }}</small>
                                </div>
                            </div>
                        </td>
                        <td>{{ $item->qty }}</td>
                        <td>{{ number_format($item->revenue, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
