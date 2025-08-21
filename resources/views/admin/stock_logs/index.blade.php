@extends('layouts.app')

@section('content')
<div class="container my-4">
    <div class="card shadow-sm border-0 rounded-4 animate__animated">

        @php
            $isSuper = auth()->user()->role === 'superadmin';
            $productIndex    = $isSuper ? route('superadmin.stock-logs.index')      : route('admin.stock-logs.index');
            $ingredientIndex = $isSuper ? route('superadmin.ingredient-stock.index') : route('admin.ingredient-stock.index');
            $onProducts = request()->routeIs('superadmin.stock-logs.*') || request()->routeIs('admin.stock-logs.*');
        @endphp

        <div class="card-header d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center gap-3 flex-wrap">
                <h5 class="mb-0 fw-bold">📋 {{ __('messages.stock_history') }}</h5>

                {{-- Pretty segment switch: Products | Ingredients --}}
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
                            ? route('superadmin.stock-logs.export', ['type' => request('type'), 'start_date' => request('start_date'), 'end_date' => request('end_date'), 'category_id' => request('category_id'), 'preset' => request('preset')])
                            : route('admin.stock-logs.export',       ['type' => request('type'), 'start_date' => request('start_date'), 'end_date' => request('end_date'), 'category_id' => request('category_id'), 'preset' => request('preset')]) }}"
                   class="btn btn-outline-success btn-sm">
                    <i class="bi bi-filetype-csv me-1"></i>{{ __('messages.export_csv') }}
                </a>

                <a href="{{ $isSuper
                            ? route('superadmin.stock-logs.pdf', ['type' => request('type'), 'start_date' => request('start_date'), 'end_date' => request('end_date'), 'category_id' => request('category_id'), 'preset' => request('preset')])
                            : route('admin.stock-logs.pdf',       ['type' => request('type'), 'start_date' => request('start_date'), 'end_date' => request('end_date'), 'category_id' => request('category_id'), 'preset' => request('preset')]) }}"
                   class="btn btn-outline-primary btn-sm">
                    <i class="bi bi-printer me-1"></i>{{ __('messages.print') }}
                </a>

                <a href="{{ $isSuper
                            ? route('superadmin.stock-logs.create', ['category_id' => request('category_id')])
                            : route('admin.stock-logs.create',       ['category_id' => request('category_id')]) }}"
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

            <form method="GET" class="mb-3 d-flex gap-2 flex-wrap">
                <select name="type" class="form-select w-auto">
                    <option value="">{{ __('messages.all') }}</option>
                    <option value="in"  {{ request('type') == 'in'  ? 'selected' : '' }}>{{ __('messages.stock_in') }}</option>
                    <option value="out" {{ request('type') == 'out' ? 'selected' : '' }}>{{ __('messages.stock_out') }}</option>
                </select>

                <select name="category_id" class="form-select w-auto">
                    <option value="">{{ __('messages.all_categories') }}</option>
                    {!! render_category_options($categories, request('category_id')) !!}
                </select>

                <select name="preset" class="form-select w-auto">
                    <option value="">{{ __('messages.all_day') }}</option>
                    <option value="today"      {{ request('preset') == 'today' ? 'selected' : '' }}>{{ __('messages.today') }}</option>
                    <option value="this_week"  {{ request('preset') == 'this_week' ? 'selected' : '' }}>{{ __('messages.this_week') }}</option>
                    <option value="this_month" {{ request('preset') == 'this_month' ? 'selected' : '' }}>{{ __('messages.this_month') }}</option>
                </select>

                <input type="date" name="start_date" class="form-control w-auto" value="{{ request('start_date') }}">
                <input type="date" name="end_date"   class="form-control w-auto" value="{{ request('end_date') }}">
                <button type="submit" class="btn btn-outline-primary">{{ __('messages.filter') }}</button>
            </form>

            <div class="table-responsive">
                <table class="table table-bordered table-striped table-hover align-middle mb-0">
                    <thead class="sticky-top" style="top: 0; z-index: 5; background-color: #dbeafe;">
                        <tr>
                            <th class="text-center">{{ __('messages.product_id') }}</th>
                            <th class="text-center">{{ __('messages.category') }}</th>
                            <th class="text-center">{{ __('messages.product') }}</th>
                            <th class="text-center">{{ __('messages.type') }}</th>
                            <th class="text-center">{{ __('messages.qty') }}</th>
                            <th class="text-center">{{ __('messages.current_stock') }}</th>
                            <th class="text-center">{{ __('messages.Note') }}</th>
                            <th class="text-center">{{ __('messages.users') }}</th>
                            <th class="text-center">{{ __('messages.date') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($logs as $log)
                            @php
                                $stockNow = rtrim(rtrim(number_format($log->product->stock, 2, '.', ''), '0'), '.');
                            @endphp
                            <tr>
                                <td class="text-center">{{ $log->product->id }}</td>
                                <td class="text-start">{{ $log->product->category->name ?? '' }}</td>
                                <td class="text-start">{{ $log->product->name }}</td>
                                <td class="text-center">
                                    <span class="badge badge-type fw-normal {{ strtolower($log->type) === 'in' ? 'bg-success' : 'bg-danger' }}">
                                        {{ strtoupper($log->type) }}
                                    </span>
                                </td>
                                <td class="text-center">{{ $log->quantity }}</td>
                                <td class="text-center">{{ $stockNow }}</td>
                                <td class="text-center">{{ $log->note }}</td>
                                <td class="text-center">{{ $log->user->name }}</td>
                                <td class="text-center">{{ $log->created_at->format('d/m/Y H:i') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center">{{ __('messages.no_stock_logs') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-3 d-flex justify-content-center">
                {{ $logs->appends(request()->query())->links('pagination::bootstrap-5') }}
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

    /* Switcher polish */
    .btn-switch .btn{border-radius:999px}
    .btn-switch .btn + .btn{margin-left:6px}
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function(){
    const toast = document.getElementById('successToast');
    if (toast){
        setTimeout(()=>{ toast.classList.remove('animate__fadeInDown');
                         toast.classList.add('animate__fadeOutUp');
                         setTimeout(()=>toast.remove(), 800);
        }, 2000);
    }
    // Enable Bootstrap tooltips for the switcher
    const tts = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tts.forEach(el => new bootstrap.Tooltip(el));
});
</script>
@endpush
