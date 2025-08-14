@extends('layouts.app')

@section('content')
@php
    $currency = optional($setting ?? null)->currency ?? '$';
    // Find the max qty to scale progress bars (avoid divide by zero)
    $maxQty = max(1, ($topProducts->max('qty') ?? 1));
@endphp

<div class="container my-4">
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
        <h5 class="fw-bold text-uppercase mb-0">Top Products This Week</h5>
        <div class="d-flex gap-2">
            <input id="tpSearch" class="form-control form-control-sm" type="search"
                   placeholder="Search product or category…" style="min-width:220px">
            <button class="btn btn-sm btn-outline-secondary" onclick="window.print()">🖨️ Print</button>
        </div>
    </div>

    <div class="table-responsive">
        <table id="tpTable" class="table table-bordered align-middle">
            <thead class="table-light">
                <tr>
                    <th style="width:56px">#</th>
                    <th>Product</th>
                    <th style="width:120px" class="text-center">Qty</th>
                    <th style="width:140px" class="text-center">Revenue</th>
                </tr>
            </thead>
            <tbody>
            @foreach($topProducts as $index => $item)
                @php
                    $rank = $index + 1;
                    $qty  = (int)($item->qty ?? 0);
                    $pct  = min(100, round(($qty / $maxQty) * 100));

                    // --- Build robust image URL (supports: absolute URL, 'product_images/*', or filename) ---
                    $raw = trim($item->product->image ?? '');
                    if ($raw) {
                        if (\Illuminate\Support\Str::startsWith($raw, ['http://','https://'])) {
                            $imgUrl = $raw;
                        } else {
                            $p = ltrim($raw, '/');
                            $p = str_replace(['public/','storage/'], '', $p);
                            if (!\Illuminate\Support\Str::startsWith($p, 'product_images/')) {
                                $p = 'product_images/'.$p;
                            }
                            $imgUrl = asset('storage/'.$p);    // => public/storage/product_images/...
                        }
                    } else {
                        $imgUrl = asset('images/no-image.png');
                    }

                    // medal color for top 3
                    $medal = match(true) {
                        $rank === 1 => '🥇',
                        $rank === 2 => '🥈',
                        $rank === 3 => '🥉',
                        default     => $rank
                    };
                @endphp

                <tr>
                    <td class="fw-semibold text-center">{!! $medal !!}</td>
                    <td>
                        <div class="d-flex align-items-center gap-2">
                            <img src="{{ $imgUrl }}" alt="{{ $item->product->name }}"
                                 class="rounded border" style="width:44px;height:44px;object-fit:cover">
                            <div>
                                <div class="fw-semibold">{{ $item->product->name }}</div>
                                <small class="text-muted">{{ $item->product->category->name ?? 'N/A' }}</small>
                                <div class="progress mt-1" style="height:6px; max-width:320px">
                                    <div class="progress-bar {{ $pct>=66?'bg-success':($pct>=33?'bg-warning':'bg-danger') }}"
                                         role="progressbar" style="width: {{ $pct }}%" aria-valuenow="{{ $pct }}"
                                         aria-valuemin="0" aria-valuemax="100"></div>
                                </div>
                            </div>
                        </div>
                    </td>
                    <td class="text-center fw-semibold">{{ $qty }}</td>
                    <td class="text-center fw-semibold">{{ $currency }}{{ number_format($item->revenue ?? 0, 2) }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
</div>

@push('styles')
<style>
  @media print { #tpSearch { display:none!important; } }
</style>
@endpush

@push('scripts')
<script>
  // Simple client-side filter
  (function(){
    const q = document.getElementById('tpSearch');
    if(!q) return;
    const rows = Array.from(document.querySelectorAll('#tpTable tbody tr'));
    q.addEventListener('input', () => {
      const s = q.value.toLowerCase();
      rows.forEach(tr => {
        const text = tr.innerText.toLowerCase();
        tr.style.display = text.includes(s) ? '' : 'none';
      });
    });
  })();
</script>
@endpush
@endsection
