@props(['sales', 'exportRoute', 'printRoute', 'filter' => null, 'totalAmount' => 0])

@php
    // Abbreviate size to S/M/L (default M); accepts 's','small','m','medium','l','large', etc.
    $sizeToAbbr = function ($size) {
        if (!$size) return 'M';
        $s = strtolower($size);
        return match (true) {
            in_array($s, ['s','small'])   => 'S',
            in_array($s, ['m','medium'])  => 'M',
            in_array($s, ['l','large'])   => 'L',
            default => strtoupper(substr($s, 0, 1)),
        };
    };
    
    $routePrefix = match (auth()->user()->role) {
        'superadmin' => 'superadmin',
        'admin' => 'admin',
        'cashier' => 'cashier',
        default => null,
    };
@endphp

<!-- Header: Logo + Date + Export Buttons -->
<div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-3 mt-3">
    <!-- Left: Logo + Shop Name -->
    <div class="d-flex align-items-center gap-2 mb-2 mb-md-0">
        <img src="{{ asset('images/coffeeland-logo.png') }}" alt="Logo" style="height: 55px;">
        <h3 class="fw-bold text-dark mb-0">{{ optional($setting)->shop_name ?? 'Coffee land' }}</h3>
    </div>

    <!-- Right: Date + Buttons -->
    <div class="text-md-end">
        <div class="fw-bold mb-2">
            {{ __('messages.date') }}: {{ now()->format('d M Y, H:i') }}
        </div>
        <div class="d-flex justify-content-md-end gap-2 {{ request('print') ? 'd-none' : '' }}">
            <a href="{{ $exportRoute }}"
               class="btn btn-success btn-sm d-flex align-items-center gap-2 px-3 shadow-sm">
                ⬇️ {{ __('messages.export_csv') }}
            </a>
            <a href="{{ $printRoute }}"
               class="btn btn-primary btn-sm d-flex align-items-center gap-2 px-3 shadow-sm">
                🖨️ {{ __('messages.print') }}
            </a>
        </div>
    </div>
</div>

@if($filter)
    {!! $filter !!}
@endif

<!-- Sales Table -->
<table class="table table-bordered table-hover align-middle text-center mb-0">
    <thead class="table-primary">
        <tr>
            <th style="white-space: nowrap;">{{ __('messages.invoice') }}</th>
            <th style="white-space: nowrap;">{{ __('messages.user') }}</th>
            <th style="white-space: nowrap;">{{ __('messages.date') }}</th>
            <th style="white-space: nowrap;">{{ __('messages.category') }}</th>
            <th style="white-space: nowrap;">{{ __('messages.item_names') }}</th>
            <th style="white-space: nowrap;">{{ __('messages.items') }}</th>
            <th style="white-space: nowrap;">{{ __('messages.price_unit') }}</th>
            <th style="white-space: nowrap;">{{ __('messages.discount') }}</th>
            <th style="white-space: nowrap;">{{ __('messages.total') }}</th>
            <th style="white-space: nowrap;">{{ __('messages.actions') }}</th>
        </tr>
    </thead>
    <tbody>
        @forelse ($sales as $sale)
        <tr>
            <td>
                @if($routePrefix)
                    <a href="{{ route($routePrefix . '.sales.invoice', $sale->id) }}">
                        {{ $sale->invoice_no }}
                    </a>
                @else
                    {{ $sale->invoice_no }}
                @endif
            </td>
            <td>{{ $sale->user->name ?? '-' }}</td>
            <td class="text-nowrap">{{ $sale->created_at->format('d M Y, H:i') }}</td>
            @php
                $categoryNames = $sale->items->pluck('product.category.name')->unique();
            @endphp
            <td>{{ $categoryNames->implode(', ') }}</td>

            <td class="text-center">
                @foreach ($sale->items as $item)
                    {{ $item->product->name }}@unless($item->product->isFood()) ({{ $sizeToAbbr($item->size) }})@endunless x{{ $item->quantity }}<br>
                    foreach ($item->options ?? [] as $opt)
                        &nbsp;&nbsp;• {{ $opt }}<br>
                    @endforeach
                @endforeach
            </td>

            <td>{{ $sale->items->sum('quantity') }}</td>

            <td class="text-center">
                @foreach ($sale->items as $item)
                    {{ optional($setting)->currency ?? '$' }}{{ number_format($item->price, 2) }}<br>
                @endforeach
            </td>

            <td>{{ optional($setting)->currency ?? '$' }}{{ number_format($sale->discount ?? 0, 2) }}</td>
            <td><strong>{{ optional($setting)->currency ?? '$' }}{{ number_format($sale->total, 2) }}</strong></td>
            <td>
                <button type="button" class="btn btn-sm btn-primary view-invoice" data-sale-id="{{ $sale->id }}">
                    View
                </button>
            </td>
        </tr>
        @empty
        <tr>
            <td colspan="10" class="text-center text-muted">No sales data available.</td>
        </tr>
        @endforelse
    </tbody>
</table>

@if($sales instanceof \Illuminate\Contracts\Pagination\Paginator)
    <div class="mt-3 d-flex justify-content-center d-print-none {{ request('print') || request()->filled('export') ? 'd-none' : '' }}">
        {{ $sales->appends(request()->query())->links('pagination::bootstrap-5') }}
    </div>
@endif

<div class="mt-4 text-end fw-bold fs-5 text-dark border-top pt-3">
    {{ __('messages.total_sale_amount') }}:
    {{ optional($setting)->currency ?? '$' }}{{ number_format($totalAmount, 2) }}
</div>

<!-- Print Footer -->
<div class="mt-4 px-4">
    <div class="d-flex justify-content-between align-items-center">
        <small>🕒 Printed on: {{ now()->format('d M Y, H:i') }}</small>
        <div class="text-end">
            <small>🖋️ Signature:</small>
            <div style="border-bottom: 1px solid #000; width: 200px; height: 50px;"></div>
        </div>
    </div>
</div>

@push('styles')
<style>
@media print {
    html, body {
        margin: 0 !important;
        padding: 0 !important;
        background: white !important;
        font-family: 'Battambang', 'Noto Sans Khmer', sans-serif;
        font-size: 14px;
    }
    body * { visibility: hidden !important; }
    .print-area, .print-area * { visibility: visible !important; }
    .print-area {
        position: static !important;
        top: 0 !important; left: 0 !important; right: 0 !important; bottom: 0 !important;
        width: 100% !important;
        margin: 0 !important; padding: 0 0.5cm !important;
        box-sizing: border-box !important; box-shadow: none !important;
    }
    .print-area > div:first-child { margin-top: 10px; }
    .sidebar, .navbar, .btn, .logout-section, form[action*="logout"], .dropdown, .dropdown-menu, .d-print-none {
        display: none !important;
    }
}
</style>
@endpush

@if(request('print'))
@push('scripts')
<script>
    window.addEventListener('load', function () { window.print(); });

    window.addEventListener('afterprint', function () {
        const params = new URLSearchParams(window.location.search);
        params.delete('print');

        const baseUrl = "{{ auth()->user()->role === 'superadmin'
            ? route('superadmin.sales.report')
            : (auth()->user()->role === 'admin'
                ? route('admin.sales.report')
                : route('cashier.sales.history')) }}";

        const queryString = params.toString();
        window.location.href = baseUrl + (queryString ? `?${queryString}` : '');
    });
</script>
@endpush
@endif
