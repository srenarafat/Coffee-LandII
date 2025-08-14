@extends('layouts.app')

@section('content')
<div class="container my-4">
  <div class="card shadow-sm border-0 rounded-4">
    <div class="card-header d-flex flex-wrap gap-3 justify-content-between align-items-center">
      <div class="d-flex align-items-center gap-2">
        <h5 class="mb-0 fw-bold">Low Stock Products</h5>
        <span class="badge bg-warning-subtle text-warning-emphasis rounded-pill">Threshold: {{ $threshold }}</span>
      </div>

      <div class="d-flex gap-2">
        <input id="lowStockSearch" type="search" class="form-control form-control-sm"
               placeholder="Search product or category…" style="min-width:220px">
        <a href="{{ route('admin.stock.low') }}" class="btn btn-sm btn-outline-secondary">Refresh</a>
      </div>
    </div>

    <div class="card-body">
      <div class="table-responsive">
        <table id="lowStockTable" class="table table-bordered table-striped table-hover align-middle mb-0">
          <thead class="sticky-top" style="top:0;z-index:5;background-color:#dbeafe;">
            <tr>
              <th class="text-start" style="width:42%">@lang('messages.product')</th>
              <th class="text-start" style="width:24%">@lang('messages.category')</th>
              <th class="text-center" style="width:14%">Stock</th>
              <th class="text-center" style="width:20%">@lang('messages.actions')</th>
            </tr>
          </thead>
          <tbody>
            @forelse($products as $product)
              @php
                $stock = (int) ($product->stock ?? 0);
                $pct   = min(100, max(0, round(($stock / max(1,$threshold)) * 100)));

                // --- Build robust image URL for public/storage/product_images ---
                $raw = trim($product->image ?? '');
                if ($raw) {
                    if (\Illuminate\Support\Str::startsWith($raw, ['http://','https://'])) {
                        $imgUrl = $raw; // already absolute URL (CDN, etc.)
                    } else {
                        // Normalize stored value (strip leading slashes & known prefixes)
                        $p = ltrim($raw, '/');
                        $p = str_replace(['public/', 'storage/'], '', $p);
                        if (!\Illuminate\Support\Str::startsWith($p, 'product_images/')) {
                            $p = 'product_images/'.$p;
                        }
                        // Final public URL: /storage/product_images/<file>
                        $imgUrl = asset('storage/'.$p);
                    }
                } else {
                    $imgUrl = asset('images/no-image.png'); // fallback thumbnail
                }
              @endphp

              <tr class="{{ $stock <= 0 ? 'table-danger' : ($stock <= $threshold ? 'table-warning' : '') }}">
                <td class="text-start">
                  <div class="d-flex align-items-start gap-2">
                    <img src="{{ $imgUrl }}" alt="{{ $product->name }}"
                         class="rounded border" style="width:36px;height:36px;object-fit:cover">
                    <div>
                      <div class="fw-semibold">{{ $product->name }}</div>
                      <div class="progress progress-thin mt-1" title="Remaining vs threshold">
                        <div class="progress-bar {{ $pct<=25?'bg-danger':($pct<=60?'bg-warning':'bg-success') }}"
                             role="progressbar" style="width: {{ $pct }}%"
                             aria-valuenow="{{ $pct }}" aria-valuemin="0" aria-valuemax="100"></div>
                      </div>
                      <small class="text-muted">at {{ $stock }} / {{ $threshold }}</small>
                    </div>
                  </div>
                </td>

                <td class="text-start">{{ $product->category->name ?? '-' }}</td>

                <td class="text-center">
                  <span class="badge rounded-pill {{ $stock<=0?'bg-danger':($stock<=$threshold?'bg-warning text-dark':'bg-success') }}">
                    {{ $stock }}
                  </span>
                </td>

                <td class="text-center">
                  <div class="d-flex justify-content-center gap-2">
                    <button class="btn btn-sm btn-primary"
                            data-stockin
                            data-product="{{ $product->id }}"
                            data-name="{{ $product->name }}">
                      @lang('messages.stock_in')
                    </button>

                    <a href="{{ route('admin.products.edit', $product->id) }}"
                       class="btn btn-sm btn-outline-secondary">
                      Edit
                    </a>
                  </div>
                </td>
              </tr>
            @empty
              <tr>
                <td colspan="4" class="text-center text-muted py-4">
                  All products have sufficient stock.
                </td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>

      {{-- Optional: pagination slot if you paginate $products --}}
      @if(method_exists($products, 'links'))
        <div class="mt-3">
          {{ $products->links() }}
        </div>
      @endif
    </div>
  </div>
</div>

{{-- Quick Stock‑In Modal (no navigation) --}}
<div class="modal fade" id="stockInModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-sm modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header py-2">
        <h6 class="modal-title">Stock In</h6>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form id="stockInForm" method="GET">
        {{-- Using GET keeps your existing create route that expects query params --}}
        <div class="modal-body">
          <div class="mb-2 small text-muted" id="stockInProductName"></div>
          <label class="form-label mb-1">Quantity</label>
          <input type="number" name="qty" class="form-control" value="1" min="1" required>
          <input type="hidden" name="type" value="in">
          <input type="hidden" name="product_id" id="stockInProductId">
        </div>
        <div class="modal-footer py-2">
          <button type="submit" class="btn btn-primary btn-sm">Continue</button>
        </div>
      </form>
    </div>
  </div>
</div>

@push('styles')
<style>
  .progress-thin { height:6px; background:#f1f5f9; }
  thead.sticky-top th { box-shadow: 0 1px 0 rgba(0,0,0,.05); }
</style>
@endpush

@push('scripts')
<script>
  // simple client-side search
  const search = document.getElementById('lowStockSearch');
  const rows   = () => Array.from(document.querySelectorAll('#lowStockTable tbody tr'));
  search?.addEventListener('input', () => {
    const q = search.value.toLowerCase();
    rows().forEach(tr => {
      const txt = tr.innerText.toLowerCase();
      tr.style.display = txt.includes(q) ? '' : 'none';
    });
  });

  // stock-in modal helpers
  const stockInModal = new bootstrap.Modal(document.getElementById('stockInModal'));
  document.querySelectorAll('[data-stockin]').forEach(btn => {
    btn.addEventListener('click', () => {
      const id   = btn.dataset.product;
      const name = btn.dataset.name;
      document.getElementById('stockInProductId').value = id;
      document.getElementById('stockInProductName').textContent = name;
      const action = @json(route('admin.stock-logs.create'));
      document.getElementById('stockInForm').action = action;
      stockInModal.show();
    });
  });
</script>
@endpush
@endsection
