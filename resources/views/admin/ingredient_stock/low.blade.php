@extends('layouts.app')

@section('content')
<div class="container my-4">
  <div class="card shadow-sm border-0 rounded-4">
    <div class="card-header d-flex flex-wrap gap-3 justify-content-between align-items-center">
      <div class="d-flex align-items-center gap-2">
        <h5 class="mb-0 fw-bold">Low Ingredient Stock</h5>
        <span class="badge bg-warning-subtle text-warning-emphasis rounded-pill">Threshold: {{ $threshold }}</span>
      </div>

      <div class="d-flex gap-2">
        <input id="lowStockSearch" type="search" class="form-control form-control-sm"
               placeholder="Search ingredient…" style="min-width:220px">
        <a href="{{ route(auth()->user()->role.'.ingredient-stock.low') }}" class="btn btn-sm btn-outline-secondary">Refresh</a>
      </div>
    </div>

    <div class="card-body">
      <div class="table-responsive">
        <table id="lowStockTable" class="table table-bordered table-striped table-hover align-middle mb-0">
          <thead class="sticky-top" style="top:0;z-index:5;background-color:#dbeafe;">
            <tr>
              <th class="text-start" style="width:60%">@lang('messages.ingredient')</th>
              <th class="text-start" style="width:20%">@lang('messages.unit')</th>
              <th class="text-center" style="width:20%">Stock</th>
            </tr>
          </thead>
          <tbody>
            @forelse($ingredients as $ingredient)
              @php
                $stock = (int) ($ingredient->stock ?? 0);
                $pct   = min(100, max(0, round(($stock / max(1,$threshold)) * 100)));
              @endphp
              <tr class="{{ $stock <= 0 ? 'table-danger' : ($stock <= $threshold ? 'table-warning' : '') }}">
                <td class="text-start">
                  <div class="fw-semibold">{{ $ingredient->name }}</div>
                  <div class="progress progress-thin mt-1" title="Remaining vs threshold">
                    <div class="progress-bar {{ $pct<=25?'bg-danger':($pct<=60?'bg-warning':'bg-success') }}" role="progressbar" style="width: {{ $pct }}%" aria-valuenow="{{ $pct }}" aria-valuemin="0" aria-valuemax="100"></div>
                  </div>
                  <small class="text-muted">at {{ $stock }} / {{ $threshold }}</small>
                </td>
                <td class="text-start">{{ $ingredient->unit }}</td>
                <td class="text-center">
                  <span class="badge rounded-pill {{ $stock<=0?'bg-danger':($stock<=$threshold?'bg-warning text-dark':'bg-success') }}">{{ $stock }}</span>
              </tr>
            @empty
              <tr>
                <td colspan="3" class="text-center text-muted py-4">
                  All ingredients have sufficient stock.
                </td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>
      @if(method_exists($ingredients, 'links'))
        <div class="mt-3">{{ $ingredients->links() }}</div>
      @endif
    </div>
  </div>
</div>

@push('styles')
<style>
  .progress-thin { height:6px; background:#f1f5f9; }
  thead.sticky-top th { box-shadow: 0 1px 0 rgba(0,0,0,.05); }
</style>
@endpush

@push('scripts')
<script>
  const search = document.getElementById('lowStockSearch');
  const rows   = () => Array.from(document.querySelectorAll('#lowStockTable tbody tr'));
  search?.addEventListener('input', () => {
    const q = search.value.toLowerCase();
    rows().forEach(tr => {
      const txt = tr.innerText.toLowerCase();
      tr.style.display = txt.includes(q) ? '' : 'none';
    });
  });
</script>
@endpush
@endsection
