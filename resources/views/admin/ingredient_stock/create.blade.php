@extends('layouts.app')
@section('content')
<div class="container my-4">
    <div class="card shadow-sm">
        <div class="card-body">
            <h5 class="fw-bold mb-4">{{ __('messages.stock_adjustment') }}</h5>
            <form action="{{ auth()->user()->role === 'superadmin' ? route('superadmin.ingredient-stock.store') : route('admin.ingredient-stock.store') }}" method="POST">
                @csrf
                <div class="mb-3">
                    <label class="form-label">Ingredient</label>
                    <select name="ingredient_id" class="form-select shadow-sm" required>
                        @foreach($ingredients as $ingredient)
                            <option value="{{ $ingredient->id }}" {{ request('ingredient_id') == $ingredient->id ? 'selected' : '' }}>
                                {{ $ingredient->name }} (Unit: {{ $ingredient->unit }})
                            </option>
                        @endforeach
                    </select>
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