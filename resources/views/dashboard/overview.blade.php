{{-- resources/views/dashboard/overview.blade.php --}}

<div class="container-fluid mt-1">

  {{-- KPIs --}}
  <div class="row g-2 mb-2">
    {{-- Card 1: Today's Sales (quick-action style) --}}
    <div class="col-6 col-lg-3">
      <a href="{{ route($routePrefix . '.reports.sales.today') }}" class="kpi-link" title="View today's sales report">
        <div class="card kpi kpi-teal kpi-action kpi-filled text-center h-100">
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
      <div class="card kpi kpi-emerald kpi-filled text-center h-100">
        <div class="card-body">
          <div class="kpi-label">Orders</div>
          <div class="kpi-value">{{ $todayOrderCount ?? 0 }}</div>
        </div>
      </div>
    </div>

    {{-- Card 3: Items Sold --}}
    <div class="col-6 col-lg-3">
      <div class="card kpi kpi-indigo kpi-filled text-center h-100">
        <div class="card-body">
          <div class="kpi-label">Items Sold</div>
          <div class="kpi-value">{{ $todayItemsSold ?? 0 }}</div>
        </div>
      </div>
    </div>

    {{-- Card 4: Avg Order Value --}}
    <div class="col-6 col-lg-3">
      <div class="card kpi kpi-rose kpi-filled text-center h-100">
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
        ['href'=>route($routePrefix.'.reports.sales.week'),        'icon'=>'bi-calendar-week',        'title'=>"Weekly Sales",          'desc'=>'Total: <span id="week-sales-total">'.(optional($setting)->currency ?? '$').number_format($weekSalesTotal ?? 0, 2).'</span>', 'variant'=>'teal'],
        ['href'=>route($routePrefix.'.reports.zreport'),           'icon'=>'bi-receipt',              'title'=>'Z Report',              'desc'=>'Printable cash summary',                                            'variant'=>'slate'],
        ['href'=>route($routePrefix.'.reports.sales.export'),      'icon'=>'bi-filetype-csv',         'title'=>'Export / Print Sales',  'desc'=>'CSV & print with filters',                                         'variant'=>'emerald'],
        ['href'=>route($routePrefix.'.stock.low'),                 'icon'=>'bi-exclamation-triangle', 'title'=>'Low Stock',             'desc'=>'Below threshold', 'badge'=>$lowStockCount,                      'variant'=>'amber'],
        ['href'=>route($routePrefix.'.reports.top-products.week'), 'icon'=>'bi-stars',                'title'=>'Top Products (Week)',   'desc'=>'Best sellers',                                                     'variant'=>'indigo'],
        ['href'=>route($routePrefix.'.reports.slow-products'),     'icon'=>'bi-hourglass-split',      'title'=>'Slow Movers',           'desc'=>'Identify for promotion',                                           'variant'=>'rose'],
      ];
    @endphp

    @foreach($tiles as $t)
      <div class="col-12 col-sm-6 col-lg-4">
        <a href="{{ $t['href'] }}" class="text-decoration-none">
          <div class="card tile tile-{{ $t['variant'] }} tile-filled h-100">
            <div class="card-body d-flex align-items-start gap-3">
              <div class="tile-icon"><i class="bi {{ $t['icon'] }}"></i></div>
              <div class="flex-grow-1">
                <div class="fw-semibold">{{ $t['title'] }}</div>
                <div class="small">{!! $t['desc'] !!}</div>
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
        <div class="card-header surface-header equal-header d-flex justify-content-between align-items-center position-relative">
          <span class="header-title-center fw-semibold">{{ __('messages.sales_earnings') }}</span>
          <div class="btn-group btn-group-sm mb-0" role="group" aria-label="Sales range">
            <button type="button" class="btn btn-outline-brown range-btn fw-semibold" data-range="today">Today</button>
            <button type="button" class="btn btn-outline-brown range-btn fw-semibold" data-range="week">Week</button>
            <button type="button" class="btn btn-outline-brown range-btn fw-semibold" data-range="month">Month</button>
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
        <div class="card-header surface-header equal-header d-flex justify-content-center align-items-center">
          <span class="fw-semibold">{{ __('messages.recent_transactions') }}</span>
        </div>
        <div class="card-body p-0">
          <div class="overflow-y-auto" style="max-height: 24rem;">
            <table class="table table-striped mb-0 text-center align-middle">
              <thead class="table-sticky custom-blue-header">
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

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
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
        .catch(() => {});
    });
  });
</script>
@endpush
