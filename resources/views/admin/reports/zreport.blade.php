@extends('layouts.app')

@section('content')
@php
    $currency  = optional($setting ?? null)->currency ?? '$';
    $printedAt = now()->format('d/m/Y, H:i');
    $shopName  = optional($setting ?? null)->shop_name ?? 'COFFEE LAND';
    $shopPhone = optional($setting ?? null)->phone ?? '';
    $shopAddr  = optional($setting ?? null)->address ?? '';

    $money = fn($n) => number_format((float)($n ?? 0), 2);
    $int   = fn($n) => number_format((int)($n ?? 0));
@endphp

{{-- Top bar --}}
<div class="d-flex justify-content-between align-items-center mb-3 no-print">
  <h4 class="fw-bold mb-0">Z-Report</h4>
  <div class="d-flex gap-2">
    <a href="{{ route(auth()->user()->role . '.dashboard') }}"
       class="btn btn-outline-secondary rounded-pill">Back to Dashboard</a>
    <button onclick="window.print()" class="btn btn-outline-primary rounded-pill">
      <i class="bi bi-printer"></i> Print
    </button>
  </div>
</div>

{{-- Brand header --}}
<div class="bg-white rounded-3 shadow-sm p-3 mb-3 print-header">
  <div class="d-flex align-items-center gap-3">
    @if(!empty($setting?->logo_path))
      <img src="{{ asset($setting->logo_path) }}" alt="Logo" style="height:48px;width:auto">
    @endif

    <div class="flex-grow-1">
      <div class="fw-bold fs-6">{{ $shopName }}</div>
      @if($shopAddr)<div class="small text-muted">{{ $shopAddr }}</div>@endif
      @if($shopPhone)<div class="small text-muted">{{ $shopPhone }}</div>@endif
    </div>

    <div class="text-end">
      <div class="small text-muted">Printed</div>
      <div class="fw-semibold">{{ $printedAt }}</div>
    </div>
  </div>
</div>

{{-- KPI row --}}
<div class="row g-3 mb-3">
  <div class="col-6 col-xl-3">
    <div class="kpi-card">
      <div class="kpi-label">Gross</div>
      <div class="kpi-value">{{ $currency }}{{ $money($summary->gross ?? 0) }}</div>
    </div>
  </div>
  <div class="col-6 col-xl-3">
    <div class="kpi-card">
      <div class="kpi-label">Discount</div>
      <div class="kpi-value">{{ $currency }}{{ $money($summary->discount ?? 0) }}</div>
    </div>
  </div>
  <div class="col-6 col-xl-3">
    <div class="kpi-card">
      <div class="kpi-label">Net</div>
      <div class="kpi-value text-success">{{ $currency }}{{ $money($summary->net ?? 0) }}</div>
    </div>
  </div>
  <div class="col-6 col-xl-3">
    <div class="kpi-card">
      <div class="kpi-label">Orders</div>
      <div class="kpi-value">{{ $int($summary->orders ?? 0) }}</div>
    </div>
  </div>
</div>

{{-- Today summary --}}
<div class="card border-0 shadow-sm rounded-3 mb-3">
  <div class="card-header bg-light fw-semibold text-center">Today's Summary</div>
  <div class="card-body p-0">
    <div class="table-responsive">
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
            <td>{{ $currency }}{{ $money($summary->gross ?? 0) }}</td>
            <td>{{ $currency }}{{ $money($summary->discount ?? 0) }}</td>
            <td class="fw-semibold text-success">{{ $currency }}{{ $money($summary->net ?? 0) }}</td>
            <td class="fw-semibold">{{ $int($summary->orders ?? 0) }}</td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</div>

<div class="row g-3">
  {{-- By Payment Method --}}
  <div class="col-lg-6">
    <div class="card border-0 shadow-sm rounded-3 h-100">
      <div class="card-header bg-light fw-semibold text-center">By Payment Method</div>
      <div class="card-body p-0">
        <div class="table-responsive">
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
                  <td class="text-uppercase">{{ $row->payment_method }}</td>
                  <td>{{ $currency }}{{ $money($row->gross ?? 0) }}</td>
                  <td>{{ $currency }}{{ $money($row->discount ?? 0) }}</td>
                  <td class="fw-semibold">{{ $currency }}{{ $money($row->net ?? 0) }}</td>
                  <td>{{ $int($row->orders ?? 0) }}</td>
                </tr>
              @empty
                <tr><td colspan="5" class="text-muted">No data</td></tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>

  {{-- By Cashier --}}
  <div class="col-lg-6">
    <div class="card border-0 shadow-sm rounded-3 h-100">
      <div class="card-header bg-light fw-semibold text-center">By Cashier</div>
      <div class="card-body p-0">
        <div class="table-responsive">
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
                  <td>{{ $currency }}{{ $money($row->gross ?? 0) }}</td>
                  <td>{{ $currency }}{{ $money($row->discount ?? 0) }}</td>
                  <td class="fw-semibold">{{ $currency }}{{ $money($row->net ?? 0) }}</td>
                  <td>{{ $int($row->orders ?? 0) }}</td>
                </tr>
              @empty
                <tr><td colspan="5" class="text-muted">No data</td></tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>

{{-- Signatures --}}
<div class="bg-white rounded-3 shadow-sm p-4 mt-3 print-footer">
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
    background:#fff;border:1px solid #eef0f2;border-radius:12px;padding:14px 16px;
    box-shadow:0 .25rem .65rem rgba(0,0,0,.04)
  }
  .kpi-label{font-size:.82rem;color:#6b7280}
  .kpi-value{font-weight:800;font-size:1.2rem;color:#111827}
  .compact-table th,.compact-table td{padding:.55rem .6rem}
  .card-header{border-bottom:1px solid #eef0f2}
  .print-header,.print-footer{page-break-inside:avoid}
  .signature-box .sig-line{border-bottom:1px dashed #999;height:28px;width:260px}

  @media print{
    body{-webkit-print-color-adjust:exact;print-color-adjust:exact}
    .no-print,nav,.sidebar,.navbar,footer{display:none!important}
    .container,.container-fluid{width:100%!important;padding:0!important}
    .card,.bg-white,.shadow-sm{box-shadow:none!important;border:1px solid #ddd!important}
    .kpi-card{border:1px solid #ddd!important}
  }
</style>
@endsection
