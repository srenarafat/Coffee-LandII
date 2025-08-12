@extends('layouts.app')
@section('content')
<div class="container my-4">
    <div class="card shadow-sm">
            <div class="card-body">
            <h5 class="fw-bold mb-4">{{ __('messages.stock_adjustment') }}</h5>

            <form method="GET" class="mb-3">
                <select name="category_id" class="form-select w-auto" onchange="this.form.submit()">
                    <option value="">{{ __('messages.all_categories') }}</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}" {{ ($categoryId ?? '') == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                    @endforeach
                </select>
            </form>

            <form action="{{ auth()->user()->role === 'superadmin' ? route('superadmin.stock-logs.store') : route('admin.stock-logs.store') }}" method="POST">
                @csrf

                <div class="mb-3">
                    <label class="form-label">{{ __('messages.product') }}</label>
                    <select name="product_id" class="form-select shadow-sm" required>
                        @foreach($products as $product)
                            <option value="{{ $product->id }}">{{ $product->name }} (Stock: {{ $product->stock }})</option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label">{{ __('messages.type') }}</label>
                    <select name="type" class="form-select shadow-sm" required>
                        <option value="in">{{ __('messages.stock_in') }}</option>
                        <option value="out">{{ __('messages.stock_out') }}</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label">{{ __('messages.qty') }}</label>
                    <input type="number" name="quantity" class="form-control shadow-sm" min="1" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">{{ __('messages.note_optional') }}</label>
                    <input type="text" name="note" class="form-control shadow-sm">
                </div>
                <div class="mt-4 text-end">
                    <a href="{{ auth()->user()->role === 'superadmin' ? route('superadmin.stock-logs.index') : route('admin.stock-logs.index') }}" class="btn btn-outline-secondary shadow-sm me-2">
                        <i class="bi bi-arrow-left"></i> {{ __('messages.back') }}
                    </a>
                    <button type="submit" class="btn btn-primary shadow-sm">
                        <i class="bi bi-save"></i> {{ __('messages.save') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
