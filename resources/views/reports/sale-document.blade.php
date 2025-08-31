<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <title>Sale Document</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 14px; }
        .doc-header { display:flex; justify-content:space-between; align-items:flex-start; gap:1rem; margin-bottom:.5rem; }
        .table { width:100%; border-collapse:collapse; }
        .table th, .table td { border:1px solid #e5e7eb; padding:4px; vertical-align:middle; }
        .table th { background:#f8f9fa; }
        .chipline { display:flex; gap:.375rem; flex-wrap:wrap; }
        .badge { display:inline-block; padding:.15rem .5rem; font-size:.75rem; border:1px solid #dee2e6; border-radius:10px; background:#f8f9fa; }
        .row { display:flex; flex-wrap:wrap; margin-top:1rem; gap:1rem; }
        .col-7 { flex:0 0 58%; }
        .col-5 { flex:0 0 40%; }
        .card { border:1px solid #e5e7eb; border-radius:12px; }
        .card-body { padding:.75rem; }
        .fw-semibold { font-weight:600; }
        .fw-bold { font-weight:bold; }
        .small { font-size:0.875rem; }
        .text-muted { color:#6c757d; }
        .d-flex { display:flex; }
        .justify-content-between { justify-content:space-between; }
        .mb-1 { margin-bottom:.25rem; }
        .mb-2 { margin-bottom:.5rem; }
        .fs-6 { font-size:1rem; }
        .mt-1 { margin-top:.25rem; }
        .my-2 { margin-top:.5rem; margin-bottom:.5rem; }
    </style>
</head>
<body>
    <div class="doc-header">
        <div>
            <div class="fw-bold">COFFEE LAND</div>
            <div class="text-muted small">Sale detail (document view)</div>
        </div>
        <div style="text-align:right" class="small text-muted">
            <div><span class="fw-semibold">Date:</span> {{ $sale->created_at->format('Y-m-d') }}</div>
            <div><span class="fw-semibold">Cashier:</span> {{ $sale->user->name ?? 'N/A' }}</div>
            <div><span class="fw-semibold">Invoice #:</span> {{ $sale->id }}</div>
            <div>Table: {{ $sale->table_number ?? '—' }}</div>
        </div>
    </div>

    <table class="table" id="docTable">
        <thead>
            <tr>
                <th style="width:70px">SN</th>
                <th>Item</th>
                <th style="width:90px">Qty</th>
                <th style="width:120px">Price</th>
                <th style="width:140px">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($sale->items as $index => $item)
                @php
                    $chips = [];
                    $size = strtolower($item->size ?? '');
                    if(in_array($size, ['small','medium','large'])){
                        $chips[] = '<span class="badge">'.strtoupper(substr($size,0,1)).'</span>';
                    }
                    if($item->sugar_level !== null && (int)$item->sugar_level !== 100){
                        $chips[] = '<span class="badge">'.((int)$item->sugar_level)."% Sugar".'</span>';
                    }
                    if($item->ice_option && strtolower($item->ice_option) !== 'normal'){
                        $chips[] = '<span class="badge">'.ucfirst($item->ice_option).' Ice</span>';
                    }
                    foreach($item->options as $opt){
                        $chips[] = '<span class="badge">'.$opt.'</span>';
                    }
                    foreach($item->notes as $n){
                        $chips[] = '<span class="badge">'.$n.'</span>';
                    }
                    $chipsHtml = $chips ? '<div class="chipline mt-1">'.implode('', $chips).'</div>' : '';
                @endphp
                <tr>
                    <td style="text-align:center">{{ $index + 1 }}</td>
                    <td>{!! e($item->product->name) !!}{!! $chipsHtml !!}</td>
                    <td style="text-align:center">{{ $item->quantity }}</td>
                    <td style="text-align:center">{{ $currency }}{{ number_format($item->price,2) }}</td>
                    <td style="text-align:center">{{ $currency }}{{ number_format($item->total,2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="row">
        <div class="col-7">
            <div class="card">
                <div class="card-body">
                    <div class="fw-semibold mb-2">Notes / Options</div>
                    @if(count($orderNotes))
                        <ul class="small text-muted" style="margin:0; padding-left:1rem;">
                            @foreach($orderNotes as $note)
                                <li>{{ $note }}</li>
                            @endforeach
                        </ul>
                    @else
                        <div class="small text-muted">—</div>
                    @endif
                </div>
            </div>
        </div>
        <div class="col-5">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between small mb-1">
                        <span>Subtotal</span><span>{{ $currency }}{{ number_format($sale->subtotal ?? $sale->items->sum('total'),2) }}</span>
                    </div>
                    <div class="d-flex justify-content-between small mb-1">
                        <span>Discount</span><span>{{ $currency }}{{ number_format($sale->discount ?? 0,2) }}</span>
                    </div>
                    <hr class="my-2">
                    <div class="d-flex justify-content-between fs-6 fw-bold">
                        <span>Grand Total</span><span>{{ $currency }}{{ number_format($sale->total,2) }}</span>
                    </div>
                    <hr class="my-2">
                    <div class="small text-muted">
                        <div>Cash (USD): {{ $currency }}{{ number_format($sale->cash_usd,2) }}</div>
                        <div>Cash (Riel): {{ number_format($sale->cash_riel) }} ៛</div>
                        <div>Change (USD): {{ $currency }}{{ number_format($sale->change_usd,2) }}</div>
                        <div>Change (Riel): {{ number_format($sale->change_riel) }} ៛</div>
                        <div>Payment Method: {{ ucfirst($sale->payment_method) }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>