@extends('layouts.app')

@section('content')
<div class="container">
    <h5 class="fw-bold text-center text-uppercase text-brown mb-3">Slow Moving Products</h5>
    <div class="table-responsive">
        <table class="table table-bordered text-center align-middle">
            <thead class="table-primary">
                <tr>
                    <th>#</th>
                    <th>Product</th>
                    <th>Category</th>
                    <th>Days Since Last Sale</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach($products as $index => $product)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $product->name }}</td>
                        <td>{{ $product->category->name ?? 'N/A' }}</td>
                        <td>{{ $product->days_since_last_sale ?? 'No Sales' }}</td>
                        <td>
                            <form method="POST" action="{{ auth()->user()->role === 'superadmin' ? route('superadmin.products.promote', $product->id) : route('admin.products.promote', $product->id) }}">
                                @csrf
                                <button class="btn btn-sm btn-outline-primary">Mark for Promotion</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection