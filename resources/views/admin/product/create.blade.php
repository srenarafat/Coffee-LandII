@extends('layouts.app')

@php
    $foodCategory = \App\Models\Category::where('name', 'Food')->first();
    $foodCategoryIds = $foodCategory ? \App\Models\Category::descendantsAndSelfIds($foodCategory->id) : [];
@endphp

@section('content')
<div class="container-fluid mt-4">
    <div class="card shadow-sm">
        <div class="card-body">
            <h4 class="fw-bold mb-4 text-brown"> {{ __('messages.add_product') }}</h4>

            <form action="{{ auth()->user()->role === 'superadmin' ? route('superadmin.products.store') : route('admin.products.store') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <div class="row g-4">
                    <div class="col-md-6">
                        <label class="form-label">{{ __('messages.category') }}</label>
                        <select name="category_id" class="form-control shadow-sm" required>
                            @foreach($categoryOptions as $id => $name)
                                <option value="{{ $id }}">{{ $name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Product Name -->
                    <div class="col-md-6">
                        <label class="form-label">{{ __('messages.product_name') }}</label>
                        <input type="text" name="name" class="form-control shadow-sm" placeholder="e.g. Latte" value="{{ old('name') }}" required>
                    </div>

                    <!-- Import Price -->
                    <div class="col-md-6">
                        <label class="form-label">{{ __('messages.import_price') }} ($)</label>
                        <input type="number" name="import_price" class="form-control shadow-sm" step="0.01" value="{{ old('import_price') }}">
                    </div>
                    
                    <!-- Base Price -->
                    <div class="col-md-6">
                        <label class="form-label">{{ __('messages.price') }} ($)</label>
                        <input type="number" name="price" class="form-control shadow-sm" placeholder="e.g. 2.50" step="0.01" value="{{ old('price') }}" required>
                    </div>

                    

                    <div id="size-price-group" class="col-12">
                        <div class="row g-4">
                            <div class="col-md-4">
                                <label class="form-label">{{ __('messages.small') }} {{ __('messages.price') }} ($)</label>
                                <input type="number" name="price_small" class="form-control shadow-sm" step="0.01" value="{{ old('price_small') }}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">{{ __('messages.medium') }} {{ __('messages.price') }} ($)</label>
                                <input type="number" name="price_medium" class="form-control shadow-sm" step="0.01" value="{{ old('price_medium') }}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">{{ __('messages.large') }} {{ __('messages.price') }} ($)</label>
                                <input type="number" name="price_large" class="form-control shadow-sm" step="0.01" value="{{ old('price_large') }}">
                            </div>
                        </div>
                    </div>

                    <!-- Product Image -->
                    <div class="col-md-6">
                        <label class="form-label">{{ __('messages.product_image') }}</label>
                        <input type="file" name="image" class="form-control shadow-sm" accept="image/*" onchange="previewImage(event)">
                        <div class="mt-2" id="imagePreview" style="display: none;">
                            <img id="preview" src="#" class="rounded shadow-sm" width="100" alt="Preview">
                        </div>
                    </div>
                </div>

                <!-- Buttons -->
                <!-- Buttons Right Aligned -->
                <div class="mt-4 text-end">
                    <a href="{{ auth()->user()->role === 'superadmin' ? route('superadmin.products.index') : route('admin.products.index') }}" class="btn btn-outline-secondary shadow-sm me-2">
                         {{ __('messages.back') }}
                    </a>
                    <button type="submit" class="btn btn-primary shadow-sm">
                         {{ __('messages.save') }}
                    </button>
                </div>

            </form>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .text-brown {
        color: #4E342E;
    }

    .form-control:focus {
        border-color: #4E342E;
        box-shadow: 0 0 0 0.15rem rgba(78, 52, 46, 0.25);
    }

    #preview {
        border: 1px solid #ddd;
        padding: 4px;
        background: #f9f9f9;
    }
</style>
@endpush

@push('scripts')
<script>
    function previewImage(event) {
        const input = event.target;
        const reader = new FileReader();
        reader.onload = function(){
            const output = document.getElementById('preview');
            output.src = reader.result;
            document.getElementById('imagePreview').style.display = 'block';
        };
        if(input.files[0]){
            reader.readAsDataURL(input.files[0]);
        }
    }
    
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
