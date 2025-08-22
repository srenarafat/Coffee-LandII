@extends('layouts.app')

@section('content')
<div class="container my-4">
    <div class="card shadow-sm border-0 rounded-4 animate__animated">

        @php
            $isSuper = auth()->user()->role === 'superadmin';
            $productIndex    = $isSuper ? route('superadmin.stock-logs.index')       : route('admin.stock-logs.index');
            $ingredientIndex = $isSuper ? route('superadmin.ingredient-stock.index') : route('admin.ingredient-stock.index');
            $onProducts = request()->routeIs('superadmin.stock-logs.*') || request()->routeIs('admin.stock-logs.*');
        @endphp

        <div class="card-header d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center gap-3 flex-wrap">
                <h5 class="mb-0 fw-bold">🥕 Ingredient Stock History</h5>

                <div class="btn-group btn-group-sm btn-switch" role="group" aria-label="Switch section">
                    <a href="{{ $productIndex }}"
                       class="btn {{ $onProducts ? 'btn-primary text-white' : 'btn-outline-secondary' }}"
                       data-bs-toggle="tooltip" title="Product stock history">
                        <i class="bi bi-box-seam me-1"></i> Products
                    </a>
                    <a href="{{ $ingredientIndex }}"
                       class="btn {{ $onProducts ? 'btn-outline-secondary' : 'btn-primary text-white' }}"
                       data-bs-toggle="tooltip" title="Ingredient stock ledger">
                        <span class="me-1" aria-hidden="true">🥕</span> Ingredients
                    </a>
                </div>
            </div>

            <div class="d-flex gap-2">
                <a href="{{ $isSuper
                            ? route('superadmin.ingredient-stock.export', request()->all())
                            : route('admin.ingredient-stock.export', request()->all()) }}"
                   class="btn btn-outline-success btn-sm">
                    <i class="bi bi-filetype-csv me-1"></i>{{ __('messages.export_csv') }}
                </a>

                <a href="{{ $isSuper
                            ? route('superadmin.ingredient-stock.pdf', request()->all())
                            : route('admin.ingredient-stock.pdf', request()->all()) }}"
                   class="btn btn-outline-primary btn-sm">
                    <i class="bi bi-printer me-1"></i>{{ __('messages.print') }}
                </a>

                <a href="{{ $isSuper ? route('superadmin.ingredient-stock.create') : route('admin.ingredient-stock.create') }}"
                   class="btn btn-primary btn-sm">
                    <i class="bi bi-plus-lg me-1"></i>{{ __('messages.stock_adjustment') }}
                </a>
            </div>
        </div>

        <div class="card-body position-relative">
            @if(session('success'))
                <div id="successToast"
                     class="alert alert-success d-flex align-items-center justify-content-between shadow-sm px-4 py-2 animate__animated animate__fadeInDown"
                     style="position: absolute; top: 0; right: 0; margin: 12px; max-width: 360px;">
                    🎉 <strong>{{ session('success') }}</strong>
                </div>
            @endif

            {{-- Filters --}}
            <form method="GET" id="stock-filter-form" class="mb-3 d-flex flex-wrap gap-2">
                <select name="type" class="form-select w-auto" onchange="this.form.submit()">
                    <option value="">{{ __('messages.all') }}</option>
                    <option value="in"  {{ request('type') == 'in'  ? 'selected' : '' }}>{{ __('messages.stock_in') }}</option>
                    <option value="out" {{ request('type') == 'out' ? 'selected' : '' }}>{{ __('messages.stock_out') }}</option>
                </select>

                <select name="ingredient_id" class="form-select w-auto" onchange="this.form.submit()">
                    <option value="">{{ __('messages.all') }} Ingredients</option>
                    @foreach($ingredients as $ingredient)
                        <option value="{{ $ingredient->id }}" {{ request('ingredient_id') == $ingredient->id ? 'selected' : '' }}>
                            {{ $ingredient->name }} ({{ rtrim(rtrim(number_format($ingredient->stock, 2, '.', ''), '0'), '.') }} {{ $ingredient->unit }})
                        </option>
                    @endforeach
                </select>

                <input type="date" name="start_date" class="form-control w-auto" value="{{ request('start_date') }}" onchange="this.form.submit()">
                <input type="date" name="end_date" class="form-control w-auto" value="{{ request('end_date') }}" onchange="this.form.submit()">
            </form>

            {{-- Table --}}
            <div class="table-responsive">
                <table class="table table-bordered table-striped table-hover align-middle mb-0">
                    <thead class="sticky-top" style="top: 0; z-index: 5; background-color: #dbeafe;">
                        <tr>
                            <th class="text-center">{{ __('messages.ingredient_id') }}</th>
                            <th class="text-center">Ingredient</th>
                            <th class="text-center">Total In</th>
                            <th class="text-center">Total Out</th>
                            <th class="text-center">{{ __('messages.current_stock') }}</th>
                            <th class="text-center">Last At</th>
                            <th class="text-center"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($items as $item)
                            @php
                                $in  = rtrim(rtrim(number_format($item->total_in ?? 0, 2, '.', ''), '0'), '.');
                                $out = rtrim(rtrim(number_format($item->total_out ?? 0, 2, '.', ''), '0'), '.');
                                $stock = rtrim(rtrim(number_format($item->stock, 2, '.', ''), '0'), '.');
                                $historyRoute = ($isSuper ? route('superadmin.ingredient-stock.history', $item) : route('admin.ingredient-stock.history', $item)) . (request()->getQueryString() ? '?' . request()->getQueryString() : '');
                            @endphp
                            <tr>
                                <td class="text-center">{{ $item->id }}</td>
                                <td class="text-start">{{ $item->name }}</td>
                                <td class="text-center">{{ $in }}</td>
                                <td class="text-center">{{ $out }}</td>
                                <td class="text-center">{{ $stock }} {{ $item->unit }}</td>
                                <td class="text-center">
    {{ $item->stockLogs->first() ? $item->stockLogs->first()->created_at->format('d/m/Y H:i') : '-' }}
