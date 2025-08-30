@extends('layouts.app')

@php
    $foodCategory = \App\Models\Category::where('name', 'Food')->first();
    $foodCategoryIds = $foodCategory ? \App\Models\Category::descendantsAndSelfIds($foodCategory->id) : [];
@endphp

@section('content')
<div class="container-fluid mt-4">
  <div class="card shadow-sm">
    <div class="card-body">
      <h4 class="fw-bold mb-4 text-brown">{{ __('messages.edit_product') }}</h4>

      <form action="{{ auth()->user()->role === 'superadmin'
                        ? route('superadmin.products.update', $product->id)
                        : route('admin.products.update', $product->id) }}"
            method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        {{-- Row 1: Category | Product Name --}}
        <div class="row g-4">
          <div class="col-md-6">
            <label class="form-label">{{ __('messages.category') }}</label>
            <select name="category_id" class="form-select shadow-sm" required>
              @foreach($categoryOptions as $id => $name)
                <option value="{{ $id }}" {{ old('category_id', $product->category_id) == $id ? 'selected' : '' }}>
                  {{ $name }}
                </option>
              @endforeach
            </select>
          </div>

          <div class="col-md-6">
            <label class="form-label">{{ __('messages.product_name') }}</label>
            <input type="text" name="name" class="form-control shadow-sm"
                   value="{{ old('name', $product->name) }}"
                   placeholder="e.g. Latte" required>
          </div>
        </div>

        {{-- Row 2: Base Price | Import Price --}}
        <div class="row g-4 mt-1">

          <div class="col-md-6">
            <label class="form-label">{{ __('messages.import_price') }} ($)</label>
            <div class="input-group shadow-sm">
              <span class="input-group-text">$</span>
              <input type="number" step="0.01" name="import_price" class="form-control"
                     value="{{ old('import_price', $product->import_price) }}" placeholder="e.g. 0.60">
            </div>
          </div>
          <div class="col-md-6">
            <label class="form-label">{{ __('messages.price') }} ($)</label>
            <div class="input-group shadow-sm">
              <span class="input-group-text">$</span>
              <input type="number" step="0.01" name="price" class="form-control"
                     value="{{ old('price', $product->price) }}" placeholder="e.g. 2.50" required>
            </div>
          </div>

          
        </div>

        {{-- Row 3: S | M | L --}}
        <div id="size-price-group" class="row g-4 mt-1">
          <div class="col-md-4">
            <label class="form-label">{{ __('messages.small') }} {{ __('messages.price') }} ($)</label>
            <input type="number" step="0.01" name="price_small" class="form-control shadow-sm"
                   value="{{ old('price_small', $product->price_small) }}">
          </div>
          <div class="col-md-4">
            <label class="form-label">{{ __('messages.medium') }} {{ __('messages.price') }} ($)</label>
            <input type="number" step="0.01" name="price_medium" class="form-control shadow-sm"
                   value="{{ old('price_medium', $product->price_medium) }}">
          </div>
          <div class="col-md-4">
            <label class="form-label">{{ __('messages.large') }} {{ __('messages.price') }} ($)</label>
            <input type="number" step="0.01" name="price_large" class="form-control shadow-sm"
                   value="{{ old('price_large', $product->price_large) }}">
          </div>
        </div>

        {{-- Row 4: Product Image (input + preview) --}}
        <div class="row g-4 mt-1">
          <div class="col-md-12">
            <label class="form-label">{{ __('messages.product_image') }}</label>
            <input type="file" name="image" class="form-control shadow-sm">
          </div>

          @if($product->image && file_exists(public_path('storage/'.$product->image)))
            <div class="col-md-12">
              <img src="{{ asset('storage/'.$product->image) }}"
                   alt="Product Image"
                   class="rounded shadow border"
                   style="width: 240px; height: 160px; object-fit: cover;">
            </div>
          @endif
        </div>

        {{-- Row 5: Buttons aligned right like Add Product --}}
        <div class="d-flex justify-content-end gap-2 mt-4">
          <a href="{{ auth()->user()->role === 'superadmin'
                      ? route('superadmin.products.index')
                      : route('admin.products.index') }}"
             class="btn btn-outline-secondary shadow-sm px-4">
            {{ __('messages.back') }}
          </a>
          <button type="submit" class="btn btn-primary shadow-sm px-4">
            {{ __('messages.update') }}
          </button>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection

@push('styles')
<style>
  .text-brown { color:#4E342E; }
  label.form-label{ font-weight:600; color:#4E342E; }
  .form-control:focus, .form-select:focus{
    border-color:#4E342E; box-shadow:0 0 0 0.15rem rgba(78,52,46,.25);
  }
</style>
@endpush

@push('scripts')
<script>
  document.addEventListener('DOMContentLoaded', function () {
    const categorySelect = document.querySelector('select[name="category_id"]');
    const sizeGroup = document.getElementById('size-price-group');
    const foodIds = @json($foodCategoryIds);

    function toggleSizeGroup() {
      const selected = parseInt(categorySelect.value, 10);
      if (foodIds.includes(selected)) {
        sizeGroup.style.display = 'none';
        sizeGroup.querySelectorAll('input').forEach(input => {
          input.disabled = true;
          input.value = '';
        });
      } else {
        sizeGroup.style.display = '';
        sizeGroup.querySelectorAll('input').forEach(input => {
          input.disabled = false;
        });
      }
    }

    categorySelect.addEventListener('change', toggleSizeGroup);
    toggleSizeGroup();
  });
</script>
@endpush
