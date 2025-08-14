{{-- resources/views/dashboard/overview.blade.php --}}

<div class="container-fluid mt-0.50">

  {{-- Brand badge --}}
  <div class="d-flex align-items-start mb-1">
    <h1 class="fw-bold mb-0 brand-badge">
      {{ optional($setting)->shop_name ?? 'COFFEE LAND' }}
    </h1>
  </div>

  {{-- KPIs --}}
  <div class="row g-3 mb-3">
  <div class="col-6 col-lg-3"><div class="card kpi kpi-teal text-center"><div class="card-body">
    <div class="kpi-label">Today's Sales</div>
    <div class="kpi-value">{{ optional($setting)->currency ?? '$' }}{{ number_format($todaySalesTotal, 2) }}</div>
  </div></div></div>

  <div class="col-6 col-lg-3"><div class="card kpi kpi-emerald text-center"><div class="card-body">
    <div class="kpi-label">Orders</div>
    <div class="kpi-value">{{ $todayOrderCount }}</div>
  </div></div></div>

  <div class="col-6 col-lg-3"><div class="card kpi kpi-indigo text-center"><div class="card-body">
    <div class="kpi-label">Items Sold</div>
    <div class="kpi-value">{{ $todayItemsSold }}</div>
  </div></div></div>

  <div class="col-6 col-lg-3"><div class="card kpi kpi-rose text-center"><div class="card-body">
    <div class="kpi-label">Avg Order Value</div>
    <div class="kpi-value">{{ optional($setting)->currency ?? '$' }}{{ number_format($todayAverageOrderValue, 2) }}</div>
  </div></div></div>
</div>

  {{-- Quick Actions (6 tiles) --}}
  <div class="row g-2 mb-2">
    @php
  $tiles = [
    ['href'=>route($routePrefix.'.reports.sales.today'),       'icon'=>'bi-graph-up',            'title'=>"Today’s Sales",            'desc'=>'Total: <span id="today-sales-total">'.(optional($setting)->currency ?? '$').number_format($todaySalesTotal, 2).'</span>', 'variant'=>'teal'],
    ['href'=>route($routePrefix.'.reports.zreport'),           'icon'=>'bi-receipt',             'title'=>'Z Report',                 'desc'=>'Printable cash summary',                                            'variant'=>'slate'],
    ['href'=>route($routePrefix.'.stock.low'),                 'icon'=>'bi-exclamation-triangle','title'=>'Low Stock',                'desc'=>'Below threshold', 'badge'=>$lowStockCount,                      'variant'=>'amber'],
    ['href'=>route($routePrefix.'.reports.top-products.week'), 'icon'=>'bi-stars',               'title'=>'Top Products (Week)',      'desc'=>'Best sellers',                                                     'variant'=>'indigo'],
    ['href'=>route($routePrefix.'.reports.sales.export'),      'icon'=>'bi-filetype-csv',        'title'=>'Export / Print Sales',     'desc'=>'CSV & print with filters',                                         'variant'=>'emerald'],
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
<div class="row">
  {{-- Chart --}}
  <div class="col-lg-6 mb-2">
    <div class="card surface h-100">
      <div class="card-header surface-header d-flex justify-content-between align-items-center">
        <span class="fw-semibold">{{ __('messages.sales_earnings') }}</span>
        <div class="btn-group btn-group-sm" role="group" aria-label="Sales range">
          <button type="button" class="btn btn-outline-brown range-btn" data-range="today">Today</button>
          <button type="button" class="btn btn-outline-brown range-btn" data-range="week">Week</button>
          <button type="button" class="btn btn-outline-brown range-btn" data-range="month">Month</button>
        </div>
      </div>
      <div class="card-body">
        <canvas id="barChart" height="170" aria-label="Sales chart" role="img"></canvas>
      </div>
    </div>
  </div>

  {{-- Transactions --}}
  <div class="col-lg-6 mb-3">
    <div class="card surface h-100">
      <div class="card-header surface-header fw-semibold">{{ __('messages.recent_transactions') }}</div>
      <div class="card-body p-0">
        <div class="overflow-y-auto" style="max-height: 22rem;">
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

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
<style>
  .dashboard-box {
    border: 2px solid #ccc; /* Thicker border */
    border-radius: 12px; /* Keep rounded corners */
    font-weight: bold; /* Make text bolder */
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1); /* Slight shadow for depth */
    padding: 12px;
}

.dashboard-box h5, 
.dashboard-box span, 
.dashboard-box p {
    font-weight: bold; /* Make all text inside bold */
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

  body{ background:#faf7f5; } /* overall warmer canvas */
  .brand-badge{ border:1px solid #fff; background:var(--brown); color:#fff; padding:2px 10px; font-size:1rem; border-radius:6px; }

  /* Surfaces (cards & tables) */
  .surface{ border:0; box-shadow:0 .25rem .75rem rgba(0,0,0,.06); }
  .surface-header{ background:var(--slate-50); border-bottom:1px solid var(--slate-200); }

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
  .tile-icon{ width:42px; height:42px; display:grid; place-items:center; border-radius:10px; font-size:20px; }
  .tile-badge{ background:#0f172a; color:#fff; border-radius:999px; padding:.2rem .5rem; font-weight:700; }

  .tile-teal .tile-icon{ background:var(--teal-50); color:var(--teal-600); }
  .tile-amber .tile-icon{ background:var(--amber-50); color:var(--amber-600); }
  .tile-indigo .tile-icon{ background:var(--indigo-50); color:var(--indigo-600); }
  .tile-emerald .tile-icon{ background:var(--emerald-50); color:var(--emerald-600); }
  .tile-rose .tile-icon{ background:var(--rose-50); color:var(--rose-600); }
  .tile-slate .tile-icon{ background:#eef2f7; color:var(--slate-600); }

  /* Buttons */
  .btn-outline-brown{
    --bs-btn-color: var(--brown); --bs-btn-border-color: var(--brown);
    --bs-btn-hover-bg: var(--brown); --bs-btn-hover-border-color: var(--brown);
    --bs-btn-hover-color:#fff;
  }

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


