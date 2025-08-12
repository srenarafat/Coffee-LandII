@extends('layouts.app')
@section('content')
<div class="container-fluid mt-4">
    <div class="d-flex align-items-start">
        <h2 class="fw-bold"
            style="border: 1px solid #ffffff; background-color: #5c4033; color: #ffffff;
                   padding: 2px 10px; font-size: 1rem; border-radius: 6px;">
            {{ optional($setting)->shop_name ?? 'COFFEE LAND' }}
        </h2>
    </div>
</div>


    <!-- Overview Cards -->
<div class="row row-cols-1 row-cols-sm-2 row-cols-md-5 g-3 mb-4">
    <div class="col">
        <div style="background-color:rgb(94, 90, 90);" class="text-white rounded p-3 text-center shadow-sm h-100">
            <div><i class="bi bi-currency-dollar fs-2 mb-2"></i></div>
            <h6 class="fw-bold">{{ __('messages.total_sales') }}</h6>
            <h4>{{ optional($setting)->currency ?? '$' }}{{ number_format($salesTotal, 2) }}</h4>
        </div>
    </div>
    <div class="col">
        <div style="background-color:rgb(69, 161, 184);" class="text-white rounded p-3 text-center shadow-sm h-100">
            <div><i class="bi bi-box-fill fs-2 mb-2"></i></div>
            <h6 class="fw-bold">{{ __('messages.total_orders') }}</h6>
            <h4>{{ $invoiceCount }}</h4>
        </div>
    </div>
    <div class="col">
        <div style="background-color:rgb(74, 91, 141);" class="text-white rounded p-3 text-center shadow-sm h-100">
            <div><i class="bi bi-people-fill fs-2 mb-2"></i></div>
            <h6 class="fw-bold">{{ __('messages.total_users') }}</h6>
            <h4>{{ $totalUsers }}</h4>
        </div>
    </div>
    <div class="col">
        <div style="background-color:rgb(158, 74, 81);" class="text-white rounded p-3 text-center shadow-sm h-100">
            <div><i class="bi bi-card-list fs-2 mb-2"></i></div>
            <h6 class="fw-bold">{{ __('messages.total_products') }}</h6>
            <h4>{{ $productCount }}</h4>
        </div>
    </div>
    <div class="col">
        <div style="background-color:rgb(93, 146, 113);" class="text-white rounded p-3 text-center shadow-sm h-100">
            <div><i class="bi bi-tags-fill fs-2 mb-2"></i></div>
            <h6 class="fw-bold">{{ __('messages.total_categories') }}</h6>
            <h4>{{ $categoryCount }}</h4>
        </div>
    </div>
</div>
    <!-- Charts and Transactions -->
    <div class="row">
        <!-- Chart -->
        <div class="col-lg-6 mb-3">
    <div class="card shadow-sm h-100">
        <div class="card-header fw-bold text-white"
             style="background: linear-gradient(90deg, #34c897 0%, #ffe082 100%); border-bottom: 1px solid #ccc;">
            {{ __('messages.sales_earnings') }}
        </div>
        <div class="card-body">
            <canvas id="barChart" height="170"></canvas>
        </div>
    </div>
</div>


        <!-- Chart.js CDN -->
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <!-- Transactions -->
        <div class="col-lg-6 mb-3">
            <div class="card shadow-sm h-100">
               <div class="card-header fw-bold text-white"
     style="background: linear-gradient(90deg, #578ee1 0%, #04fbf7 100%); border-bottom: 1px solid #ccc;">
    {{ __('messages.recent_transactions') }}
</div>


                <div class="card-body p-0">
                    <table class="table table-striped mb-0 text-center align-middle">
                        <thead class="custom-blue-header text-black">
                            <tr>
                                <th>{{ __('messages.invoice') }}</th>
                                <th>{{ __('messages.date') }}</th>
                                <th>{{ __('messages.cashier') }}</th>
                                <th>{{ __('messages.amount') }}</th>
                                <th>{{ __('messages.discount') }}</th>
                                <th>{{ __('messages.total') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($recentSales as $sale)
                            <tr>
                                <td>{{ $sale->invoice_no }}</td>
                                <td>{{ $sale->created_at->format('d/m/Y, H:i') }}</td>
                                <td>{{ $sale->user->name ?? 'N/A' }}</td>
                                <td>{{ optional($setting)->currency ?? '$' }}{{ number_format($sale->subtotal, 2) }}</td>
                                <td>{{ optional($setting)->currency ?? '$' }}{{ number_format($sale->discount ?? 0, 2) }}</td>
                                <td><strong>{{ optional($setting)->currency ?? '$' }}{{ number_format($sale->total, 2) }}</strong></td>
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
@endsection


@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const ctx = document.getElementById('barChart').getContext('2d');
new Chart(ctx, {
    type: 'line',
    data: {
        labels: ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'],
        datasets: [{
                label: '{{ __('messages.sales') }}',
                data: [0, 200, 160, 180, 240, 300, 260],
                fill: true,
                backgroundColor: 'rgba(0, 255, 255, 0.4)', // aqua
                borderColor: 'rgba(0, 255, 255, 1)',
                tension: 0.5,
                pointRadius: 0
            },
            {
                label: '{{ __('messages.earnings') }}',
                data: [0, 260, 210, 230, 300, 400, 320],
                fill: '-1', // stack on top of previous
                backgroundColor: 'rgba(255, 221, 128, 0.6)', // light yellow
                borderColor: 'rgba(255, 221, 128, 1)',
                tension: 0.5,
                pointRadius: 0
            }
        ]
    },
    options: {
        responsive: true,
        interaction: {
            mode: 'index',
            intersect: false
        },
        scales: {
            y: {
                beginAtZero: true
            },
            x: {
                grid: {
                    display: false
                }
            }
        },
        plugins: {
            legend: {
                    display: true,
                    position: 'top',
                    labels: {
                        usePointStyle: true,
                        pointStyle: 'circle',
                    }
                },

            tooltip: {
                enabled: true,
                mode: 'index',
                intersect: false,
                backgroundColor: '#fff',
                titleColor: '#000',
                bodyColor: '#000',
                borderColor: '#ccc',
                borderWidth: 1
            }
        },
        hover: {
            mode: 'nearest',
            intersect: true
        }
    }




});
</script>
@endpush
@push('styles')
<style>
    .custom-blue-header th {
        background-color: #d8eaff !important;
        color: #000 !important;
        font-weight: bold;
    }
</style>
@endpush



