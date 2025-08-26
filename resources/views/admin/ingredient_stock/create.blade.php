@extends('layouts.app')

@section('content')
<div class="container my-4">
  <div class="card shadow-sm border-0 rounded-4">
    <div class="card-body p-3 p-lg-4">
      <h5 class="fw-bold mb-3">{{ __('messages.stock_adjustment') }}</h5>

      <form id="ingredient-form"
            action="{{ auth()->user()->role === 'superadmin'
                      ? route('superadmin.ingredient-stock.store')
                      : route('admin.ingredient-stock.store') }}"
            method="POST" novalidate>
        @csrf

        @php
          $preId    = old('ingredient_id', request('ingredient_id'));
          $selected = $preId ? $ingredients->firstWhere('id', (int) $preId) : null;
        @endphp

        <div class="row g-3 align-items-start">
          <!-- Left: form controls -->
          <div class="col-12 col-lg-8">
            {{-- Ingredient search/select --}}
            <div class="mb-2">
              <label class="form-label">{{ __('messages.ingredient') }}</label>

              <div class="position-relative">
                <input
                  type="text"
                  id="ingredient-input"
                  name="ingredient_name"
                  class="form-control form-control-lg shadow-sm @error('ingredient_name') is-invalid @enderror @error('ingredient_id') is-invalid @enderror"
                  value="{{ old('ingredient_name', $selected->name ?? '') }}"
                  placeholder="{{ __('messages.ingredient_placeholder') }}"
                  autocomplete="off" required>
                <input type="hidden" name="ingredient_id" id="ingredient-id" value="{{ $preId }}">

                {{-- dropdown results --}}
                <div id="ingredient-results" class="list-group shadow-sm" style="position:absolute; z-index:20; top:100%; left:0; right:0; display:none; max-height:260px; overflow:auto;"></div>
              </div>

              {{-- hint + edit toggle --}}
              <div class="d-flex align-items-center justify-content-between mt-1">
                <small id="match-hint" class="text-muted"></small>
                <button type="button" id="edit-existing-btn" class="btn btn-link btn-sm p-0" style="display:none;">
                  {{ __('messages.edit_existing') }}
                </button>
              </div>

              @error('ingredient_id') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
              @error('ingredient_name') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
            </div>

            {{-- Unit (shared for NEW + when editing existing) --}}
            <div id="unit-row" class="mb-2" style="display:none;">
              <label class="form-label mb-1">
                {{ __('messages.unit') }}
                <small id="unit-required-note" class="text-muted">(required for new ingredient)</small>
              </label>
              <input type="text"
                     id="unit-input"
                     name="unit"
                     class="form-control shadow-sm @error('unit') is-invalid @enderror"
                     value="{{ old('unit', $selected->unit ?? '') }}"
                     placeholder="kg, g, ml, pcs"
                     pattern="[A-Za-z\s/.\-]+">
              @error('unit') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
            </div>

            {{-- Edit existing: rename (optional). Use Unit field above if changing unit --}}
            <div id="edit-existing-panel" class="mt-1" style="display:none;">
              <div class="row g-2">
                <div class="col-md-7">
                  <label class="form-label">{{ __('messages.rename_to_optional') }}</label>
                  <input type="text" name="rename_to" id="rename-to" class="form-control" placeholder="e.g., Sugar" value="{{ old('rename_to') }}">
                </div>
              </div>
              <input type="hidden" name="edit_existing" id="edit-existing-flag" value="{{ old('edit_existing', '0') }}">
              <small class="text-muted">{{ __('messages.tip_change_unit') }}</small>
            </div>

            {{-- Type --}}
            <div class="mb-2 mt-1">
              <label class="form-label">{{ __('messages.type') }}</label>
              <select name="type" class="form-select shadow-sm @error('type') is-invalid @enderror" required>
                <option value="in"  {{ old('type', request('type', 'in')) === 'in' ? 'selected' : '' }}>
                  {{ __('messages.stock_in') }}
                </option>
                <option value="out" {{ old('type', request('type')) === 'out' ? 'selected' : '' }}>
                  {{ __('messages.stock_out') }}
                </option>
              </select>
              @error('type') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
            </div>

            {{-- Quantity --}}
            <div class="mb-2">
              <label class="form-label">{{ __('messages.qty') }}</label>
              <div class="input-group">
                <button class="btn btn-outline-secondary" type="button" id="qty-minus" tabindex="-1">−</button>
                <input type="number"
                       name="quantity"
                       id="quantity"
                       class="form-control text-center shadow-sm @error('quantity') is-invalid @enderror"
                       min="0.01" step="0.01"
                       value="{{ old('quantity') }}"
                       required>
                <button class="btn btn-outline-secondary" type="button" id="qty-plus" tabindex="-1">+</button>
                <span class="input-group-text" id="unit-badge">{{ $selected->unit ?? '' }}</span>
              </div>
              <small class="text-muted" id="qty-helper"></small>
              @error('quantity') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
            </div>

            {{-- Note --}}
            <div class="mb-2">
              <label class="form-label">{{ __('messages.note_optional') }}</label>
              <input type="text" name="note" class="form-control shadow-sm @error('note') is-invalid @enderror" value="{{ old('note') }}">
              @error('note') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
            </div>

            <div class="mt-3 text-end">
              <a href="{{ auth()->user()->role === 'superadmin'
                          ? route('superadmin.ingredient-stock.index')
                          : route('admin.ingredient-stock.index') }}"
                 class="btn btn-light border shadow-sm me-2">
                <i class="bi bi-arrow-left"></i> {{ __('messages.back') }}
              </a>
              <button type="submit" class="btn btn-primary shadow-sm">
                <i class="bi bi-save"></i> {{ __('messages.save') }}
              </button>
            </div>
          </div>

          <!-- Right: compact info card -->
          <div class="col-12 col-lg-4">
            <div class="card border-0 shadow-sm">
              <div class="card-body">
                <div class="d-flex align-items-center justify-content-between mb-2">
                  <h6 class="mb-0">{{ __('messages.ingredient') }}</h6>
                  <span id="stock-chip" class="badge bg-secondary">—</span>
                </div>
                <div class="small text-muted mb-2">
                  {{ __('messages.unit') }}:
                  <span id="unit-chip" class="fw-semibold">—</span>
                </div>
                <div id="low-stock-alert" class="alert alert-danger py-1 px-2 small" style="display:none;">
                  ⚠️ {{ __('messages.low_stock') }}
                </div>
                <div class="small text-muted">
                  {{ __('messages.tip') }}:
                  <span class="text-secondary">
                    {{ __('messages.search_type_to_filter') }}
                  </span>
                </div>
              </div>
            </div>
          </div>
        </div> <!-- /row -->
      </form>
    </div>
  </div>
