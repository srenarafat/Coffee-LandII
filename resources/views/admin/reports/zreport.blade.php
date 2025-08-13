@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4 no-print">
    <h4 class="fw-bold mb-0">Z-Report</h4>
    <button onclick="window.print()" class="btn btn-outline-primary rounded-pill">
        🖨️ Print
    </button>
</div>

<div class="bg-white rounded shadow-sm p-4 mb-4">
    <h5 class="text-center fw-bold mb-3">Today's Summary</h5>
    <table class="table table-bordered text-center mb-0">
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
                <td>{{ number_format($summary->gross, 2) }}</td>
                <td>{{ number_format($summary->discount, 2) }}</td>
                <td>{{ number_format($summary->net, 2) }}</td>
                <td>{{ $summary->orders }}</td>
            </tr>
        </tbody>
    </table>
</div>

<div class="row g-4">
    <div class="col-lg-6">
        <div class="bg-white rounded shadow-sm p-4 h-100">
            <h6 class="fw-bold text-center mb-3">By Payment Method</h6>
            <table class="table table-bordered text-center mb-0">
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
                    @foreach($totalsByPaymentMethod as $row)
                        <tr>
                            <td>{{ ucfirst($row->payment_method) }}</td>
                            <td>{{ number_format($row->gross, 2) }}</td>
                            <td>{{ number_format($row->discount, 2) }}</td>
                            <td>{{ number_format($row->net, 2) }}</td>
                            <td>{{ $row->orders }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="bg-white rounded shadow-sm p-4 h-100">
            <h6 class="fw-bold text-center mb-3">By Cashier</h6>
            <table class="table table-bordered text-center mb-0">
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
                    @foreach($totalsByCashier as $row)
                        <tr>
                            <td>{{ $row->user->name ?? 'N/A' }}</td>
                            <td>{{ number_format($row->gross, 2) }}</td>
                            <td>{{ number_format($row->discount, 2) }}</td>
                            <td>{{ number_format($row->net, 2) }}</td>
                            <td>{{ $row->orders }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

<style>
@media print {
    .no-print {
        display: none;
    }
}
</style>
@endsection
