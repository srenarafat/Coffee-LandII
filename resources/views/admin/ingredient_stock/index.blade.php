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
                <h5 class="mb-0 fw-bold">📋 Ingredient Stock History</h5>

                {{-- Segmented switch: Products | Ingredients --}}
                <div class="btn-group btn-group-sm btn-switch" role="group" aria-label="Switch section">
                    <a href="{{ $productIndex }}"
                       class="btn {{ $onProducts ? 'btn-primary text-white' : 'btn-outline-secondary' }}"
                       data-bs-toggle="tooltip" data-bs-placement="bottom"
                       title="Product stock history">
                        <i class="bi bi-box-seam me-1"></i> Products
                    </a>
                    <a href="{{ $ingredientIndex }}"
                       class="btn {{ $onProducts ? 'btn-outline-secondary' : 'btn-primary text-white' }}"
                       data-bs-toggle="tooltip" data-bs-placement="bottom"
                       title="Ingredient stock ledger">
                        <span class="me-1" aria-hidden="true">🥕</span> Ingredients
                    </a>
                </div>
            </div>

            <div class="d-flex gap-2">
                <a href="{{ $isSuper
                            ? route('superadmin.ingredient-stock.export', ['type'=>request('type'), 'start_date'=>request('start_date'), 'end_date'=>request('end_date'), 'ingredient_id'=>request('ingredient_id')])
                            : route('admin.ingredient-stock.export',       ['type'=>request('type'), 'start_date'=>request('start_date'), 'end_date'=>request('end_date'), 'ingredient_id'=>request('ingredient_id')]) }}"
                   class="btn btn-outline-success btn-sm d-print-none">
                    <i class="bi bi-filetype-csv me-1"></i>{{ __('messages.export_csv') }}
                </a>

                <a href="{{ $isSuper
                            ? route('superadmin.ingredient-stock.pdf', ['type'=>request('type'), 'start_date'=>request('start_date'), 'end_date'=>request('end_date'), 'ingredient_id'=>request('ingredient_id')])
                            : route('admin.ingredient-stock.pdf',       ['type'=>request('type'), 'start_date'=>request('start_date'), 'end_date'=>request('end_date'), 'ingredient_id'=>request('ingredient_id')]) }}"
                   class="btn btn-outline-primary btn-sm d-print-none">
                    <i class="bi bi-printer me-1"></i>{{ __('messages.print') }}
                </a>

                <a href="{{ $isSuper ? route('superadmin.ingredient-stock.create') : route('admin.ingredient-stock.create') }}"
                   class="btn btn-primary btn-sm d-print-none">
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

                @if(request()->hasAny(['type','ingredient_id','start_date','end_date']))
                    <a href="{{ $ingredientIndex }}" class="btn btn-outline-secondary">Reset</a>
                @endif
            </form>

            {{-- Table --}}
            <div class="table-responsive">
                <table class="table table-bordered table-striped table-hover align-middle mb-0">
                    <thead class="sticky-top">
                        <tr>
                            <th class="text-center">Details</th>
                            <th class="text-center">Ingredient</th>
                            <th class="text-center">Total In</th>
                            <th class="text-center">Total Out</th>
                            <th class="text-center">{{ __('messages.current_stock') }}</th>
                            <th class="text-center">Last At</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($items as $item)
                            @php
                                $in  = rtrim(rtrim(number_format($item->total_in ?? 0, 2, '.', ''), '0'), '.');
                                $out = rtrim(rtrim(number_format($item->total_out ?? 0, 2, '.', ''), '0'), '.');
                                $stock = rtrim(rtrim(number_format($item->stock, 2, '.', ''), '0'), '.');
                            @endphp
                            <tr>
                                <td class="text-center">
                                    <button type="button" class="btn btn-sm btn-outline-secondary toggle-history" data-id="{{ $item->id }}">Details</button>
                                </td>
                                <td class="text-center">{{ $item->name }}</td>
                                <td class="text-center">{{ $in }}</td>
                                <td class="text-center">{{ $out }}</td>
                                <td class="text-center">{{ $stock }} {{ $item->unit }}</td>
                                <td class="text-center">{{ optional($item->last_at)->format('d/m/Y H:i') }}</td>
                            </tr>
                            <tr id="history-row-{{ $item->id }}" class="history-row" style="display:none;">
                                <td colspan="6" class="p-0"><div class="history-content"></div></td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center">{{ __('messages.no_stock_logs') }}</td>
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
@endsection

@push('styles')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" />
<style>
    #successToast{border-left:6px solid #198754;background:#d1e7dd;font-size:14px;border-radius:6px;z-index:1050}
    thead.sticky-top{top:0;z-index:5;background-color:#dbeafe!important}
    thead.sticky-top th{background-color:#dbeafe!important;color:#000;font-weight:bold;border-bottom:1px solid #ccc}
    .badge-type{font-size:.75rem}
    .btn-outline-primary:hover{background:#0d6efd;color:#fff}
    .btn-outline-success:hover{background:#198754;color:#fff}
    .btn-outline-danger:hover{background:#dc3545;color:#fff}
    .table td,.table th{vertical-align:middle}

    /* Segmented switch styling (same as product page) */
    .btn-switch .btn{border-radius:999px}
    .btn-switch .btn + .btn{margin-left:6px}
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const toast = document.getElementById('successToast');
    if (toast) {
        setTimeout(() => {
            toast.classList.remove('animate__fadeInDown');
            toast.classList.add('animate__fadeOutUp');
            setTimeout(() => toast.remove(), 800);
        }, 2000);
    }
    // enable Bootstrap tooltips on the switcher
    const tts = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tts.forEach(el => new bootstrap.Tooltip(el));
    
    document.querySelectorAll('.toggle-history').forEach(btn => {
        btn.addEventListener('click', async function () {
            const id = this.getAttribute('data-id');
            const row = document.getElementById(`history-row-${id}`);
            if (row.style.display === 'none') {
                if (!row.dataset.loaded) {
                    const base = '{{ $isSuper ? url('superadmin/ingredient-stock') : url('admin/ingredient-stock') }}';
                    const res = await fetch(`${base}/${id}/history${window.location.search}`);
                    row.querySelector('.history-content').innerHTML = await res.text();
                    row.dataset.loaded = 'true';
                }
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    });
});
</script>
@endpush
