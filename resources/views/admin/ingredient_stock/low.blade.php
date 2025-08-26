@extends('layouts.app')

@section('content')
<div class="container my-4">
  <div class="card shadow-sm border-0 rounded-4">

    {{-- Header --}}
<div class="card-header d-flex flex-wrap justify-content-between align-items-center bg-light">
  <h5 class="mb-0 fw-bold text-brown d-flex align-items-center gap-2">
    📦 Low Ingredient Stock
    <span class="badge bg-warning-subtle text-warning-emphasis rounded-pill">
      Threshold: 3
    </span>
  </h5>

  <div class="d-flex gap-2">
    <input id="lowStockSearch" type="search" class="form-control form-control-sm"
           placeholder="Search ingredient…" style="min-width:220px">
    <a href="{{ route(auth()->user()->role.'.ingredient-stock.low') }}"
       class="btn btn-sm btn-outline-secondary">Refresh</a>
  </div>
</div>

    {{-- Body --}}
    <div class="card-body p-0">
      <div class="table-responsive">
        <table id="lowStockTable" class="table table-striped align-middle mb-0">
          <thead class="bg-brown text-white">
            <tr>
              <th class="text-start">Ingredient</th>
              <th class="text-center">Unit</th>
              <th class="text-center">In Stock</th>
              <th class="text-center">Status</th>
              <th class="text-center">Action</th>
            </tr>
          </thead>
          <tbody>
          @forelse($ingredients as $ingredient)
            @php
              $stock   = (float) ($ingredient->stock ?? 0);
              $unitRaw = (string) ($ingredient->unit ?? '');
              $unitKey = strtolower(trim($unitRaw));
              $min     = $unitAlerts[$unitKey] ?? 3;   // default alert threshold
              $isAlert = $stock < $min;
              $fmt     = fn($n) => rtrim(rtrim(number_format($n, 2), '0'), '.');
            @endphp
            <tr class="{{ $isAlert ? 'table-warning' : '' }}">
              <td>{{ $ingredient->name }}</td>
              <td class="text-center">{{ $unitRaw }}</td>
              <td class="text-center">
                <span class="badge rounded-pill {{ $isAlert ? 'bg-danger' : 'bg-warning text-dark' }} current-stock">
                  {{ $fmt($stock) }}
                </span>
              </td>
              <td class="text-center">
                @if($isAlert)
                  <span class="badge bg-danger">ALERT</span>
                @else
                  <span class="badge bg-warning text-dark">Low</span>
                @endif
              </td>
              <td class="text-center">
                <button
                  class="btn btn-sm btn-brown adjust-stock-btn"
                  data-id="{{ $ingredient->id }}"
                  data-name="{{ $ingredient->name }}"
                  data-unit="{{ $ingredient->unit }}">
                  Adjust
                </button>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="5" class="text-center text-muted py-4">✅ All ingredients have sufficient stock.</td>
            </tr>
          @endforelse
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

{{-- Toasts --}}
@if(session('success'))
  <div class="alert alert-success alert-dismissible fade show position-fixed top-0 end-0 m-3 shadow" role="alert" style="z-index:1056">
    {{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
  </div>
@endif
@if(session('info'))
  <div class="alert alert-info alert-dismissible fade show position-fixed top-0 end-0 m-3 shadow" role="alert" style="z-index:1056">
    {{ session('info') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
  </div>
@endif

{{-- Adjust Modal (add amount) --}}
<div class="modal fade" id="adjustStockModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-sm modal-dialog-centered">
    <div class="modal-content border-0 rounded-3 shadow">
      <form id="adjustStockForm" method="POST" action="{{ route(auth()->user()->role.'.ingredient-stock.adjust') }}">
        @csrf
        <input type="hidden" name="id" id="adjustStockId">
        <input type="hidden" name="only_increase" value="1">

        <div class="modal-header bg-brown text-white py-2">
          <h6 class="modal-title fw-semibold">Adjust Stock</h6>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>

        <div class="modal-body p-3">
          <div class="mb-2">
            <label class="form-label small mb-1">Ingredient</label>
            <input type="text" id="adjustStockName" class="form-control form-control-sm" readonly>
          </div>

          <div class="mb-2">
            <label class="form-label small mb-1">in Stock</label>
            <input type="text" id="adjustStockCurrent" class="form-control form-control-sm" readonly>
          </div>

          <div class="mb-2">
            <label class="form-label small mb-1">Add Quantity</label>
            <input type="number"
                   name="stock"
                   id="adjustStockAdd"
                   class="form-control form-control-sm"
                   step="0.01"
                   min="0.01"
                   placeholder="e.g. 2">
          </div>

          <div class="mb-2">
            <label class="form-label small mb-1">Note</label>
            <textarea name="note" rows="2" class="form-control form-control-sm"
                      placeholder="E.g. Delivery, Wastage, Manual adjustment…"></textarea>
          </div>
        </div>

        <div class="modal-footer py-2">
          <button type="submit" class="btn btn-brown btn-sm px-3">Save</button>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection

@push('styles')
<style>
  .text-brown { color:#6f4e37; }
  .btn-brown { background:#6f4e37; color:#fff; }
  .btn-brown:hover { background:#5c3f2e; color:#fff; }
  thead th { font-weight:600; }
</style>
@endpush

@push('scripts')
<script>
  // Search filter
  const search = document.getElementById('lowStockSearch');
  const rows   = () => Array.from(document.querySelectorAll('#lowStockTable tbody tr'));
  search?.addEventListener('input', () => {
    const q = search.value.toLowerCase();
    rows().forEach(tr => {
      const txt = tr.innerText.toLowerCase();
      tr.style.display = txt.includes(q) ? '' : 'none';
    });
  });

  // Open Adjust modal
  document.querySelectorAll('.adjust-stock-btn').forEach(btn => {
    btn.addEventListener('click', () => {
      const id   = btn.dataset.id;
      const name = btn.dataset.name;
      const unit = btn.dataset.unit;

      const stockText = btn.closest('tr').querySelector('.current-stock')?.textContent ?? '0';
      const current   = parseFloat(stockText.replace(/[^0-9.\-]/g, '') || '0');

      document.getElementById('adjustStockId').value      = id;
      document.getElementById('adjustStockName').value    = `${name} (${unit})`;
      document.getElementById('adjustStockCurrent').value = `${current} ${unit}`;
      document.getElementById('adjustStockAdd').value = '';

      new bootstrap.Modal(document.getElementById('adjustStockModal')).show();
    });
  });

  // AJAX submit
  document.getElementById('adjustStockForm')?.addEventListener('submit', async (e) => {
    e.preventDefault();
    const form   = e.target;
    const saveEl = form.querySelector('button[type="submit"]');
    saveEl.disabled = true;

    try {
      const res = await fetch(form.action, {
        method: 'POST',
        body: new FormData(form),
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
      });

      if (res.status === 422) {
        const data = await res.json().catch(() => ({}));
        alert(data?.error || 'Please enter a positive amount to add.');
        return;
      }

      let ok = false;
      try {
        const data = await res.json();
        ok = !!data.ok;
      } catch (_) {
        ok = res.ok;
      }

      if (ok) window.location.reload();
    } catch (err) {
      console.error(err);
    } finally {
      saveEl.disabled = false;
    }
  });
</script>
@endpush
