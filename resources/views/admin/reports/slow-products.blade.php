@extends('layouts.app')

@section('content')
@php
  // Role-aware promotion route builder
  $promoRoute = fn($id) => auth()->user()?->role === 'superadmin'
      ? route('superadmin.products.promote', $id)
      : route('admin.products.promote', $id);

  // Helper to format days & color
  $fmtDays = function($d) {
      if ($d === null) return ['label' => 'No Sales', 'class' => 'bg-secondary'];
      $days = floor((float)$d);
      $class = $days >= 60 ? 'bg-danger'
             : ($days >= 30 ? 'bg-warning text-dark'
             : 'bg-success');
      return ['label' => "{$days} days", 'class' => $class, 'days' => $days];
  };
@endphp

<div class="container my-4">
  <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <h5 class="fw-bold text-uppercase mb-0">Slow Moving Products</h5>

    <div class="d-flex flex-wrap gap-2">
      <input id="slowSearch" class="form-control form-control-sm" type="search"
             placeholder="Search product or category…" style="min-width:220px">
      <input id="minDays" class="form-control form-control-sm" type="number"
             min="0" placeholder="Min days…" style="width:120px">
      <button class="btn btn-sm btn-outline-secondary" onclick="window.print()">🖨️ Print</button>
    </div>
  </div>

  <div class="table-responsive">
    <table id="slowTable" class="table table-bordered align-middle">
      <thead class="table-primary">
        <tr class="text-center">
          <th style="width:64px">#</th>
          <th class="text-center" style="width:44%">Product</th>
          <th class="text-center" style="width:24%">Category</th>
          <th style="width:180px">Days Since Last Sale</th>
          <th style="width:180px">Action</th>
        </tr>
      </thead>
      <tbody>
        @foreach($products as $index => $product)
          @php
            // --- robust image URL (supports absolute, 'product_images/*', or filename) ---
            $raw = trim($product->image ?? '');
            if ($raw) {
              if (\Illuminate\Support\Str::startsWith($raw, ['http://','https://'])) {
                $imgUrl = $raw;
              } else {
                $p = ltrim($raw, '/');
                $p = str_replace(['public/','storage/'], '', $p);
                if (!\Illuminate\Support\Str::startsWith($p, 'product_images/')) {
                  $p = 'product_images/'.$p;
                }
                $imgUrl = asset('storage/'.$p); // public/storage/product_images/...
              }
            } else {
              $imgUrl = asset('images/no-image.png');
            }

            $age = $fmtDays($product->days_since_last_sale ?? null);
          @endphp

          <tr data-name="{{ strtolower($product->name) }}"
              data-cat="{{ strtolower($product->category->name ?? 'n/a') }}"
              data-days="{{ $age['days'] ?? '' }}">
            <td class="text-center fw-semibold">{{ $index + 1 }}</td>

            <td class="text-start">
              <div class="d-flex align-items-center gap-2">
                <img src="{{ $imgUrl }}" alt="{{ $product->name }}"
                     class="rounded border" style="width:40px;height:40px;object-fit:cover">
                <div>
                  <div class="fw-semibold">{{ $product->name }}</div>
                  <small class="text-muted">
                    @if(!empty($product->last_sale_at))
                      Last sale: {{ \Carbon\Carbon::parse($product->last_sale_at)->format('d M Y') }}
                    @else
                      Never sold
                    @endif
                  </small>
                </div>
              </div>
            </td>

            <td class="text-center">{{ $product->category->name ?? 'N/A' }}</td>

            <td class="text-center">
              <span class="badge rounded-pill {{ $age['class'] }}">{{ $age['label'] }}</span>
            </td>

            <td class="text-center">
              <button class="btn btn-sm btn-outline-primary"
                      data-promote
                      data-action="{{ $promoRoute($product->id) }}"
                      data-name="{{ $product->name }}">
                Mark for Promotion
              </button>
            </td>
          </tr>
        @endforeach
      </tbody>
    </table>
  </div>

  {{-- Pagination (if you paginate $products) --}}
  @if(method_exists($products, 'links'))
    <div class="mt-3">{{ $products->links() }}</div>
  @endif
</div>

{{-- Confirm Promotion Modal --}}
<div class="modal fade" id="promoModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-sm modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header py-2">
        <h6 class="modal-title">Mark for Promotion</h6>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form id="promoForm" method="POST">
        @csrf
        <div class="modal-body">
          <div class="small text-muted mb-2" id="promoName"></div>
          <div class="mb-2">
            <label class="form-label mb-1">Note (optional)</label>
            <input name="note" class="form-control form-control-sm" placeholder="Why promote?">
          </div>
          <div class="form-check">
            <input class="form-check-input" type="checkbox" name="discount_flag" id="discountFlag">
            <label class="form-check-label small" for="discountFlag">Suggest a discount</label>
          </div>
        </div>
        <div class="modal-footer py-2">
          <button type="submit" class="btn btn-primary btn-sm">Confirm</button>
        </div>
      </form>
    </div>
  </div>
</div>

@push('styles')
<style>
  @media print {
    #slowSearch, #minDays, button[onclick="window.print()"] { display: none !important; }
  }
</style>
@endpush

@push('scripts')
<script>
  // Client filter: search + min days
  (function(){
    const q  = document.getElementById('slowSearch');
    const md = document.getElementById('minDays');
    const rows = Array.from(document.querySelectorAll('#slowTable tbody tr'));
    const apply = () => {
      const s = (q?.value || '').toLowerCase();
      const d = md?.value ? Number(md.value) : null;
      rows.forEach(tr => {
        const matchText = tr.dataset.name.includes(s) || tr.dataset.cat.includes(s);
        const days = tr.dataset.days ? Number(tr.dataset.days) : null;
        const matchDays = d === null || (days !== null && days >= d);
        tr.style.display = (matchText && matchDays) ? '' : 'none';
      });
    };
    q?.addEventListener('input', apply);
    md?.addEventListener('input', apply);
  })();

  // Promotion modal
  (function(){
    const modal = new bootstrap.Modal(document.getElementById('promoModal'));
    const form  = document.getElementById('promoForm');
    document.querySelectorAll('[data-promote]').forEach(btn => {
      btn.addEventListener('click', () => {
        form.action = btn.dataset.action;
        document.getElementById('promoName').textContent = btn.dataset.name;
        modal.show();
      });
    });
  })();
</script>
@endpush
@endsection
