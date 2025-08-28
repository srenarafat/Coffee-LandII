@extends('layouts.app')

@section('content')
@php
    $indexRoute = match (Auth::user()->role) {
        'superadmin' => route('superadmin.products.index'),
        'admin'      => route('admin.products.index'),
        default      => route('cashier.products.index'),
    };

    $createRoute = match (Auth::user()->role) {
        'superadmin' => route('superadmin.products.create'),
        'admin'      => route('admin.products.create'),
        default      => route('cashier.products.create'),
    };

    $currentSort     = request('sort');
    $currentSearch   = request('search');
    $currentCategory = request('category');
@endphp

<div class="container-fluid p-3 position-relative">

    @if(session('success'))
        <div id="successToast"
             class="alert alert-success d-flex align-items-center justify-content-between shadow-sm px-4 py-2 animate__animated animate__fadeInDown"
             style="position: absolute; top: 0; right: 0; margin: 12px; max-width: 360px;">
            🎉 <strong>{{ session('success') }}</strong>
        </div>
    @endif

    <!-- Product List Box Card -->
    <div class="card shadow-sm border-0 rounded-4">
        <div class="card-header bg-light border-bottom d-flex flex-wrap gap-2 justify-content-between align-items-center px-4 py-3">
            <h5 class="fw-bold mb-0">🧃 Product List</h5>

            <div class="d-flex flex-wrap align-items-center gap-2">
                <!-- 🔠 Sort A–Z -->
                <a href="{{ url()->current() }}?sort=asc{{ $currentSearch ? '&search='.urlencode($currentSearch) : '' }}{{ $currentCategory ? '&category='.$currentCategory : '' }}"
                   class="btn btn-sm rounded-pill d-flex align-items-center gap-1
                          {{ $currentSort === 'asc' ? 'btn-primary text-white' : 'btn-outline-primary' }}">
                    <i class="bi bi-sort-alpha-down"></i> A–Z
                </a>

                <!-- 🔠 Sort Z–A -->
                <a href="{{ url()->current() }}?sort=desc{{ $currentSearch ? '&search='.urlencode($currentSearch) : '' }}{{ $currentCategory ? '&category='.$currentCategory : '' }}"
                   class="btn btn-sm rounded-pill d-flex align-items-center gap-1
                          {{ $currentSort === 'desc' ? 'btn-primary text-white' : 'btn-outline-primary' }}">
                    <i class="bi bi-sort-alpha-up"></i> Z–A
                </a>

                <!-- Search Bar -->
                <form method="GET" action="{{ url()->current() }}" class="position-relative" style="width: 240px;">
                    @if($currentCategory)
                        <input type="hidden" name="category" value="{{ $currentCategory }}">
                    @endif
                    @if($currentSort)
                        <input type="hidden" name="sort" value="{{ $currentSort }}">
                    @endif
                    <input type="text" name="search" id="liveSearch"
                           value="{{ $currentSearch }}"
                           placeholder="{{ __('messages.search_placeholder') }}"
                           class="form-control shadow-sm rounded-pill ps-4 pe-5"
                           style="height: 38px; font-size: 14px; border: 1px solid #ddd;">
                    <button type="submit"
                            class="position-absolute top-50 end-0 translate-middle-y me-3 border-0 bg-transparent p-0">
                        <i class="bi bi-search text-muted fs-5"></i>
                    </button>
                </form>

                <!-- Add New Product Button -->
                <a href="{{ $createRoute }}"
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
                        {{-- 🆕 Import Price column (after Name) --}}
                        <th>Import Price</th>
                        <th>{{ __('messages.price') }}</th>
                        <th>{{ __('messages.category') }}</th>
                        <th>{{ __('messages.status') }}</th>
                        <th style="width: 240px;">{{ __('messages.action') }}</th>
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

                            {{-- 🆕 Import Price cell --}}
                            <td>
                                @php $ip = $product->import_price ?? null; @endphp
                                {{ $ip !== null ? '$'.number_format($ip, 2) : '-' }}
                            </td>

                            <td>${{ number_format($product->price, 2) }}</td>
                            <td>{{ $product->category->name ?? '' }}</td>
                            <td>
                                @if($product->is_active)
                                    <span class="badge bg-success">{{ __('messages.active') }}</span>
                                @else
                                    <span class="badge bg-secondary">{{ __('messages.inactive') }}</span>
                                @endif
                            </td>
                            <td>
                                <div class="d-flex justify-content-center gap-2">
                                    @if($product->is_active)
                                        <form action="{{ auth()->user()->role === 'superadmin'
                                                            ? route('superadmin.products.deactivate', $product->id)
                                                            : route('admin.products.deactivate', $product->id) }}"
                                              method="POST"
                                              onsubmit="return confirm('{{ __('messages.delete_confirm') }}')">
                                            @csrf @method('PATCH')
                                            <button type="submit" class="btn btn-deactivate btn-sm d-flex align-items-center gap-1">
                                                <i class="bi bi-exclamation-triangle-fill"></i>
                                                <span>{{ __('messages.deactivate') }}</span>
                                            </button>
                                        </form>
                                    @else
                                        <form action="{{ auth()->user()->role === 'superadmin'
                                                            ? route('superadmin.products.activate', $product->id)
                                                            : route('admin.products.activate', $product->id) }}"
                                              method="POST"
                                              onsubmit="return confirm('{{ __('messages.delete_confirm') }}')">
                                            @csrf @method('PATCH')
                                            <button type="submit" class="btn btn-success btn-sm d-flex align-items-center gap-1">
                                                <i class="bi bi-check-circle-fill"></i>
                                                <span>{{ __('messages.activate') }}</span>
                                            </button>
                                        </form>
                                    @endif

                                    <a href="{{ auth()->user()->role === 'superadmin'
                                                ? route('superadmin.products.edit', $product->id)
                                                : route('admin.products.edit', $product->id) }}"
                                       class="btn btn-edit btn-sm d-flex align-items-center gap-1">
                                        <i class="bi bi-pencil-square"></i>
                                        <span>{{ __('messages.edit') }}</span>
                                    </a>

                                    <form action="{{ auth()->user()->role === 'superadmin'
                                                    ? route('superadmin.products.destroy', $product->id)
                                                    : route('admin.products.destroy', $product->id) }}"
                                          method="POST"
                                          onsubmit="return confirm('{{ __('messages.delete_confirm') }}')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-delete btn-sm d-flex align-items-center gap-1">
                                            <i class="bi bi-trash3"></i>
                                            <span>{{ __('messages.delete') }}</span>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    const liveSearchInput = document.getElementById('liveSearch');
    const productBody = document.getElementById('productBody');

    function buildQuery() {
        const params = new URLSearchParams();
        const search = liveSearchInput?.value || '';
        const sort = "{{ $currentSort }}";
        const category = "{{ $currentCategory }}";

        if (search)   params.set('search', search);
        if (sort)     params.set('sort', sort);
        if (category) params.set('category', category);

        return params.toString();
    }

    if (liveSearchInput) {
        let timer;
        liveSearchInput.addEventListener('keyup', function () {
            clearTimeout(timer);
            timer = setTimeout(() => {
                const query = buildQuery();
                fetch(`{{ $indexRoute }}?${query}`, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                })
                .then(response => response.text())
                .then(html => { productBody.innerHTML = html; });
            }, 200);
        });
    }

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
    #successToast { border-left: 6px solid #198754; background-color: #d1e7dd; font-size: 14px; border-radius: 6px; z-index: 1050; }
    .product-img { width: 70px; height: 70px; object-fit: cover; border-radius: 8px; box-shadow: 0 1px 4px rgba(0,0,0,0.1); }
    thead.sticky-top th { background-color: #dbeafe !important; color: #000; font-weight: bold; border-bottom: 1px solid #ccc; }

    .btn-deactivate{ background:#f59e0b; border-color:#f59e0b; color:#111; font-weight:600; }
    .btn-deactivate:hover{ background:#d97706; border-color:#d97706; color:#fff; }

    .btn-edit{ background:#3b82f6; border-color:#3b82f6; color:#fff; font-weight:600; }
    .btn-edit:hover{ background:#1d4ed8; border-color:#1d4ed8; color:#fff; }

    .btn-delete{ background:#e11d48; border-color:#e11d48; color:#fff; font-weight:600; }
    .btn-delete:hover{ background:#b91c1c; border-color:#b91c1c; color:#fff; }

    .btn.btn-sm .bi{ font-size:1rem; line-height:1; }
</style>
@endpush
