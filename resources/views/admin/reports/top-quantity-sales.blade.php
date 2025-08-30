@extends('layouts.app')

@section('content')
@php
    // Max value for the progress bar scaling
    $maxQty = max($topProducts->pluck('total_quantity')->all() ?: [1]);
@endphp

<style>
  /* ===== Card & Table ===== */
  .report-card{ border-radius:14px; }
  .report-table{ --row-hover:#f8fafc; --stripe:#fbfdff; }
  .report-table thead th{
    position: sticky; top: 0; z-index: 2;
    background:#fff; border-bottom:1px solid #e9edf3;
    font-weight:700; color:#334155;
  }
  .report-table tbody tr:nth-child(odd){ background: var(--stripe); }
  .report-table tbody tr:hover{ background: var(--row-hover); }

  /* ===== Rank styles ===== */
  .rank-badge{
    display:inline-flex; align-items:center; justify-content:center;
    width:28px; height:28px; font-size:16px; border-radius:50%;
    vertical-align:middle;
  }
  .rank-1{ background:#fff7e6; }
  .rank-2{ background:#eef4ff; }
  .rank-3{ background:#fff0f1; }
  .rank-chip{
    display:inline-block; min-width:36px; text-align:center;
    background:#eef2f7; color:#334155; border-radius:999px;
    padding:.1rem .5rem; font-weight:700; line-height:1.2;
    vertical-align:middle;
  }

  /* ===== Category chip ===== */
  .cat-chip{
    display:inline-flex; align-items:center; gap:.35rem;
    background:#f1f5f9; color:#334155;
    border:1px solid #e5e7eb; border-radius:999px;
    padding:.18rem .6rem; font-weight:600; white-space:nowrap;
  }

  /* ===== Quantity mini bar ===== */
  .qty-cell{ position:relative; display:flex; align-items:center; gap:.5rem; }
  .qty-bar{
    position:relative; flex:0 0 180px; height:10px;
    background:#eef2f7; border-radius:999px; overflow:hidden;
  }
  .qty-bar::after{
    content:""; position:absolute; inset:0; width:var(--pct,0);
    background:linear-gradient(90deg, #22c55e, #16a34a);
  }
  .qty-label{ min-width:3ch; text-align:right; color:#0b1320; }

  /* ===== Header background style (title pill) ===== */
  .page-title-bar{
    background: linear-gradient(90deg, #4f46e5, #3b82f6);
    color:#fff;
    padding:10px 20px;
    border-radius:10px;
    font-weight:700;
    display:inline-flex; align-items:center; gap:.5rem;
    white-space:nowrap;
  }

  /* ===== Top row layout: Title centered in left zone, controls on right ===== */
  .top-row{
    display:grid;
    grid-template-columns: 1fr auto;  /* left zone (table area) | right zone (controls) */
    align-items:center;
    gap:12px;
    margin-bottom:12px;
  }
  .top-row .title-wrap{
    justify-self:center;  /* centers the title within the left zone */
  }
  .top-row .report-controls{
    justify-self:end;
  }
  @media (max-width: 992px){
    .top-row{ grid-template-columns: 1fr; }
    .top-row .title-wrap{ justify-self:start; margin-bottom:6px; }
    .top-row .report-controls{ justify-self:start; }
  }

  /* ===== Table header background ===== */
  .report-table thead th{
    background:#f3f4f6; /* light gray */
    color:#1f2937;      /* dark text */
    font-weight:700;
    text-transform:uppercase;
    border-bottom:2px solid #e5e7eb;
  }

  /* Controls sizing on small screens */
  @media (max-width: 576px){
    .report-controls .form-select{ width: 180px !important; }
  }
</style>

<!-- ===== Page Header (title centered in left zone, controls at right) ===== -->
<div class="top-row">
  <!-- Title centered in the left table area -->
  <div class="title-wrap">
    <h5 class="page-title-bar mb-0">
      <i class="bi bi-bar-chart-line"></i>
      {{ __('messages.top_quantity_sale_products') }}
    </h5>
  </div>

  <!-- Controls (right) -->
  <div class="report-controls d-flex flex-wrap align-items-center gap-2">
    <!-- Export CSV -->
    <a href="{{ auth()->user()->role === 'superadmin'
        ? route('superadmin.reports.top-quantity-sales.export', [
            'filter' => request('period'),
            'month' => request('month'),
            'year' => request('year'),
            'category_id' => request('category_id'),
        ])
        : route('admin.reports.top-quantity-sales.export', [
            'filter' => request('period'),
            'month' => request('month'),
            'year' => request('year'),
            'category_id' => request('category_id'),
        ]) }}"
       class="btn btn-outline-success rounded-pill px-4">
      ⬇️ {{ __('messages.export_csv') }}
    </a>

    <!-- Print PDF -->
    <a href="{{ auth()->user()->role === 'superadmin'
        ? route('superadmin.reports.top-quantity-sales.pdf', [
            'filter' => request('period'),
            'month' => request('month'),
            'year' => request('year'),
            'category_id' => request('category_id'),
        ])
        : route('admin.reports.top-quantity-sales.pdf', [
            'filter' => request('period'),
            'month' => request('month'),
            'year' => request('year'),
            'category_id' => request('category_id'),
        ]) }}"
       class="btn btn-outline-primary rounded-pill px-4">
      🖨️ {{ __('messages.print') }}
    </a>

    <!-- Filters -->
    <form id="top-qty-form" method="GET"
          action="{{ auth()->user()->role === 'superadmin' ? route('superadmin.reports.topQuantitySales') : route('admin.reports.topQuantitySales') }}"
          class="d-flex flex-wrap align-items-center gap-2">
      <select name="period"
              class="form-select rounded-pill shadow-sm"
              style="width: 160px;"
              onchange="this.form.submit()">
        <option value="today"  {{ request('period','all')=='today'  ? 'selected':'' }}>{{ __('messages.today') }}</option>
        <option value="week"   {{ request('period','all')=='week'   ? 'selected':'' }}>{{ __('messages.this_week') }}</option>
        <option value="month"  {{ request('period','all')=='month'  ? 'selected':'' }}>{{ __('messages.this_month') }}</option>
        <option value="all"    {{ request('period','all')=='all'    ? 'selected':'' }}>{{ __('messages.all_day') }}</option>
      </select>

      <select name="category_id"
              class="form-select rounded-pill shadow-sm"
              style="width: 200px;"
              onchange="this.form.submit()">
        <option value="">{{ __('messages.all_categories') }}</option>
        {!! render_category_options($categories, request('category_id')) !!}
      </select>
    </form>
  </div>
</div>

<!-- ===== Results counter (small, right) ===== -->
<div class="d-flex justify-content-end mb-2">
  <div class="text-muted small">
    {{ __('messages.showing_results') }} <strong>{{ $topProducts->count() }}</strong> {{ __('messages.items') }}
  </div>
</div>

<!-- ===== Table ===== -->
<div class="row g-4">
  <div class="col-12">
    <div class="card report-card border-0 shadow-sm">
      <div class="table-responsive">
        <table class="table report-table align-middle mb-0">
          <thead class="sticky-top bg-white">
            <tr>
              <th class="text-center" style="width:90px"></th>
              <th>{{ __('messages.product') }}</th>
              <th class="text-center" style="width:240px">{{ __('messages.category') }}</th>
              <th class="text-center" style="width:260px">{{ __('messages.total_quantity') }}</th>
              <th class="text-center" style="width:160px">{{ __('messages.month') }}</th>
              <th style="width:120px">{{ __('messages.year') }}</th>
            </tr>
          </thead>

          <tbody>
            @forelse($topProducts as $i => $item)
              @php
                $rank = $i + 1;
                $qty  = (int) $item->total_quantity;
                $pct  = min(100, round(($qty / $maxQty) * 100));
              @endphp
              <tr>
                <!-- Rank -->
                <td class="text-center">
                  {{-- Optional medals for top 3
                  @if($rank <= 3)
                    <span class="rank-badge rank-{{ $rank }}" aria-hidden="true">
                      {{ ['🥇','🥈','🥉'][$rank-1] }}
                    </span>
                  @endif --}}
                  <span class="rank-chip ms-1">{{ $rank }}</span>
                </td>

                <!-- Product -->
                <td class="fw-semibold">
                  {{ $item->product->name ?? 'N/A' }}
                </td>

                <!-- Category -->
                <td class="text-center">
                  <span class="cat-chip">
                    <i class="bi bi-tag me-1"></i>{{ $item->category_name }}
                  </span>
                </td>

                <!-- Quantity -->
                <td class="text-center">
                  <div class="qty-cell justify-content-center">
                    <div class="qty-bar" style="--pct: {{ $pct }}%"></div>
                    <span class="qty-label fw-bold">{{ $qty }}</span>
                  </div>
                </td>

                <!-- Month -->
                <td class="text-center">
                  {{ \Carbon\Carbon::create()->month($item->month)->format('F') }}
                </td>

                <!-- Year -->
                <td>{{ $item->year }}</td>
              </tr>
            @empty
              <tr>
                <td colspan="6" class="text-center text-muted py-4">
                  {{ __('messages.no_data_available') }}
                </td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>

      @if(method_exists($topProducts, 'links'))
        <div class="card-footer bg-white border-0 px-3 py-2">
          {{ $topProducts->links() }}
        </div>
      @endif
    </div>
  </div>
</div>
@endsection
