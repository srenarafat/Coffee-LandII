@extends('layouts.app')


@section('content')
<div class="container-fluid mt-4">
    <div class="card shadow-sm">
        <div class="card-body">
            <h4 class="fw-bold mb-4 text-brown"> {{ __('messages.edit_product') }}</h4>


            <form action="{{ auth()->user()->role === 'superadmin' ? route('superadmin.products.update', $product->id) : route('admin.products.update', $product->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')


                <div class="row g-4">
                    <!-- Left Column -->
                    <div class="col-md-6">
                        <!-- Product Name -->
                        <div class="mb-3">
                            <label class="form-label">{{ __('messages.product_name') }}</label>
                            <input type="text" name="name" value="{{ old('name', $product->name) }}" class="form-control shadow-sm" placeholder="e.g., Iced Latte" required>
                        </div>

                        <!-- Base Price -->
                        <div class="mb-3">
                            <label class="form-label">{{ __('messages.price') }} ($)</label>
                            <div class="input-group shadow-sm">
                                <span class="input-group-text">$</span>
                                <input type="number" name="price" value="{{ old('price', $product->price) }}" class="form-control" step="0.01" placeholder="e.g., 2.50" required>
                            </div>
                        </div>

                        <!-- Size Prices -->
                        <div class="mb-3">
                            <label class="form-label">{{ __('messages.small') }} {{ __('messages.price') }} ($)</label>
                            <input type="number" name="price_small" value="{{ old('price_small', $product->price_small) }}" class="form-control shadow-sm" step="0.01">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">{{ __('messages.medium') }} {{ __('messages.price') }} ($)</label>
                            <input type="number" name="price_medium" value="{{ old('price_medium', $product->price_medium) }}" class="form-control shadow-sm" step="0.01">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">{{ __('messages.large') }} {{ __('messages.price') }} ($)</label>
                            <input type="number" name="price_large" value="{{ old('price_large', $product->price_large) }}" class="form-control shadow-sm" step="0.01">
                        </div>

                        <!-- Category -->
                        <div class="mb-3">
                            <label class="form-label">{{ __('messages.category') }}</label>
                            <select name="category_id" class="form-select shadow-sm" required>
                                @foreach($categoryOptions as $id => $name)
                                    <option value="{{ $id }}" {{ old('category_id', $product->category_id) == $id ? 'selected' : '' }}>
                                        {{ $name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>


                    <!-- Right Column -->
                    <div class="col-md-6 text-center">
                        <!-- Product Image Upload -->
                        <div class="mb-3">
                            <label class="form-label">{{ __('messages.product_image') }}</label>
                            <input type="file" name="image" class="form-control shadow-sm">
                        </div>


                        <!-- Current Image Preview -->
                        @if($product->image && file_exists(public_path('storage/' . $product->image)))
                            <div class="mb-3">
                                <img src="{{ asset('storage/' . $product->image) }}" alt="Product Image" class="rounded shadow border"
                                     style="width: 200px; height: 140px; object-fit: cover;">
                            </div>
                        @endif


                        <!-- Buttons Centered Below Image -->
                        <div class="mt-3">
                            <a href="{{ auth()->user()->role === 'superadmin' ? route('superadmin.products.index') : route('admin.products.index') }}" class="btn btn-outline-secondary shadow-sm d-inline-flex align-items-center gap-2 px-4 me-2">
                                <span>{{ __('messages.back') }}</span>
                            </a>
                            <button type="submit" class="btn btn-primary shadow-sm d-inline-flex align-items-center gap-2 px-4">
                                <span>{{ __('messages.update') }}</span>
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection


@push('styles')
<style>
    .btn-brown {
        background-color: #4E342E;
        border: none;
    }


    .btn-brown:hover {
        background-color: #3E2723;
    }


    .text-brown {
        color: #4E342E;
    }




    .form-control:focus, .form-select:focus {
        border-color: #4E342E;
        box-shadow: 0 0 0 0.15rem rgba(78, 52, 46, 0.25);
    }


    label.form-label {
        font-weight: 600;
        color: #4E342E;
    }
</style>
@endpush