</div>
@endsection

@push('styles')
<style>
  /* compact list item */
  .result-item{display:flex;align-items:center;gap:.5rem}
  .result-name{flex:1;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
  .result-meta{font-size:.8rem;opacity:.75}
  #ingredient-results .list-group-item{cursor:pointer}
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
  // ===== Data =====
  const RAW = @json(
    $ingredients->map(function ($i) {
        return [
            'id'    => $i->id,
            'name'  => $i->name,
            'unit'  => $i->unit,
            'stock' => (float) $i->stock
        ];
    })
);

  const LOW_STOCK_THRESHOLD = 3; // change here easily

  // ===== Elements =====
  const input      = document.getElementById('ingredient-input');
  const hiddenId   = document.getElementById('ingredient-id');
  const resultsBox = document.getElementById('ingredient-results');

  const hint       = document.getElementById('match-hint');
  const editBtn    = document.getElementById('edit-existing-btn');
  const editPanel  = document.getElementById('edit-existing-panel');
  const editFlag   = document.getElementById('edit-existing-flag');
  const renameTo   = document.getElementById('rename-to');

  const unitRow    = document.getElementById('unit-row');
  const unitInput  = document.getElementById('unit-input');
  const unitNote   = document.getElementById('unit-required-note');

  const unitBadge  = document.getElementById('unit-badge');
  const unitChip   = document.getElementById('unit-chip');
  const stockChip  = document.getElementById('stock-chip');
  const lowAlert   = document.getElementById('low-stock-alert');

  const qty        = document.getElementById('quantity');
  const qtyMinus   = document.getElementById('qty-minus');
  const qtyPlus    = document.getElementById('qty-plus');
  const qtyHelper  = document.getElementById('qty-helper');

  function fmtStock(v){
    // UI: drop trailing .00 (show integers neatly)
    const n = Number(v);
    return Number.isInteger(n) ? String(n) : n.toFixed(2).replace(/\.00$/,'');
  }

  function cleanUnit(u){
    u = (u || '').replace(/^\s*\d+\s*/, '');
    u = u.replace(/[^A-Za-z\s\/.\-]/g, '');
    return u.trim();
  }

  function showUnitRow(show, requiredNote){
    unitRow.style.display = show ? 'block' : 'none';
    unitNote.style.display = requiredNote ? 'inline' : 'none';
  }

  function updateInfoCard(unit, stock){
    unitBadge.textContent = unit || '';
    unitChip.textContent  = unit || '—';
    stockChip.textContent = (stock ?? '') !== '' ? ('Stock: ' + fmtStock(stock)) : '—';
    if (typeof stock === 'number' && stock < LOW_STOCK_THRESHOLD){
      lowAlert.style.display = 'block';
    } else {
      lowAlert.style.display = 'none';
    }
  }

  function pick(i){
    hiddenId.value   = i.id;
    input.value      = i.name;
    hint.textContent = 'Existing ingredient selected.';
    editBtn.style.display = 'inline';

    showUnitRow(false,false);
    updateInfoCard(i.unit, i.stock);
    closeResults();
  }

  function createNewFlow(){
    hiddenId.value = '';
    hint.textContent = 'New ingredient will be created.';
    editBtn.style.display = 'none';
    editPanel.style.display = 'none';
    editFlag && (editFlag.value = '0');

    // show unit input (required)
    showUnitRow(true, true);
    unitInput.value = cleanUnit(unitInput.value);
    updateInfoCard(unitInput.value, '');
  }

  function openResults(items){
    resultsBox.innerHTML = '';
    items.slice(0, 50).forEach(i => {
      const a = document.createElement('a');
      a.className = 'list-group-item list-group-item-action';
      a.innerHTML = `
        <div class="result-item">
          <div class="result-name fw-semibold">${i.name}</div>
          <div class="result-meta">Unit: ${i.unit || '-'} • Stock: ${fmtStock(i.stock ?? 0)}</div>
        </div>`;
      a.addEventListener('click', () => pick(i));
      resultsBox.appendChild(a);
    });
    resultsBox.style.display = items.length ? 'block' : 'none';
  }

  function closeResults(){ resultsBox.style.display = 'none'; }

  function filter(q){
    q = (q||'').trim().toLowerCase();
    if(!q) return RAW;
    return RAW.filter(i => i.name.toLowerCase().includes(q));
  }

  function handleInput(){
    const q = input.value;
    const matches = filter(q);
    // exact match? auto-pick
    const exact = matches.find(m => m.name.toLowerCase() === q.trim().toLowerCase());
    if (exact){
      pick(exact);
      return;
    }
    // else new flow + show suggestions
    createNewFlow();
    openResults(matches);
  }

  // Edit existing toggler
  editBtn.addEventListener('click', () => {
    const open = editPanel.style.display === 'none';
    editPanel.style.display = open ? 'block' : 'none';
    if (editFlag) editFlag.value = open ? '1' : '0';
    showUnitRow(open, false);
    if (open && unitChip.textContent && !unitInput.value){
      unitInput.value = unitChip.textContent.trim();
    }
  });

  // Unit input keeps chips in sync
  unitInput && unitInput.addEventListener('input', () => {
    unitInput.value = cleanUnit(unitInput.value);
    updateInfoCard(unitInput.value, '');
  });

  // Qty helpers
  qtyMinus.addEventListener('click', () => {
    const cur = parseFloat(qty.value || '0') || 0;
    let next = Math.max(0, (cur - 1));
    qty.value = next.toFixed(2).replace(/\.00$/,'');
  });
  qtyPlus.addEventListener('click', () => {
    const cur = parseFloat(qty.value || '0') || 0;
    let next = cur + 1;
    qty.value = next.toFixed(2).replace(/\.00$/,'');
  });
  qty.addEventListener('input', () => {
    // show unit under qty
    qtyHelper.textContent = unitBadge.textContent ? `× ${unitBadge.textContent}` : '';
  });

  // Input events
  input.addEventListener('input', handleInput);
  input.addEventListener('focus', () => openResults(filter(input.value)));
  document.addEventListener('click', (e) => {
    if (!resultsBox.contains(e.target) && e.target !== input) closeResults();
  });

  // ===== Init from old() / preset =====
  const presetId = Number("{{ $preId ?: 0 }}");
  if (presetId){
    const found = RAW.find(i => i.id === presetId);
    if (found){ pick(found); }
  } else {
    createNewFlow();
  }

  @if(old('edit_existing') === '1')
    editPanel.style.display = 'block';
    if (editFlag) editFlag.value = '1';
    showUnitRow(true, false);
  @endif
});
</script>
@endpush
