@extends('layouts.app')


@section('content')
<div class="container my-4">
    <div class="card shadow-sm border-0 rounded-4 animate__animated">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0 fw-bold">📋 {{ __('messages.stock_history') }}</h5>
            <div class="d-flex gap-2">
                <a href="{{ auth()->user()->role === 'superadmin' ? route('superadmin.stock-logs.export', ['type' => request('type'), 'start_date' => request('start_date'), 'end_date' => request('end_date'), 'category_id' => request('category_id'), 'preset' => request('preset')]) : route('admin.stock-logs.export', ['type' => request('type'), 'start_date' => request('start_date'), 'end_date' => request('end_date'), 'category_id' => request('category_id'), 'preset' => request('preset')]) }}" class="btn btn-outline-success btn-sm">⬇️ {{ __('messages.export_csv') }}</a>
                <a href="{{ auth()->user()->role === 'superadmin' ? route('superadmin.stock-logs.pdf', ['type' => request('type'), 'start_date' => request('start_date'), 'end_date' => request('end_date'), 'category_id' => request('category_id'), 'preset' => request('preset')]) : route('admin.stock-logs.pdf', ['type' => request('type'), 'start_date' => request('start_date'), 'end_date' => request('end_date'), 'category_id' => request('category_id'), 'preset' => request('preset')]) }}" class="btn btn-outline-primary btn-sm">🖨️ {{ __('messages.print') }}</a>
                <a href="{{ auth()->user()->role === 'superadmin'
                    ? route('superadmin.stock-logs.create', ['category_id' => request('category_id')])
                    : route('admin.stock-logs.create', ['category_id' => request('category_id')]) }}" class="btn btn-primary btn-sm">{{ __('messages.stock_adjustment') }}</a>
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
            <form method="GET" class="mb-3 d-flex gap-2">
                <select name="type" class="form-select w-auto">
                    <option value="">{{ __('messages.all') }}</option>
                    <option value="in" {{ request('type') == 'in' ? 'selected' : '' }}>{{ __('messages.stock_in') }}</option>
                    <option value="out" {{ request('type') == 'out' ? 'selected' : '' }}>{{ __('messages.stock_out') }}</option>
                </select>
                <select name="category_id" class="form-select w-auto">
                    <option value="">{{ __('messages.all_categories') }}</option>
                    {!! render_category_options($categories, request('category_id')) !!}
                </select>
                <select name="preset" class="form-select w-auto">
                    <option value="">{{ __('messages.all_day') }}</option>
                    <option value="today" {{ request('preset') == 'today' ? 'selected' : '' }}>{{ __('messages.today') }}</option>
                    <option value="this_week" {{ request('preset') == 'this_week' ? 'selected' : '' }}>{{ __('messages.this_week') }}</option>
                    <option value="this_month" {{ request('preset') == 'this_month' ? 'selected' : '' }}>{{ __('messages.this_month') }}</option>
                </select>
                <input type="date" name="start_date" class="form-control w-auto" value="{{ request('start_date') }}">
                <input type="date" name="end_date" class="form-control w-auto" value="{{ request('end_date') }}">
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
                <th class="text-center">{{ __('messages.Note') }}</th>
                <th class="text-center">{{ __('messages.users') }}</th>
                <th class="text-center">{{ __('messages.date') }}</th>
            </tr>
        </thead>
        <tbody>
        @forelse($logs as $log)
            <tr>
                <td class="text-center">{{ $log->product->id }}</td>
                <td class="text-start">{{ $log->product->category->name ?? '' }}</td>
                <td class="text-start">{{ $log->product->name }}</td>
                <td class="text-center">
                    <span class="badge bg-{{ strtolower($log->type) === 'in' ? 'success' : 'danger' }} badge-type fw-normal" style="font-size: 0.75rem;">
                        {{ strtoupper($log->type) }}
                    </span>
                </td>
                <td class="text-center">{{ $log->quantity }}</td>
                <td class="text-center">{{ $log->note }}</td>
                <td class="text-center">{{ $log->user->name }}</td>
                <td class="text-center">{{ $log->created_at->format('d/m/Y H:i') }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="8" class="text-center">{{ __('messages.no_stock_logs') }}</td>
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
    #successToast {
        border-left: 6px solid #198754;
        background-color: #d1e7dd;
        font-size: 14px;
        border-radius: 6px;
        z-index: 1050;
    }


    thead.sticky-top th {
        background-color: #dbeafe !important;
        color: #000;
        font-weight: bold;
        border-bottom: 1px solid #ccc;
    }


    .badge-type {
        font-size: 0.85rem;
    }


    .btn-outline-primary:hover {
        background-color: #0d6efd;
        color: white;
    }


    .btn-outline-success:hover {
        background-color: #198754;
        color: white;
    }


    .btn-outline-danger:hover {
        background-color: #dc3545;
        color: white;
    }


    .table td,
    .table th {
        vertical-align: middle;
    }
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
    });
</script>
@endpush



