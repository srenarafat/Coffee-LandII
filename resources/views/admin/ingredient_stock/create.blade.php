@extends('layouts.app')

@section('content')
<div class="container my-4">
    <div class="card shadow-sm">
        <div class="card-body">
            <h5 class="fw-bold mb-4">{{ __('messages.stock_adjustment') }}</h5>

            <form id="ingredient-form"
                  action="{{ auth()->user()->role === 'superadmin'
                        ? route('superadmin.ingredient-stock.store')
                        : route('admin.ingredient-stock.store') }}"
                  method="POST" novalidate>
                @csrf

                @php
                    $preId = old('ingredient_id', request('ingredient_id'));
                    $selected = $preId ? $ingredients->firstWhere('id', (int) $preId) : null;
                @endphp

                {{-- Ingredient (free type or pick from list) --}}
                <div class="mb-2">
                    <label class="form-label">Ingredient</label>
                    <input
                        type="text"
                        id="ingredient-input"
                        name="ingredient_name"
                        class="form-control shadow-sm @error('ingredient_name') is-invalid @enderror @error('ingredient_id') is-invalid @enderror"
                        list="ingredientList"
                        value="{{ old('ingredient_name', $selected->name ?? '') }}"
                        placeholder="Type a name (e.g., Sugar) or pick from list"
                        autocomplete="off"
                        required>
                    <datalist id="ingredientList">
                        @foreach($ingredients as $ingredient)
                            <option value="{{ $ingredient->id }}|{{ $ingredient->name }} (Unit: {{ $ingredient->unit }}, Stock: {{ $ingredient->stock }})"></option>
                        @endforeach
                    </datalist>
                    <input type="hidden" name="ingredient_id" id="ingredient-id" value="{{ $preId }}">

                    {{-- hint + edit toggle --}}
                    <div class="form-text d-flex align-items-center gap-2">
                        <span id="match-hint" class="text-muted"></span>
                        <button type="button" id="edit-existing-btn" class="btn btn-link p-0" style="text-decoration: underline; display:none;">
                            Edit existing
                        </button>
                    </div>

                    @error('ingredient_id')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                    @error('ingredient_name')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Unit (ONE shared input, used for NEW and when EDITING existing) --}}
                <div id="unit-row" class="mb-2" style="display:none;">
                    <label class="form-label">
                        Unit <small id="unit-required-note" class="text-muted">(required for new ingredient)</small>
                    </label>
                    <input type="text"
                           id="unit-input"
                           name="unit"
                           class="form-control shadow-sm @error('unit') is-invalid @enderror"
                           value="{{ old('unit', $selected->unit ?? '') }}"
                           placeholder="kg, g, ml, pcs"
                           pattern="[A-Za-z\s/.\-]+">
                    @error('unit')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Edit existing panel (rename only; use Unit field above if you want to change unit) --}}
                <div id="edit-existing-panel" class="mt-2" style="display:none;">
                    <div class="row g-2">
                        <div class="col-md-6">
                            <label class="form-label">Rename to (optional)</label>
                            <input type="text" name="rename_to" id="rename-to" class="form-control"
                                   placeholder="e.g., Sugar" value="{{ old('rename_to') }}">
                        </div>
                    </div>
                    <input type="hidden" name="edit_existing" id="edit-existing-flag" value="{{ old('edit_existing', '0') }}">
                    <small class="text-muted">Tip: to change unit of this ingredient, use the Unit field above.</small>
                </div>

                {{-- Type --}}
                <div class="mb-2">
                    <label class="form-label">{{ __('messages.type') }}</label>
                    <select name="type"
                            class="form-select shadow-sm @error('type') is-invalid @enderror"
                            required>
                        <option value="in"  {{ old('type', request('type', 'in')) === 'in' ? 'selected' : '' }}>
                            {{ __('messages.stock_in') }}
                        </option>
                        <option value="out" {{ old('type', request('type')) === 'out' ? 'selected' : '' }}>
                            {{ __('messages.stock_out') }}
                        </option>
                    </select>
                    @error('type')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Quantity --}}
                <div class="mb-2">
                    <label class="form-label">{{ __('messages.qty') }}</label>
                    <div class="input-group">
                        <input type="number"
                               name="quantity"
                               class="form-control shadow-sm @error('quantity') is-invalid @enderror"
                               min="0.01" step="0.01"
                               value="{{ old('quantity') }}"
                               required>
                        <span class="input-group-text" id="unit-display">{{ $selected->unit ?? '' }}</span>
                    </div>
                    @error('quantity')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Note --}}
                <div class="mb-2">
                    <label class="form-label">{{ __('messages.note_optional') }}</label>
                    <input type="text"
                           name="note"
                           class="form-control shadow-sm @error('note') is-invalid @enderror"
                           value="{{ old('note') }}">
                    @error('note')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mt-4 text-end">
                    <a href="{{ auth()->user()->role === 'superadmin'
                                ? route('superadmin.ingredient-stock.index')
                                : route('admin.ingredient-stock.index') }}"
                       class="btn btn-outline-secondary shadow-sm me-2">
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
  const input       = document.getElementById('ingredient-input');
  const hidden      = document.getElementById('ingredient-id');
  const list        = document.getElementById('ingredientList');
  const options     = Array.from(list ? list.options : []);
  const unitBadge   = document.getElementById('unit-display');
  const hint        = document.getElementById('match-hint');

  const editBtn     = document.getElementById('edit-existing-btn');
  const editPanel   = document.getElementById('edit-existing-panel');
  const editFlag    = document.getElementById('edit-existing-flag');
  const renameTo    = document.getElementById('rename-to');

  const unitRow     = document.getElementById('unit-row');
  const unitInput   = document.getElementById('unit-input');
  const unitNote    = document.getElementById('unit-required-note');

  function cleanUnit(u) {
    u = (u || '').replace(/^\s*\d+\s*/, '');           // cut leading numbers: "10 kg" -> "kg"
    u = u.replace(/[^A-Za-z\s\/\.\-]/g, '');           // keep letters/space/slash/dot/hyphen
    return u.trim();
  }

  function showUnitRow(show, requiredForNew) {
    unitRow.style.display = show ? 'block' : 'none';
    unitNote.style.display = requiredForNew ? 'inline' : 'none';
  }

  function fillFromOptionValue(val) {
    const [id, displayText] = (val || '').split('|');
    if (!id || !/^\d+$/.test(id)) return false;

    const name = (displayText || '').split(' (')[0].trim();
    hidden.value = id; input.value = name;

    const unitMatch = (displayText || '').match(/Unit:\s*([^,]+)/i);
    const unit = cleanUnit(unitMatch ? unitMatch[1] : '');
    unitBadge.textContent = unit;
    // for existing by default we hide unit input (read only), unless user clicks "Edit existing"
    showUnitRow(false, false);

    editBtn.style.display = 'inline';
    hint.textContent = 'Existing ingredient selected.';
    return true;
  }

  function tryExactName(raw) {
    const lower = raw.trim().toLowerCase();
    const opt = options.find(o => {
      const segs = (o.value || '').split('|');
      const displayText = segs[1] || '';
      const name = displayText.split(' (')[0].trim().toLowerCase();
      return name === lower;
    });
    return opt ? fillFromOptionValue(opt.value) : false;
  }

  function updateSelection() {
    const raw = input.value.trim();
    if (raw.includes('|') && fillFromOptionValue(raw)) return;
    if (raw && tryExactName(raw)) return;

    // new ingredient flow
    hidden.value = '';
    hint.textContent = 'New ingredient will be created.';
    editBtn.style.display = 'none';
    editPanel.style.display = 'none';
    editFlag.value = '0';
    showUnitRow(true, true); // unit is visible & required note shown for NEW
    unitInput.value = cleanUnit(unitInput.value);
    unitBadge.textContent = unitInput.value || '';
  }

  // Edit existing toggler: also reveal unit input to allow change
  editBtn.addEventListener('click', () => {
    const show = editPanel.style.display === 'none';
    editPanel.style.display = show ? 'block' : 'none';
    editFlag.value = show ? '1' : '0';
    showUnitRow(show, false); // show unit input when editing existing
    // prefill the unit input from the badge when opening
    const u = unitBadge.textContent.trim();
    if (show && u && !unitInput.value) unitInput.value = u;
  });

  // Keep badge synced with unit input
  unitInput.addEventListener('input', () => {
    unitInput.value = cleanUnit(unitInput.value);
    unitBadge.textContent = unitInput.value;
  });

  input.addEventListener('input', updateSelection);
  input.addEventListener('change', updateSelection);

  // Initialize state (handles old() cases)
  updateSelection();
  // If we came back with old('edit_existing') = 1, open the panel and show unit
  @if(old('edit_existing') === '1')
    editPanel.style.display = 'block';
    editFlag.value = '1';
    showUnitRow(true, false);
  @endif
});
</script>
@endpush
