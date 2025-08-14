@extends('layouts.app')

@section('content')
@php
    $currency = optional($setting ?? null)->currency ?? '$';
    $printedAt = now()->format('d/m/Y, H:i');
    $shopName  = optional($setting ?? null)->shop_name ?? 'COFFEE LAND';
    $shopPhone = optional($setting ?? null)->phone ?? '';
    $shopAddr  = optional($setting ?? null)->address ?? '';
@endphp

{{-- Header (title + print btn) --}}
<div class="d-flex justify-content-between align-items-center mb-4 no-print">
    <h4 class="fw-bold mb-0">Z‑Report</h4>
    <button onclick="window.print()" class="btn btn-outline-primary rounded-pill">
        🖨️ Print
    </button>
</div>

{{-- Print Header --}}
<div class="bg-white rounded shadow-sm p-3 mb-3 print-header">
    <div class="d-flex align-items-center gap-3">
        @if(!empty($setting?->logo_path))
            <img src="{{ asset($setting->logo_path) }}" alt="Logo" style="height:48px;width:auto;">
        @endif
        <div class="flex-grow-1">
            <div class="fw-bold">{{ $shopName }}</div>
            <div class="small text-muted">{{ $shopAddr }}</div>
            <div class="small text-muted">{{ $shopPhone }}</div>
        </div>
        <div class="text-end">
            <div class="small text-muted">Printed:</div>
            <div class="fw-semibold">{{ $printedAt }}</div>
        </div>
    </div>
</div>

{{-- KPI Row --}}
<div class="row g-3 mb-4">
    <div class="col-6 col-lg-3">
        <div class="kpi-card">
            <div class="kpi-label">Gross</div>
            <div class="kpi-value">{{ $currency }}{{ number_format($summary->gross ?? 0, 2) }}</div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="kpi-card">
            <div class="kpi-label">Discount</div>
            <div class="kpi-value">{{ $currency }}{{ number_format($summary->discount ?? 0, 2) }}</div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="kpi-card">
            <div class="kpi-label">Net</div>
            <div class="kpi-value text-success">{{ $currency }}{{ number_format($summary->net ?? 0, 2) }}</div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="kpi-card">
            <div class="kpi-label">Orders</div>
            <div class="kpi-value">{{ number_format($summary->orders ?? 0) }}</div>
        </div>
    </div>
</div>

{{-- Summary Table --}}
<div class="bg-white rounded shadow-sm p-4 mb-4">
    <h5 class="text-center fw-bold mb-3">Today's Summary</h5>
    <table class="table table-bordered text-center mb-0 compact-table">
        <thead class="table-light">
            <tr>
                <th>Gross</th>
                <th>Discount</th>
                <th>Net</th>
                <th>Orders</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>{{ $currency }}{{ number_format($summary->gross ?? 0, 2) }}</td>
                <td>{{ $currency }}{{ number_format($summary->discount ?? 0, 2) }}</td>
                <td class="fw-semibold text-success">{{ $currency }}{{ number_format($summary->net ?? 0, 2) }}</td>
                <td class="fw-semibold">{{ number_format($summary->orders ?? 0) }}</td>
            </tr>
        </tbody>
    </table>
</div>

<div class="row g-4">
    {{-- By Payment Method --}}
    <div class="col-lg-6">
        <div class="bg-white rounded shadow-sm p-4 h-100">
            <h6 class="fw-bold text-center mb-3">By Payment Method</h6>
            <table class="table table-bordered text-center mb-0 compact-table">
                <thead class="table-light">
                    <tr>
                        <th>Method</th>
                        <th>Gross</th>
                        <th>Discount</th>
                        <th>Net</th>
                        <th>Orders</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($totalsByPaymentMethod as $row)
                        <tr>
                            <td>{{ strtoupper($row->payment_method) }}</td>
                            <td>{{ $currency }}{{ number_format($row->gross ?? 0, 2) }}</td>
                            <td>{{ $currency }}{{ number_format($row->discount ?? 0, 2) }}</td>
                            <td class="fw-semibold">{{ $currency }}{{ number_format($row->net ?? 0, 2) }}</td>
                            <td>{{ number_format($row->orders ?? 0) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-muted">No data</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- By Cashier --}}
    <div class="col-lg-6">
        <div class="bg-white rounded shadow-sm p-4 h-100">
            <h6 class="fw-bold text-center mb-3">By Cashier</h6>
            <table class="table table-bordered text-center mb-0 compact-table">
                <thead class="table-light">
                    <tr>
                        <th>Cashier</th>
                        <th>Gross</th>
                        <th>Discount</th>
                        <th>Net</th>
                        <th>Orders</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($totalsByCashier as $row)
                        <tr>
                            <td>{{ $row->user->name ?? 'N/A' }}</td>
                            <td>{{ $currency }}{{ number_format($row->gross ?? 0, 2) }}</td>
                            <td>{{ $currency }}{{ number_format($row->discount ?? 0, 2) }}</td>
                            <td class="fw-semibold">{{ $currency }}{{ number_format($row->net ?? 0, 2) }}</td>
                            <td>{{ number_format($row->orders ?? 0) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-muted">No data</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- Footer for print (signature) --}}
<div class="bg-white rounded shadow-sm p-4 mt-4 print-footer">
    <div class="row g-4">
        <div class="col-md-6">
            <div class="signature-box">
                <div class="small text-muted mb-2">Prepared By</div>
                <div class="sig-line"></div>
            </div>
        </div>
        <div class="col-md-6 text-md-end">
            <div class="signature-box">
                <div class="small text-muted mb-2">Approved By</div>
                <div class="sig-line"></div>
            </div>
        </div>
    </div>
</div>

{{-- Styles --}}
<style>
  .kpi-card{
    background:#fff; border:0; border-radius:12px; padding:14px 16px;
    box-shadow:0 .25rem .75rem rgba(0,0,0,.06);
  }
  .kpi-label{ font-size:.82rem; color:#6b7280; }
  .kpi-value{ font-weight:800; font-size:1.2rem; color:#111827; }
  .compact-table th, .compact-table td{ padding:.5rem .6rem; }

  .print-header, .print-footer{ page-break-inside: avoid; }
  .signature-box .sig-line{
    border-bottom: 1px dashed #999; height: 28px; width: 260px;
  }

  @media print {
    body { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
    .no-print { display: none !important; }
    nav, .sidebar, .navbar, footer { display: none !important; }
    .container, .container-fluid { width: 100% !important; padding: 0 !important; }
    .card, .bg-white, .shadow-sm { box-shadow: none !important; border: 1px solid #ddd !important; }
    .kpi-card { border: 1px solid #ddd !important; }
  }
</style>
@endsection
