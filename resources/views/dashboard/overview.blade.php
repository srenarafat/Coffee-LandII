{{-- resources/views/dashboard/overview.blade.php --}}

<div class="container-fluid mt-1">

  {{-- KPIs --}}
  <div class="row g-2 mb-2">
    {{-- Card 1: Today's Sales (quick-action style) --}}
    <div class="col-6 col-lg-3">
      <a href="{{ route($routePrefix . '.reports.sales.today') }}" class="kpi-link">
        <div class="card kpi kpi-teal kpi-action text-center h-100">
          <div class="card-body">
            <div class="kpi-label">Today's Sales</div>
            <div class="kpi-value">
              {{ optional($setting)->currency ?? '$' }}{{ number_format($todaySalesTotal ?? 0, 2) }}
            </div>
            <div class="small kpi-cta"></div>
          </div>
        </div>
      </a>
    </div>

    {{-- Card 2: Orders --}}
    <div class="col-6 col-lg-3">
      <div class="card kpi kpi-emerald text-center h-100">
        <div class="card-body">
          <div class="kpi-label">Orders</div>
          <div class="kpi-value">{{ $todayOrderCount ?? 0 }}</div>
        </div>
      </div>
    </div>

    {{-- Card 3: Items Sold --}}
    <div class="col-6 col-lg-3">
      <div class="card kpi kpi-indigo text-center h-100">
        <div class="card-body">
          <div class="kpi-label">Items Sold</div>
          <div class="kpi-value">{{ $todayItemsSold ?? 0 }}</div>
        </div>
      </div>
    </div>

    {{-- Card 4: Avg Order Value --}}
    <div class="col-6 col-lg-3">
      <div class="card kpi kpi-rose text-center h-100">
        <div class="card-body">
          <div class="kpi-label">Avg Order Value</div>
          <div class="kpi-value">
            {{ optional($setting)->currency ?? '$' }}{{ number_format($todayAverageOrderValue ?? 0, 2) }}
          </div>
        </div>
      </div>
    </div>
  </div>

  {{-- Quick Actions (6 tiles) --}}
  <div class="row g-2 mb-2">
    @php
      $tiles = [
        // Weekly Sales (Quick Action)
        ['href'=>route($routePrefix.'.reports.sales.week'),        'icon'=>'bi-calendar-week',       'title'=>"Weekly Sales",             'desc'=>'Total: <span id="week-sales-total">'.(optional($setting)->currency ?? '$').number_format($weekSalesTotal ?? 0, 2).'</span>', 'variant'=>'teal'],
        // Z Report
        ['href'=>route($routePrefix.'.reports.zreport'),           'icon'=>'bi-receipt',             'title'=>'Z Report',                 'desc'=>'Printable cash summary',                                            'variant'=>'slate'],
        // Export / Print Sales
        ['href'=>route($routePrefix.'.reports.sales.export'),      'icon'=>'bi-filetype-csv',        'title'=>'Export / Print Sales',     'desc'=>'CSV & print with filters',                                         'variant'=>'emerald'],
        // Low Stock
        ['href'=>route($routePrefix.'.stock.low'),                 'icon'=>'bi-exclamation-triangle','title'=>'Low Stock',                'desc'=>'Below threshold', 'badge'=>$lowStockCount,                      'variant'=>'amber'],
        // Top Products (Week)
        ['href'=>route($routePrefix.'.reports.top-products.week'), 'icon'=>'bi-stars',               'title'=>'Top Products (Week)',      'desc'=>'Best sellers',                                                     'variant'=>'indigo'],
        // Slow Movers
        ['href'=>route($routePrefix.'.reports.slow-products'),     'icon'=>'bi-hourglass-split',     'title'=>'Slow Movers',              'desc'=>'Identify for promotion',                                           'variant'=>'rose'],
      ];
    @endphp

    @foreach($tiles as $t)
      <div class="col-12 col-sm-6 col-lg-4">
        <a href="{{ $t['href'] }}" class="text-decoration-none">
          <div class="card tile tile-{{ $t['variant'] }} h-100">
            <div class="card-body d-flex align-items-start gap-3">
              <div class="tile-icon"><i class="bi {{ $t['icon'] }}"></i></div>
              <div class="flex-grow-1">
                <div class="fw-semibold text-dark">{{ $t['title'] }}</div>
                <div class="text-muted small">{!! $t['desc'] !!}</div>
              </div>
              @isset($t['badge'])
                <span class="badge tile-badge">{{ $t['badge'] }}</span>
              @endisset
            </div>
          </div>
        </a>
      </div>
    @endforeach
  </div>
</div>

{{-- Charts & Recent Transactions --}}
<div class="container-fluid">
  <div class="row g-2 align-items-stretch mb-2">

    {{-- Chart --}}
    <div class="col-12 col-lg-6 d-flex">
      <div class="card surface flex-fill h-100">
        {{-- HEADER: centered title + equal height --}}
        <div class="card-header surface-header equal-header d-flex justify-content-between align-items-center position-relative">
          <span class="header-title-center text-black fw-semibold">{{ __('messages.sales_earnings') }}</span>
          <div class="btn-group btn-group-sm mb-0" role="group" aria-label="Sales range">
            <button type="button" class="btn btn-outline-brown range-btn text-black fw-semibold" data-range="today">Today</button>
            <button type="button" class="btn btn-outline-brown range-btn text-black fw-semibold" data-range="week">Week</button>
            <button type="button" class="btn btn-outline-brown range-btn text-black fw-semibold" data-range="month">Month</button>
          </div>
        </div>

        <div class="card-body">
          <canvas id="barChart" height="170" aria-label="Sales chart" role="img"></canvas>
        </div>
      </div>
    </div>


    {{-- Transactions --}}
    <div class="col-12 col-lg-6 d-flex">
      <div class="card surface flex-fill h-100">
        <div class="card-header surface-header text-black text-center fw-semibold">{{ __('messages.recent_transactions') }}</div>
        <div class="card-body p-0">
          <div class="overflow-y-auto" style="max-height: 24rem;">
            <table class="table table-striped mb-0 text-center align-middle">
              <thead class="table-sticky custom-blue-header text-black">
                <tr>
                  <th>{{ __('messages.invoice') }}</th>
                  <th>{{ __('messages.date') }}</th>
                  <th>{{ __('messages.cashier') }}</th>
                  <th class="text-end">{{ __('messages.amount') }}</th>
                  <th class="text-end">{{ __('messages.discount') }}</th>
                  <th class="text-end">{{ __('messages.total') }}</th>
                </tr>
              </thead>
              <tbody>
              @forelse ($recentSales as $sale)
                <tr>
                  <td>{{ $sale->invoice_no }}</td>
                  <td>{{ $sale->created_at->format('d/m/Y, H:i') }}</td>
                  <td>{{ $sale->user->name ?? 'N/A' }}</td>
                  <td class="text-end px-3 py-2">{{ optional($setting)->currency ?? '$' }}{{ number_format($sale->subtotal, 2) }}</td>
                  <td class="text-end px-3 py-2">{{ optional($setting)->currency ?? '$' }}{{ number_format($sale->discount ?? 0, 2) }}</td>
                  <td class="text-end px-3 py-2 fw-bold">{{ optional($setting)->currency ?? '$' }}{{ number_format($sale->total, 2) }}</td>
                </tr>
              @empty
                <tr>
                  <td colspan="6" class="text-muted text-center">{{ __('messages.no_transactions_found') }}</td>
                </tr>
              @endforelse
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
<style>
/* Same height for both headers */
:root { --header-h: 42px; }              /* adjust 52–60px if needed */
.equal-header{
  min-height: var(--header-h);
  display:flex;
  align-items:center;
  padding:.40rem .9rem;
}

/* Center the left header title while keeping buttons clickable */
.header-title-center{
  position:absolute; left:50%; top:50%;
  transform:translate(-50%, -50%);
  white-space:nowrap;
  pointer-events:none;
}

/* Tighten buttons so header stays compact */
.surface-header .btn-group{ margin:0; }
.surface-header .btn-group .btn{
  padding:.25rem .5rem;
  line-height:1.1;
}


  :root{
    --brown:#5c4033; --brown-100:#fff3ec;
    --slate-50:#f8fafc; --slate-200:#e2e8f0; --slate-600:#475569;
    --teal-50:#f0fdfa; --teal-100:#ccfbf1; --teal-600:#0d9488;
    --amber-50:#fffbeb; --amber-100:#fef3c7; --amber-600:#d97706;
    --indigo-50:#eef2ff; --indigo-100:#e0e7ff; --indigo-600:#4f46e5;
    --emerald-50:#ecfdf5; --emerald-100:#d1fae5; --emerald-600:#059669;
    --rose-50:#fff1f2; --rose-100:#ffe4e6; --rose-600:#e11d48;
  }

  body{ background:#faf7f5; }

  /* Surfaces (cards & tables) */
  .surface{ border:0; box-shadow:0 .25rem .75rem rgba(0,0,0,.06); border-radius:14px; }

  /* Card header — brand look */
  .surface-header{
    background: linear-gradient(180deg, rgba(180,127,75,.96) 0%, rgba(190,126,83,.86) 100%);
    color:#fff;
    border-bottom:none;
    padding:.52rem .9rem;
  }
  .card.surface .surface-header{
    border-top-left-radius:14px;
    border-top-right-radius:14px;
  }

  /* Buttons inside the dark header (Today/Week/Month) */
  .btn-outline-brown{
    --bs-btn-color: var(--brown);
    --bs-btn-border-color: var(--brown);
    --bs-btn-hover-bg: var(--brown);
    --bs-btn-hover-border-color: var(--brown);
    --bs-btn-hover-color:#fff;
  }
  .surface-header .btn-outline-brown{
    --bs-btn-color:#fff;
    --bs-btn-border-color:rgba(255,255,255,.55);
    --bs-btn-hover-bg:#fff;
    --bs-btn-hover-border-color:#fff;
    --bs-btn-hover-color:var(--brown);
    --bs-btn-active-bg:#fff;
    --bs-btn-active-border-color:#fff;
    --bs-btn-active-color:var(--brown);
  }
  .surface-header .range-btn.active{
    background:#fff;
    color:var(--brown);
    border-color:#fff;
  }

  /* Make KPI act like a quick-action tile */
  .kpi-link{ display:block; text-decoration:none; }
  .kpi-action{ cursor:pointer; transition:transform .15s ease, box-shadow .15s ease; }
  .kpi-action:hover{ transform:translateY(-2px); box-shadow:0 .8rem 1.6rem rgba(0,0,0,.08); }
  .kpi-action:active{ transform:translateY(0); }
  .kpi-action:focus-within{ outline:2px solid var(--teal-600); outline-offset:2px; }
  .kpi-cta{ color:#6b7280; opacity:.9; }

  /* KPI cards */
  .kpi{ border:0; border-radius:16px; box-shadow:0 .35rem .9rem rgba(0,0,0,.07); }
  .kpi .card-body{ padding:18px 16px; }
  .kpi-label{ font-size:.82rem; color:#6b7280; }
  .kpi-value{ font-weight:800; font-size:1.25rem; color:#0f172a; }
  .kpi-teal{ background:linear-gradient(180deg,var(--teal-50),#fff); border:1px solid var(--teal-100); }
  .kpi-emerald{ background:linear-gradient(180deg,var(--emerald-50),#fff); border:1px solid var(--emerald-100); }
  .kpi-indigo{ background:linear-gradient(180deg,var(--indigo-50),#fff); border:1px solid var(--indigo-100); }
  .kpi-rose{ background:linear-gradient(180deg,var(--rose-50),#fff); border:1px solid var(--rose-100); }

  /* Action tiles */
  .tile{ border:0; border-radius:14px; box-shadow:0 .25rem .75rem rgba(0,0,0,.06); transition:transform .15s ease, box-shadow .15s ease; }
  .tile:hover{ transform:translateY(-2px); box-shadow:0 .8rem 1.6rem rgba(0,0,0,.08); }
  .tile:focus,
  .tile:focus-visible{
    outline:3px solid var(--brown);
    outline-offset:3px;
  }
  .tile-icon{
    width:42px; height:42px; display:grid; place-items:center;
    border-radius:10px; font-size:20px;
  }
  .tile-badge{ background:#0f172a; color:#fff; border-radius:999px; padding:.2rem .5rem; font-weight:700; }

  .tile-teal .tile-icon{ background:var(--teal-50); color:var(--teal-600); }
  .tile-amber .tile-icon{ background:var(--amber-50); color:var(--amber-600); }
  .tile-indigo .tile-icon{ background:var(--indigo-50); color:var(--indigo-600); }
  .tile-emerald .tile-icon{ background:var(--emerald-50); color:var(--emerald-600); }
  .tile-rose .tile-icon{ background:var(--rose-50); color:var(--rose-600); }
  .tile-slate .tile-icon{ background:#eef2f7; color:var(--slate-600); }

  /* Tables */
  .custom-blue-header th{ background:#d8eaff!important; color:#000!important; font-weight:700 }
  .table-sticky{ position:sticky; top:0; z-index:2 }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
  // Initial data passed from controller
  const initialLabels = @json($chartLabels);
  const initialData   = @json($chartData);
  const routePrefix   = @json($routePrefix);

  const ctx = document.getElementById('barChart').getContext('2d');
  let chart = new Chart(ctx, {
    type: 'line',
    data: {
      labels: initialLabels,
      datasets: [{
        label: '{{ __('messages.sales') }}',
        data: initialData,
        fill: true,
        backgroundColor: 'rgba(0, 200, 200, 0.22)',
        borderColor: 'rgba(0, 160, 160, 1)',
        tension: .45,
        pointRadius: 0
      }]
    },
    options: {
      responsive: true,
      interaction: { mode: 'index', intersect: false },
      scales: {
        y: { beginAtZero: true, grid: { color:'rgba(0,0,0,.06)'} },
        x: { grid: { display: false } }
      },
      plugins: { legend: { display: false } }
    }
  });

  // Range buttons -> fetch new data
  document.querySelectorAll('.range-btn').forEach(btn => {
    btn.addEventListener('click', () => {
      const range = btn.dataset.range;
      fetch(`/${routePrefix}/dashboard/sales-data/${range}`)
        .then(r => r.json())
        .then(({labels, totals}) => {
          chart.data.labels = labels;
          chart.data.datasets[0].data = totals;
          chart.update();
        })
        .catch(() => {/* ignore */});
    });
  });
</script>
@endpush
