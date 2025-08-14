{{-- resources/views/dashboard/overview.blade.php --}}

<div class="container-fluid mt-0.50">

  {{-- Brand badge --}}
  <div class="d-flex align-items-start mb-1">
    <h1 class="fw-bold mb-0 brand-badge">
      {{ optional($setting)->shop_name ?? 'COFFEE LAND' }}
    </h1>
  </div>

  {{-- KPIs --}}
  <div class="row g-3 mb-2">
    <div class="col-6 col-lg-3">
      <div class="card shortcut-card h-100 text-center">
        <div class="card-body">
          <div class="text-muted small">Today's Sales</div>
          <div class="fs-5 fw-bold">{{ optional($setting)->currency ?? '$' }}{{ number_format($todaySalesTotal, 2) }}</div>
        </div>
      </div>
    </div>
    <div class="col-6 col-lg-3">
      <div class="card shortcut-card h-100 text-center">
        <div class="card-body">
          <div class="text-muted small">Orders</div>
          <div class="fs-5 fw-bold">{{ $todayOrderCount }}</div>
        </div>
      </div>
    </div>
    <div class="col-6 col-lg-3">
      <div class="card shortcut-card h-100 text-center">
        <div class="card-body">
          <div class="text-muted small">Items Sold</div>
          <div class="fs-5 fw-bold">{{ $todayItemsSold }}</div>
        </div>
      </div>
    </div>
    <div class="col-6 col-lg-3">
      <div class="card shortcut-card h-100 text-center">
        <div class="card-body">
          <div class="text-muted small">Avg Order Value</div>
          <div class="fs-5 fw-bold">{{ optional($setting)->currency ?? '$' }}{{ number_format($todayAverageOrderValue, 2) }}</div>
        </div>
      </div>
    </div>
  </div>

  {{-- Quick Actions (6 tiles) --}}
  <div class="row g-2 mb-2">
    @php
      $tiles = [
        ['href'=>route($routePrefix.'.reports.sales.today'),       'icon'=>'bi-graph-up',           'title'=>"Today’s Sales",            'desc'=>'Total: <span id="today-sales-total">0</span>'],
        ['href'=>route($routePrefix.'.reports.zreport'),           'icon'=>'bi-receipt',            'title'=>'Z Report',
         'desc'=>'Printable cash summary'],
        ['href'=>route($routePrefix.'.stock.low'),                 'icon'=>'bi-exclamation-triangle','title'=>'Low Stock',
         'desc'=>'Below threshold', 'badge'=>$lowStockCount],
        ['href'=>route($routePrefix.'.reports.top-products.week'), 'icon'=>'bi-stars',              'title'=>'Top Products (Week)',
         'desc'=>'Best sellers'],
        ['href'=>route($routePrefix.'.reports.sales.export'),      'icon'=>'bi-filetype-csv',       'title'=>'Export / Print Sales',
         'desc'=>'CSV & print with filters'],
        ['href'=>route($routePrefix.'.reports.slow-products'),     'icon'=>'bi-hourglass-split',    'title'=>'Slow Movers',
         'desc'=>'Identify for promotion'],
      ]
    @endphp

    @foreach($tiles as $t)
      <div class="col-12 col-sm-6 col-lg-4">
        <a href="{{ $t['href'] }}" class="text-decoration-none">
          <div class="card shortcut-card h-100">
            <div class="card-body d-flex align-items-start gap-3">
              <div class="shortcut-icon">
                <i class="bi {{ $t['icon'] }} fs-4"></i>
              </div>
              <div>
                <div class="fw-semibold text-dark">{{ $t['title'] }}</div>
                <div class="text-muted small">{!! $t['desc'] !!}</div>
                @isset($t['value'])
                  <div class="fw-bold mt-1">{{ $t['value'] }}</div>
                @endisset
              </div>
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
    <div class="card shortcut-card h-100 position-relative">
            @isset($t['badge'])
              <span class="shortcut-badge badge rounded-pill">{{ $t['badge'] }}</span>
            @endisset
      <div class="card-header d-flex justify-content-between align-items-center">
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
    <div class="card shadow-sm h-100">
      <div class="card-header fw-semibold">{{ __('messages.recent_transactions') }}</div>
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
  :root{ --brown:#5c4033; --brown-100:#fff3ec; }
  .brand-badge{
    border:1px solid #fff; background:var(--brown); color:#fff;
    padding:2px 10px; font-size:1rem; border-radius:6px;
  }
  .shortcut-card{ border:0; box-shadow:0 .25rem .75rem rgba(0,0,0,.06); transition:.2s }
  .shortcut-card:hover{ transform:translateY(-2px); box-shadow:0 .75rem 1.5rem rgba(0,0,0,.08) }
  .shortcut-icon{ background:var(--brown-100); color:var(--brown); border-radius:.75rem; padding:.75rem }
  .btn-outline-brown{
    --bs-btn-color: var(--brown); --bs-btn-border-color: var(--brown);
    --bs-btn-hover-bg: var(--brown); --bs-btn-hover-border-color: var(--brown);
    --bs-btn-hover-color: #fff;
  }
  .custom-blue-header th{ background:#d8eaff!important; color:#000!important; font-weight:700 }
  .table-sticky{ position:sticky; top:0; z-index:2 }
  .shortcut-badge{ position:absolute; top:.5rem; right:.5rem; background:var(--brown); color:#fff; }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
  const ctx = document.getElementById('barChart').getContext('2d');
  let chart = new Chart(ctx, {
    type: 'line',
    data: {
      labels: @json($chartLabels),
      datasets: [{
        label: '{{ __('messages.sales') }}',
        data: @json($chartData),
        fill: true,
        backgroundColor: 'rgba(0, 255, 255, 0.25)',
        borderColor: 'rgba(0, 180, 180, 1)',
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
      plugins: {
        legend: { display: false },
        tooltip: {
          enabled: true,
          backgroundColor: '#fff',
          titleColor: '#000', bodyColor:'#000',
          borderColor:'#ddd', borderWidth:1
        }
      }
    }
  });

  const routePrefix = @json($routePrefix);
  const currency = @json(optional($setting)->currency ?? '$');

  function updateTodaySales(){
    fetch(`/${routePrefix}/dashboard/today-sales-total`)
      .then(r=>r.json())
      .then(data=>{
        const el = document.getElementById('today-sales-total');
        if (el) {
          el.textContent = currency + parseFloat(data.total).toFixed(2);
        }
      });
  }
  updateTodaySales();
  setInterval(updateTodaySales, 60000);

  document.querySelectorAll('.range-btn').forEach(btn=>{
    btn.addEventListener('click', ()=>{
      const range = btn.dataset.range;
      fetch(`/${routePrefix}/dashboard/sales-data/${range}`)
        .then(r=>r.json())
        .then(data=>{
          chart.data.labels = data.labels;
          chart.data.datasets[0].data = data.totals;
          chart.update();
        });
    });
  });
</script>
@endpush
