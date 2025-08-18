@extends('layouts.app')

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

                    <!-- Price -->
                    <div class="col-md-6">
                        <label class="form-label">{{ __('messages.price') }} ($)</label>
                        <input type="number" name="price" class="form-control shadow-sm" placeholder="e.g. 2.50" step="0.01" value="{{ old('price') }}" required>
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
</script>
@endpush
