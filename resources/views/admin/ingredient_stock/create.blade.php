@extends('layouts.app')
@section('content')
<div class="container my-4">
    <div class="card shadow-sm">
        <div class="card-body">
            <h5 class="fw-bold mb-4">{{ __('messages.stock_adjustment') }}</h5>
            <form id="ingredient-form" action="{{ auth()->user()->role === 'superadmin' ? route('superadmin.ingredient-stock.store') : route('admin.ingredient-stock.store') }}" method="POST">
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
                            <option value="{{ $ingredient->id }}|{{ $ingredient->name }} (Unit: {{ $ingredient->unit }}, Stock: {{ $ingredient->stock }})"></option>
                        @endforeach
                    </datalist>
                    <input type="hidden" name="ingredient_id" id="ingredient-id" value="{{ request('ingredient_id') }}">
                </div>
                @error('ingredient_id')
                    <div class="text-danger small">{{ $message }}</div>
                @enderror
                <div class="mb-3">
                    <label class="form-label">{{ __('messages.type') }}</label>
                    <select name="type" class="form-select shadow-sm" required>
                        <option value="in" {{ request('type', 'in') === 'in' ? 'selected' : '' }}>{{ __('messages.stock_in') }}</option>
                        <option value="out" {{ request('type') === 'out' ? 'selected' : '' }}>{{ __('messages.stock_out') }}</option>
                    </select>
                </div>
                @error('type')
                    <div class="text-danger small">{{ $message }}</div>
                @enderror
                <div class="mb-3">
                    <label class="form-label">{{ __('messages.qty') }}</label>
                    <div class="input-group">
                        <input type="number" name="quantity" class="form-control shadow-sm" min="0.01" step="0.01" required>
                        <span class="input-group-text" id="unit-display">{{ $selected->unit ?? '' }}</span>
                    </div>
                </div>
                @error('quantity')
                    <div class="text-danger small">{{ $message }}</div>
                @enderror
                <div class="mb-3">
                    <label class="form-label">{{ __('messages.note_optional') }}</label>
                    <input type="text" name="note" class="form-control shadow-sm">
                </div>
                @error('note')
                    <div class="text-danger small">{{ $message }}</div>
                @enderror
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
        const unitDisplay = document.getElementById('unit-display');
        const form = document.getElementById('ingredient-form');

        function updateSelection() {
            const rawValue = input.value.trim();
            const separator = rawValue.indexOf('|');

            if (separator !== -1) {
                const left = rawValue.slice(0, separator).trim();
                const right = rawValue.slice(separator + 1).trim();

                if (/^\d+$/.test(left)) {
                    hidden.value = left;
                    input.value = right;
                    const unitMatch = right.match(/Unit:\s*([^,]+)/i);
                    unitDisplay.textContent = unitMatch ? unitMatch[1] : '';
                    return;
                }
            }

            const lower = rawValue.toLowerCase();
            const match = options.find(o => {
                const [id, displayText] = o.value.split('|');
                const name = displayText.split(' (')[0].toLowerCase();
                return o.value.toLowerCase() === lower || displayText.toLowerCase() === lower || id === lower || name.includes(lower);
            });

            if (match) {
                const [id, displayText] = match.value.split('|');
                hidden.value = id;
                const unitMatch = displayText.match(/Unit:\s*([^,]+)/i);
                unitDisplay.textContent = unitMatch ? unitMatch[1] : '';
                if (rawValue !== displayText) {
                    input.value = displayText;
                }
            } else {
                hidden.value = '';
                unitDisplay.textContent = '';
            }
        }

        input.addEventListener('input', updateSelection);
        input.addEventListener('change', updateSelection);
        input.addEventListener('blur', updateSelection);
        form.addEventListener('submit', function(e) {
            updateSelection();
            if (!hidden.value) {
                e.preventDefault();
                alert('Please select a valid ingredient.');
            }
        });

        updateSelection();
    });
</script>

@endpush