</td>
                                <td class="text-center">
                                    <button type="button"
                                            class="btn btn-sm btn-outline-secondary view-history-btn"
                                            data-bs-toggle="modal"
                                            data-bs-target="#historyModal"
                                            data-title="{{ $item->name }}"
                                            data-url="{{ $historyRoute }}">
                                        <i class="bi bi-clock-history me-1"></i> Details
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center">{{ __('messages.no_stock_logs') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-3 d-flex justify-content-center">
                {{ $items->appends(request()->query())->links('pagination::bootstrap-5') }}
            </div>
        </div>
    </div>
</div>

{{-- Modal (same as products) --}}
<div class="modal fade" id="historyModal" tabindex="-1" aria-labelledby="historyModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="historyModalLabel">History</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body" id="historyModalBody">
        <div class="py-5 text-center text-muted">
            <div class="spinner-border me-2" role="status" aria-hidden="true"></div>
            Loading…
        </div>
      </div>
    </div>
  </div>
</div>
@endsection

@push('styles')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" />
<style>
    #successToast{border-left:6px solid #198754;background:#d1e7dd;font-size:14px;border-radius:6px;z-index:1050}
    thead.sticky-top th{background-color:#dbeafe!important;color:#000;font-weight:bold;border-bottom:1px solid #ccc}
    .badge-type{font-size:.85rem}
    .btn-outline-primary:hover{background:#0d6efd;color:#fff}
    .btn-outline-success:hover{background:#198754;color:#fff}
    .btn-outline-danger:hover{background:#dc3545;color:#fff}
    .table td,.table th{vertical-align:middle}
    .btn-switch .btn{border-radius:999px}
    .btn-switch .btn + .btn{margin-left:6px}
</style>
@endpush

@push('scripts')
<script src="https://unpkg.com/htmx.org@1.9.2"></script>
<script>
document.addEventListener('DOMContentLoaded', function(){
    const toast = document.getElementById('successToast');
    if (toast){
        setTimeout(()=>{ toast.classList.remove('animate__fadeInDown');
                         toast.classList.add('animate__fadeOutUp');
                         setTimeout(()=>toast.remove(), 800);
        }, 2000);
    }

    const tts = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tts.forEach(el => new bootstrap.Tooltip(el));

    // Modal detail loader
    document.addEventListener('click', function (e) {
        const btn = e.target.closest('.view-history-btn');
        if (!btn) return;
        const title = btn.dataset.title || 'History';
        const url   = btn.dataset.url;

        document.getElementById('historyModalLabel').textContent = `${title} — {{ __('messages.stock_history') }}`;
        const body = document.getElementById('historyModalBody');
        body.innerHTML = `<div class="py-5 text-center text-muted">
            <div class="spinner-border me-2" role="status"></div> Loading…
        </div>`;

        htmx.ajax('GET', url, { target: '#historyModalBody', swap: 'innerHTML' });
    });
});
</script>
@endpush
