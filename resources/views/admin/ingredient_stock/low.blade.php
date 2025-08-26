@extends('layouts.app')

@section('content')
<div class="container my-4">
  <div class="card shadow-sm border-0 rounded-4">
    {{-- header --}}
    <div class="card-header d-flex flex-wrap gap-3 justify-content-between align-items-center bg-light">
      <div class="d-flex align-items-center gap-2">
        <h5 class="mb-0 fw-bold text-brown">📦 Low Ingredient Stock</h5>
        <span class="badge bg-warning-subtle text-warning-emphasis rounded-pill">
          Threshold: {{ $threshold }}
        </span>
      </div>

      <div class="d-flex gap-2">
        <input id="lowStockSearch" type="search" class="form-control form-control-sm"
               placeholder="Search ingredient…" style="min-width:220px">
        <a href="{{ route(auth()->user()->role.'.ingredient-stock.low') }}" class="btn btn-sm btn-outline-secondary">
          Refresh
        </a>
      </div>
    </div>

    {{-- body --}}
    <div class="card-body p-0">
      <div class="table-responsive">
        <table id="lowStockTable" class="table table-hover align-middle mb-0">
          <thead class="sticky-top bg-brown text-white" style="top:0;z-index:5;">
            <tr>
              <th class="text-start" style="width:40%">Ingredient</th>
              <th class="text-center" style="width:15%">Unit</th>
              <th class="text-center" style="width:15%">Stock</th>
              <th class="text-center" style="width:15%">Status</th>
              <th class="text-center" style="width:15%">Action</th>
            </tr>
          </thead>
          <tbody>
            @forelse($ingredients as $ingredient)
              @php
                $stock = (int) ($ingredient->stock ?? 0);
                $pct   = min(100, max(0, round(($stock / max(1,$threshold)) * 100)));
              @endphp
              <tr>
                {{-- ingredient name + progress --}}
                <td>
                  <div class="fw-semibold">{{ $ingredient->name }}</div>
                  <div class="progress progress-thin mt-1">
                    <div class="progress-bar {{ $pct<=25?'bg-danger':($pct<=60?'bg-warning':'bg-success') }}"
                         role="progressbar" style="width: {{ $pct }}%" aria-valuenow="{{ $pct }}"
                         aria-valuemin="0" aria-valuemax="100"></div>
                  </div>
                  <small class="text-muted">at {{ $stock }} / {{ $threshold }}</small>
                </td>

                {{-- unit --}}
                <td class="text-center">{{ $ingredient->unit }}</td>

                {{-- stock count --}}
                <td class="text-center">
                  <span class="badge rounded-pill {{ $stock<=0?'bg-danger':($stock<=$threshold?'bg-warning text-dark':'bg-success') }}">
                    {{ $stock }}
                  </span>
                </td>

                {{-- status --}}
                <td class="text-center">
                  @if($stock <= 0)
                    <span class="badge bg-danger">Out</span>
                  @elseif($stock <= $threshold)
                    <span class="badge bg-warning text-dark">Low</span>
                  @else
                    <span class="badge bg-success">OK</span>
                  @endif
                </td>

                {{-- action --}}
                <td class="text-center">
                  <button class="btn btn-sm btn-brown adjust-stock-btn"
                          data-id="{{ $ingredient->id }}"
                          data-name="{{ $ingredient->name }}"
                          data-unit="{{ $ingredient->unit }}"
                          data-stock="{{ $stock }}">
                    Adjust
                  </button>
                </td>
              </tr>
            @empty
              <tr>
                <td colspan="5" class="text-center text-muted py-4">
                  ✅ All ingredients have sufficient stock.
                </td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>
      @if(method_exists($ingredients, 'links'))
        <div class="mt-3 px-3">{{ $ingredients->links() }}</div>
      @endif
    </div>
  </div>
</div>

{{-- Stock Adjustment Modal --}}
<div class="modal fade" id="adjustStockModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-sm modal-dialog-centered">
    <div class="modal-content border-0 rounded-3 shadow">
      <form id="adjustStockForm" method="POST" action="{{ route(auth()->user()->role.'.ingredient-stock.adjust') }}">
        @csrf
        <input type="hidden" name="id" id="adjustStockId">

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
            <label class="form-label small mb-1">Current Stock</label>
            <input type="text" id="adjustStockCurrent" class="form-control form-control-sm" readonly>
          </div>
          <div class="mb-2">
            <label class="form-label small mb-1">New Stock</label>
            <input type="number" name="stock" class="form-control form-control-sm" required>
            <small class="text-muted">Enter the new stock amount</small>
          </div>
        </div>

        <div class="modal-footer py-2">
          <button type="submit" class="btn btn-brown btn-sm px-3">Save</button>
        </div>
      </form>
    </div>
  </div>
</div>

@push('styles')
<style>
  .text-brown { color:#6f4e37; }
  .btn-brown { background:#6f4e37; color:#fff; }
  .btn-brown:hover { background:#5c3f2e; color:#fff; }
  .progress-thin { height:6px; background:#f1f5f9; }
</style>
@endpush

@push('scripts')
<script>
  // search filter
  const search = document.getElementById('lowStockSearch');
  const rows   = () => Array.from(document.querySelectorAll('#lowStockTable tbody tr'));
  search?.addEventListener('input', () => {
    const q = search.value.toLowerCase();
    rows().forEach(tr => {
      const txt = tr.innerText.toLowerCase();
      tr.style.display = txt.includes(q) ? '' : 'none';
    });
  });

  // adjust stock modal
  document.querySelectorAll('.adjust-stock-btn').forEach(btn=>{
    btn.addEventListener('click', ()=>{
      const id   = btn.dataset.id;
      const name = btn.dataset.name;
      const unit = btn.dataset.unit;
      const stock= btn.dataset.stock;

      document.getElementById('adjustStockId').value   = id;
      document.getElementById('adjustStockName').value = name+' ('+unit+')';
      document.getElementById('adjustStockCurrent').value = stock+' '+unit;

      new bootstrap.Modal(document.getElementById('adjustStockModal')).show();
    });
  });
</script>
@endpush
@endsection
