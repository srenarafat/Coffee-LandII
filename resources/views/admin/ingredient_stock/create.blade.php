@extends('layouts.app')
@section('content')
<div class="container my-4">
    <div class="card shadow-sm">
        <div class="card-body">
            <h5 class="fw-bold mb-4">{{ __('messages.stock_adjustment') }}</h5>
            <form action="{{ auth()->user()->role === 'superadmin' ? route('superadmin.ingredient-stock.store') : route('admin.ingredient-stock.store') }}" method="POST">
                @csrf
                @php
                    $selected = $ingredients->firstWhere('id', request('ingredient_id'));
                    $selectedDisplay = $selected ? $selected->name . ' (Unit: ' . $selected->unit . ', Stock: ' . $selected->stock . ')' : '';
                @endphp
                <div class="mb-3">
                    <label class="form-label">Ingredient</label>
                    <input type="text" id="ingredient-input" class="form-control shadow-sm" list="ingredientList" value="{{ $selectedDisplay }}" placeholder="Start typing..." required>
                    <datalist id="ingredientList">
                        @foreach($ingredients as $ingredient)
                            <option data-id="{{ $ingredient->id }}" value="{{ $ingredient->name }} (Unit: {{ $ingredient->unit }}, Stock: {{ $ingredient->stock }})"></option>
                        @endforeach
                    </datalist>
                    <input type="hidden" name="ingredient_id" id="ingredient-id" value="{{ request('ingredient_id') }}">
                </div>
                <div class="mb-3">
                    <label class="form-label">{{ __('messages.type') }}</label>
                    <select name="type" class="form-select shadow-sm" required>
                        <option value="in" {{ request('type', 'in') === 'in' ? 'selected' : '' }}>{{ __('messages.stock_in') }}</option>
                        <option value="out" {{ request('type') === 'out' ? 'selected' : '' }}>{{ __('messages.stock_out') }}</option>
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
                    <a href="{{ auth()->user()->role === 'superadmin' ? route('superadmin.ingredient-stock.index') : route('admin.ingredient-stock.index') }}" class="btn btn-outline-secondary shadow-sm me-2">
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

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const input = document.getElementById('ingredient-input');
        const hidden = document.getElementById('ingredient-id');
        const options = Array.from(document.getElementById('ingredientList').options);

        input.addEventListener('input', function() {
            const match = options.find(o => o.value === this.value);
            hidden.value = match ? match.dataset.id : '';
        });
    });
</script>
@endpush