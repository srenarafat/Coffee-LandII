@extends('layouts.app')

@section('content')
<!-- Title + Controls Row -->
<div class="row g-4 mb-2">
    <!-- Title -->
    <div class="col-lg-5 text-center">
        <h5 class="fw-bold text-center text-uppercase text-brown mb-0">
            📊 {{ __('messages.top_quantity_sale_products') }}
        </h5>
    </div>

    <!-- Controls (right on lg+, centered on small) -->
    <div class="col-lg-7">
        <div class="d-flex flex-wrap align-items-center gap-4 justify-content-lg-end justify-content-center">

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
                        style="width: 160px;"
                        onchange="this.form.submit()">
                    <option value="">{{ __('messages.all_categories') }}</option>
                    {!! render_category_options($categories, request('category_id')) !!}
                </select>
            </form>
        </div>
    </div>
</div>

<!-- Chart & Table Section -->
<div class="row g-4">
    <!-- Pie Chart -->
    <div class="col-lg-6">
        <div class="bg-white rounded shadow-sm p-4 h-100 d-flex align-items-center justify-content-center">
            <canvas id="topSalesChart" style="max-height: 300px;"></canvas>
        </div>
    </div>

    <!-- Table -->
    <div class="col-lg-6">
        <div class="bg-white rounded shadow-sm p-4">
            <table class="table table-bordered text-center align-middle mb-0">
                <thead class="table-primary">
                    <tr>
                        <th>#</th>
                        <th>{{ __('messages.product') }}</th>
                        <th>{{ __('messages.category') }}</th>
                        <th>{{ __('messages.total_quantity') }}</th>
                        <th>{{ __('messages.month') }}</th>
                        <th>{{ __('messages.year') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($topProducts as $index => $item)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $item->product->name ?? 'N/A' }}</td>
                            <td>{{ $item->category_name }}</td>
                            <td>{{ $item->total_quantity }}</td>
                            <td>{{ \Carbon\Carbon::create()->month($item->month)->format('F') }}</td>
                            <td>{{ $item->year }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const ctx = document.getElementById('topSalesChart').getContext('2d');
new Chart(ctx, {
  type: 'pie',
  data: {
    labels: {!! json_encode($topProducts->pluck('product.name')) !!},
    datasets: [{
      label: '{{ __('messages.quantity_sold') }}',
      data: {!! json_encode($topProducts->pluck('total_quantity')) !!},
      backgroundColor: ['#007bff','#28a745','#ffc107','#dc3545','#6f42c1','#20c997','#fd7e14'],
      borderWidth: 1
    }]
  },
  options: { responsive: true, plugins: { legend: { position: 'bottom' } } }
});
</script>
@endpush
