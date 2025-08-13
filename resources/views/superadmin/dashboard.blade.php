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
    
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 mt-4">
        <a href="{{ route('superadmin.reports.sales.today') }}"
           class="flex flex-col items-center justify-center p-6 bg-white text-[#5c4033] rounded-lg shadow hover:bg-[#5c4033] hover:text-white transition">
            <i class="bi bi-graph-up text-4xl"></i>
            <span class="mt-2 font-semibold text-center">Today's Sales</span>
        </a>
        <a href="{{ route('superadmin.reports.zreport') }}"
           class="flex flex-col items-center justify-center p-6 bg-white text-[#5c4033] rounded-lg shadow hover:bg-[#5c4033] hover:text-white transition">
            <i class="bi bi-file-earmark-text text-4xl"></i>
            <span class="mt-2 font-semibold text-center">Z Report</span>
        </a>
        <a href="{{ route('superadmin.stock.low') }}"
           class="flex flex-col items-center justify-center p-6 bg-white text-[#5c4033] rounded-lg shadow hover:bg-[#5c4033] hover:text-white transition">
            <i class="bi bi-exclamation-triangle text-4xl"></i>
            <span class="mt-2 font-semibold text-center">Low Stock</span>
        </a>
        <a href="{{ route('superadmin.reports.top-products.week') }}"
           class="flex flex-col items-center justify-center p-6 bg-white text-[#5c4033] rounded-lg shadow hover:bg-[#5c4033] hover:text-white transition">
            <i class="bi bi-star text-4xl"></i>
            <span class="mt-2 font-semibold text-center">Top Products Week</span>
        </a>
        <a href="{{ route('superadmin.reports.slow-products') }}"
           class="flex flex-col items-center justify-center p-6 bg-white text-[#5c4033] rounded-lg shadow hover:bg-[#5c4033] hover:text-white transition">
            <i class="bi bi-hourglass-split text-4xl"></i>
            <span class="mt-2 font-semibold text-center">Slow Products</span>
        </a>
        <a href="{{ route('superadmin.reports.sales.export') }}"
           class="flex flex-col items-center justify-center p-6 bg-white text-[#5c4033] rounded-lg shadow hover:bg-[#5c4033] hover:text-white transition">
            <i class="bi bi-download text-4xl"></i>
            <span class="mt-2 font-semibold text-center">Export Sales</span>
        </a>
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
        labels: @json($chartLabels),
        datasets: [{
                label: '{{ __('messages.sales') }}',
            data: @json($chartData),
            fill: true,
            backgroundColor: 'rgba(0, 255, 255, 0.4)', // aqua
            borderColor: 'rgba(0, 255, 255, 1)',
            tension: 0.5,
            pointRadius: 0
        }]
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



