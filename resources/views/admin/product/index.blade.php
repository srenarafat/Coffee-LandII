@extends('layouts.app')


@section('content')
<div class="container-fluid p-3 position-relative">


    @if(session('success'))
        <div id="successToast"
             class="alert alert-success d-flex align-items-center justify-content-between shadow-sm px-4 py-2 animate__animated animate__fadeInDown"
             style="position: absolute; top: 0; right: 0; margin: 12px; max-width: 360px;">
            🎉 <strong>{{ session('success') }}</strong>
        </div>
    @endif


    <!-- Header -->
   <!-- Product List Box Card -->
<div class="card shadow-sm border-0 rounded-4">
    <div class="card-header bg-light border-bottom d-flex justify-content-between align-items-center px-4 py-3">
        <h5 class="fw-bold mb-0">🧃 Product List</h5>
        <div class="d-flex align-items-center gap-2">
    <!-- Search Bar -->
    <form method="GET" action="{{ url()->current() }}" class="position-relative" style="width: 240px;">
        @if(request('category'))
            <input type="hidden" name="category" value="{{ request('category') }}">
        @endif

        <input type="text" name="search" id="liveSearch"
            value="{{ request('search') }}"
            placeholder="{{ __('messages.search_placeholder') }}"
            class="form-control shadow-sm rounded-pill ps-4 pe-5"
            style="height: 38px; font-size: 14px; border: 1px solid #ddd;">

        <!-- Search Icon Button -->
        <button type="submit"
            class="position-absolute top-50 end-0 translate-middle-y me-3 border-0 bg-transparent p-0">
            <i class="bi bi-search text-muted fs-5"></i>
        </button>
    </form>

    <!-- Add New Product Button -->
    <a href="{{ route('admin.products.create') }}"
       class="btn btn-primary rounded-pill d-flex align-items-center gap-2 shadow-sm">
        <i class="bi bi-plus-lg"></i> {{ __('messages.add_new_product') }}
    </a>
</div>
    </div>


    <!-- Product Table -->
    <div class="table-responsive" style="max-height: calc(100vh - 170px);">
        <table class="table table-hover align-middle text-center mb-0">
           <thead class="sticky-top" style="top: 0; z-index: 5; background-color: #dbeafe;">
                <tr>
                    <th style="width: 60px;">{{ __('messages.serial') }}</th>
                    <th style="width: 100px;">{{ __('messages.image') }}</th>
                    <th>{{ __('messages.name') }}</th>
                    <th>{{ __('messages.price') }}</th>
                    <th>{{ __('messages.category') }}</th>
                    <th style="width: 180px;">{{ __('messages.action') }}</th>
                </tr>
            </thead>
            <tbody id="productBody" style="min-height: 400px;">
                @foreach($products as $key => $product)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>
                            <img src="{{ asset('storage/' . $product->image) }}" class="product-img" alt="product">
                        </td>
                        <td>{{ $product->name }}</td>
                        <td>${{ number_format($product->price, 2) }}</td>
                        <td>{{ $product->category->name ?? '' }}</td>
                        <td>
                            <div class="d-flex justify-content-center gap-2">
                                <a href="{{ route('admin.products.edit', $product->id) }}" class="btn btn-outline-primary btn-sm"> {{ __('messages.edit') }}</a>
                                <form action="{{ route('admin.products.destroy', $product->id) }}" method="POST" onsubmit="return confirm('Are you sure?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-outline-danger btn-sm"> {{ __('messages.delete') }}</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>




@endsection




@push('scripts')
<script>
    document.getElementById('liveSearch').addEventListener('keyup', function () {
        const query = this.value;
        fetch(`{{ route('admin.products.index') }}?search=${encodeURIComponent(query)}`, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(response => response.text())
        .then(html => {
            document.getElementById('productBody').innerHTML = html;
        });
    });


    document.addEventListener('DOMContentLoaded', function () {
        const toast = document.getElementById('successToast');
        if (toast) {
            setTimeout(() => {
                toast.classList.remove('animate__fadeInDown');
                toast.classList.add('animate__fadeOutUp');
                setTimeout(() => toast.remove(), 800);
            }, 2000);
        }
    });
</script>
@endpush


@push('styles')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" />
<style>
    #successToast {
        border-left: 6px solid #198754;
        background-color: #d1e7dd;
        font-size: 14px;
        border-radius: 6px;
        z-index: 1050;
    }


    .product-img {
        width: 70px;
        height: 70px;
        object-fit: cover;
        border-radius: 8px;
        box-shadow: 0 1px 4px rgba(0, 0, 0, 0.1);
    }


    .btn-outline-primary:hover {
        background-color: #0d6efd;
        color: white;
    }


    .btn-outline-danger:hover {
        background-color: #dc3545;
        color: white;
    }


    /* 🔵 Header Style like Category List */
    thead.sticky-top th {
        background-color: #dbeafe !important;
        color: #000;
        font-weight: bold;
        border-bottom: 1px solid #ccc;
    }
   
</style>
@endpush





