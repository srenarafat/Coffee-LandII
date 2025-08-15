{{-- resources/views/dashboard/overview.blade.php --}}

<script>
  window.initialOrders = @json($chartOrders ?? []);
  window.initialItems  = @json($chartItems ?? []);
  window.initialAov    = @json($chartAov ?? []);
</script>

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

  {{-- Customer Metrics --}}
  <div class="row g-2 mb-2">
    <div class="col-6 col-lg-4">
      <div class="card kpi kpi-teal kpi-filled text-center h-100">
        <div class="card-body">
          <div class="kpi-label">New Customers</div>
          <div class="kpi-value">{{ $newCustomers ?? 0 }}</div>
        </div>
      </div>
    </div>
    <div class="col-6 col-lg-4">
      <div class="card kpi kpi-emerald kpi-filled text-center h-100">
        <div class="card-body">
          <div class="kpi-label">Returning Customers</div>
          <div class="kpi-value">{{ $returningCustomers ?? 0 }}</div>
        </div>
      </div>
    </div>
    <div class="col-6 col-lg-4">
      <div class="card kpi kpi-amber kpi-filled text-center h-100">
        <div class="card-body">
          <div class="kpi-label">At-Risk Customers</div>
          <div class="kpi-value">{{ $atRiskCustomers ?? 0 }}</div>
        </div>
      </div>
    </div>
  </div>

  {{-- Quick Actions (6 tiles) --}}
  <div class="row g-2 mb-3">
    @php
      $tiles = [
        ['href'=>route($routePrefix.'.reports.sales.week'),        'icon'=>'bi-calendar-week',        'title'=>"Weekly Sales",          'desc'=>'Total: <span id="week-sales-total">'.(optional($setting)->currency ?? '$').number_format($weekSalesTotal ?? 0, 2).'</span>', 'variant'=>'teal'],
        ['href'=>route($routePrefix.'.reports.zreport'),           'icon'=>'bi-receipt',              'title'=>'Z Report',              'desc'=>'Printable cash summary',                                            'variant'=>'slate'],
        ['href'=>route($routePrefix.'.customers.new'),             'icon'=>'bi-people-fill',          'title'=>'Customer Tracking',
         'desc'=>'New: '.$newCustomers.' | Returning: '.$returningCustomers,
         'badge'=> (($atRiskCustomers ?? 0) > 0 ? $atRiskCustomers : null),
         'badgeClass'=> (($atRiskCustomers ?? 0) > 0 ? 'bg-dark' : null),
         'variant'=>'emerald'],
        ['href'=>route($routePrefix.'.stock.low'),                 'icon'=>'bi-exclamation-triangle', 'title'=>'Low Stock',             'desc'=>'Below threshold', 'badge'=>$lowStockCount,                      'variant'=>'amber'],

        // Top Products (Week) with count badge (only if > 0)
        ['href'=>route($routePrefix.'.reports.top-products.week'),
         'icon'=>'bi-stars',
         'title'=>'Top Products (Week)',
         'desc'=>'Best sellers',
         'badge'=> (($topProductsWeekCount ?? 0) > 0 ? $topProductsWeekCount : null),
         'variant'=>'indigo'],

        // Slow Movers with count badge (only if > 0)
        ['href'=>route($routePrefix.'.reports.slow-products'),
         'icon'=>'bi-hourglass-split',
         'title'=>'Slow Movers',
         'desc'=>'Identify for promotion',
         'badge'=> (($slowMoversCount ?? 0) > 0 ? $slowMoversCount : null),
         'variant'=>'rose'],
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
                <span class="badge {{ $t['badgeClass'] ?? '' }} tile-badge">{{ $t['badge'] }}</span>
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

    {{-- Sales & Earnings --}}
    <div class="col-12 col-lg-6 d-flex">
      <div class="card surface sales-card flex-fill h-100">
        <div class="card-header surface-header equal-header d-flex align-items-center justify-content-between">
          <div class="d-flex sales-tools">
            <div class="btn-group btn-group-sm" role="group" aria-label="Sales range">
              <button type="button" class="btn btn-outline-brown range-btn fw-semibold" data-range="today">Today</button>
              <button type="button" class="btn btn-outline-brown range-btn fw-semibold" data-range="week">Week</button>
              <button type="button" class="btn btn-outline-brown range-btn fw-semibold" data-range="month">{{ __('messages.this_month') }}</button>
            </div>
            
          </div>

          <span class="header-title-center fw-semibold">{{ __('messages.sales_earnings') }}</span>

          <button type="button" id="downloadChart" class="btn btn-sm btn-outline-brown">Download</button>
        </div>

        <div class="card-body">
          {{-- mini stats row (auto-updates) --}}
          <div class="row g-2 stat-row">
            <div class="col-6 col-md-auto">
              <div class="stat-chip">
                <span class="label">Revenue</span>
                <span id="statRevenue" class="value">{{ optional($setting)->currency ?? '$' }}{{ number_format($todaySalesTotal ?? 0, 2) }}</span>
              </div>
            </div>
            <div class="col-6 col-md-auto">
              <div class="stat-chip">
                <span class="label">Orders</span>
                <span id="statOrders" class="value">{{ $todayOrderCount ?? 0 }}</span>
              </div>
            </div>
            <div class="col-6 col-md-auto">
              <div class="stat-chip">
                <span class="label">Items</span>
                <span id="statItems" class="value">{{ $todayItemsSold ?? 0 }}</span>
              </div>
            </div>
            <div class="col-6 col-md-auto">
              <div class="stat-chip">
                <span class="label">AOV</span>
                <span id="statAov" class="value">{{ optional($setting)->currency ?? '$' }}{{ number_format($todayAverageOrderValue ?? 0, 2) }}</span>
              </div>
            </div>
          </div>

          <canvas id="barChart" height="170" aria-label="Sales chart" role="img"></canvas>
        </div>
      </div>
    </div>

    {{-- Recent Transactions --}}
    <div class="col-12 col-lg-6 d-flex">
      <div class="card surface flex-fill h-100">
        <div class="card-header surface-header equal-header d-flex justify-content-center align-items-center">
          <span class="fw-semibold">{{ __('messages.recent_transactions') }}</span>
        </div>
        <div class="card-body p-0 table-wrap">
          <div class="overflow-y-auto" style="max-height: 24rem;">
            <table class="table table-striped table-modern mb-0 text-center align-middle">
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
                  @php $hasDiscount = ($sale->discount ?? 0) > 0; @endphp
                  <tr>
                    <td>{{ $sale->invoice_no }}</td>
                    <td>{{ $sale->created_at->format('d/m/Y, H:i') }}</td>
                    <td>{{ $sale->user->name ?? 'N/A' }}</td>
                    <td class="num">{{ optional($setting)->currency ?? '$' }}{{ number_format($sale->subtotal, 2) }}</td>
                    <td class="num">
                      @if($hasDiscount)
                        <span class="disc-pill">{{ optional($setting)->currency ?? '$' }}{{ number_format($sale->discount, 2) }}</span>
                      @else
                        {{ optional($setting)->currency ?? '$' }}{{ number_format($sale->discount ?? 0, 2) }}
                      @endif
                    </td>
                    <td class="num fw-bold">{{ optional($setting)->currency ?? '$' }}{{ number_format($sale->total, 2) }}</td>
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
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
<style>
  /* ---- Prevent Sales & Earnings header collisions & layout ---- */
  .sales-card .surface-header{ display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; row-gap:.4rem; }
  .sales-card .sales-tools{ display:flex; gap:.5rem; flex-wrap:wrap; }
  .sales-card .header-title-center{ position:static; transform:none; margin:0 auto; order:2; pointer-events:none; }
  .sales-card #downloadChart{ order:3; }
  @media (min-width: 992px){
    .sales-card .surface-header{ flex-wrap:nowrap; }
    .sales-card .header-title-center{ position:absolute; left:50%; top:50%; transform:translate(-50%, -50%); order:0; }
  }

  /* ---- Mini stat chips ---- */
  .stat-row{ margin:.25rem 0 .5rem; }
  .stat-chip{ display:flex; align-items:center; gap:.5rem; background:rgba(255,255,255,.7); border:1px solid rgba(0,0,0,.06); border-radius:999px; padding:.25rem .6rem; box-shadow:0 2px 6px rgba(0,0,0,.04); font-weight:600; white-space:nowrap; }
  .stat-chip .label{ color:#475569; font-size:.78rem; }
  .stat-chip .value{ font-variant-numeric:tabular-nums; color:#0f172a; }
  @media (max-width:576px){ .stat-row .stat-chip{ width:100%; justify-content:space-between; } }

  /* ---- Dataset toggle look ---- */
  .dataset-toggle .btn{ --bs-btn-padding-y:.2rem; --bs-btn-padding-x:.55rem; --bs-btn-font-size:.8rem; border-color:var(--brown); color:var(--brown); }
  .dataset-toggle .btn.active{ background:var(--brown); color:#fff; border-color:var(--brown); }

  /* ---- Table polish ---- */
  .table-modern tbody tr:hover{ background:#f7fbff; }
  .table-modern th, .table-modern td{ padding:.6rem .75rem; }
  .table-modern .num{ font-variant-numeric:tabular-nums; text-align:right; }
  .disc-pill{ display:inline-block; min-width:3.25rem; text-align:center; background:#fee2e2; color:#991b1b; border:1px solid #fecaca; padding:.1rem .35rem; border-radius:999px; font-weight:700; }
  .surface .table-wrap{ border-bottom-left-radius:14px; border-bottom-right-radius:14px; overflow:hidden; }

  /* ---- Black count badge for tiles (good on any color) ---- */
  .tile-badge{ background:#000; color:#fff; border:0; min-width:1.6rem; height:1.6rem; display:grid; place-items:center; font-weight:800; line-height:1; border-radius:999px; box-shadow:0 2px 6px rgba(0,0,0,.25); }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
  const currency    = @json(optional($setting)->currency ?? '$');
  const routePrefix = @json($routePrefix);

  // initial payload from server
  const initial = {
    labels : @json($chartLabels),
    revenue: @json($chartData),     // your totals as revenue
    orders : window.initialOrders,  // optional arrays, hide buttons if missing
    items  : window.initialItems,
    aov    : window.initialAov
  };

  // Chart gradient
  const ctx = document.getElementById('barChart').getContext('2d');
  const gradient = ctx.createLinearGradient(0, 0, 0, 260);
  gradient.addColorStop(0, 'rgba(16,185,129,.25)');
  gradient.addColorStop(1, 'rgba(16,185,129,0)');

  function makeDataset(label, data, color){
    return { label, data, tension:.45, pointRadius:0, borderWidth:2, backgroundColor:gradient, borderColor:color, fill:true };
  }

  let currentDataset = 'revenue';
  const datasetsMap = {
    revenue: () => makeDataset('Revenue', initial.revenue, 'rgba(13,148,136,1)'),
    orders : () => makeDataset('Orders',  initial.orders,  'rgba(59,130,246,1)'),
    items  : () => makeDataset('Items',   initial.items,   'rgba(99,102,241,1)'),
    aov    : () => makeDataset('AOV',     initial.aov,     'rgba(244,63,94,1)')
  };

  // Hide dataset buttons with no data
  document.querySelectorAll('#datasetToggle [data-dataset]').forEach(btn=>{
    if(!Array.isArray(initial[btn.dataset.dataset])) btn.style.display='none';
  });

  const chart = new Chart(ctx, {
    type:'line',
    data:{ labels:initial.labels, datasets:[datasetsMap[currentDataset]()] },
    options:{
      responsive:true,
      interaction:{ mode:'index', intersect:false },
      scales:{
        y:{ beginAtZero:true, grid:{ color:'rgba(0,0,0,.06)'} },
        x:{ grid:{ display:false } }
      },
      plugins:{
        legend:{ display:false },
        tooltip:{ callbacks:{
          label:(c)=>{
            const v=c.parsed.y??0;
            return ['revenue','aov'].includes(currentDataset) ? `${currency}${v.toFixed(2)}` : v.toLocaleString();
          }
        }}
      }
    }
  });

  // Range buttons -> fetch new data
  document.querySelectorAll('.range-btn').forEach(btn=>{
    btn.addEventListener('click', ()=>{
      const range = btn.dataset.range;
      fetch(`/${routePrefix}/dashboard/sales-data/${range}`)
        .then(r=>r.json())
        .then(payload=>{
          // payload: {labels, totals, orders?, items?}
          initial.labels  = payload.labels;
          initial.revenue = payload.totals;
          if(payload.orders) initial.orders = payload.orders;
          if(payload.items)  initial.items  = payload.items;
          initial.aov = Array.isArray(initial.orders)
            ? initial.revenue.map((t,i)=>{
                const o = initial.orders[i] || 0;
                return o ? t/o : 0;
              })
            : [];

          const sum = a => Array.isArray(a) ? a.reduce((x,y)=>x+(+y||0),0) : 0;
          const revenue = sum(initial.revenue);
          const orders  = Array.isArray(initial.orders)? sum(initial.orders): undefined;
          const items   = Array.isArray(initial.items) ? sum(initial.items) : undefined;
          const aov     = orders && orders>0 ? (revenue/orders) : 0;

          // update mini stats
          document.getElementById('statRevenue').textContent = currency + revenue.toFixed(2);
          if(orders!==undefined) document.getElementById('statOrders').textContent = orders.toLocaleString();
          if(items !==undefined) document.getElementById('statItems').textContent  = items.toLocaleString();
          document.getElementById('statAov').textContent = currency + aov.toFixed(2);

          // refresh chart
          chart.data.labels = initial.labels;
          chart.data.datasets = [datasetsMap[currentDataset]()];
          chart.update();
        })
        .catch(()=>{});
    });
  });

  // Dataset toggle
  document.querySelectorAll('#datasetToggle [data-dataset]').forEach(btn=>{
    btn.addEventListener('click', ()=>{
      document.querySelectorAll('#datasetToggle .btn').forEach(b=>b.classList.remove('active'));
      btn.classList.add('active');
      currentDataset = btn.dataset.dataset;
      chart.data.datasets = [datasetsMap[currentDataset]()];
      chart.update();
    });
  });

  // Download as PNG
  document.getElementById('downloadChart').addEventListener('click', ()=>{
    const a = document.createElement('a');
    a.href = chart.toBase64Image('image/png', 1);
    a.download = `sales_chart_${currentDataset}.png`;
    a.click();
  });
</script>
@endpush
