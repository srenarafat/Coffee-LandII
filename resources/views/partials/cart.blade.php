<div class="card shadow-sm border-0 rounded-4 d-flex flex-column cart-wrapper">
    <!-- Header -->
    <div class="card-header bg-white border-0 fw-bold fs-5 d-flex justify-content-between align-items-center">
        <span>🛒 {{ __('messages.cart') }}</span>
        <div class="d-flex align-items-center gap-2">
            <span id="currentTable" class="fw-normal">
                @if(session('table_number'))
                    {{ __('messages.table') }}: {{ session('table_number') }}
                @endif
            </span>
            <button type="button" id="clearTable"
                    class="btn btn-sm btn-outline-danger rounded-circle d-flex align-items-center justify-content-center {{ session('table_number') ? '' : 'd-none' }}">
                <i class="bi bi-x-lg"></i>
            </button>
            <button type="button" id="openTableModal"
                    class="btn btn-sm btn-outline-secondary rounded-circle d-flex align-items-center justify-content-center">
                <i class="bi bi-person-lines-fill"></i>
            </button>
        </div>
    </div>

    @if (session('cart') && count(session('cart')) > 0)
    <!-- Product List -->
    <div class="px-3 pt-3 pb-2 overflow-auto cart-panel" style="max-height: 400px;">
        <div class="table-responsive mb-2">
            <div style="overflow-x: auto;">
                <table class="table align-middle text-nowrap">
                    <thead class="cart-header text-white text-center align-middle">
                        <tr>
                            <th>{{ __('messages.product') }}</th>
                            <th>{{ __('messages.qty') }}</th>
                            <th>{{ __('messages.price') }}</th>
                            <th>{{ __('messages.action') }}</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody id="cartBody">
                        @php $total = 0; $itemCount = 0; @endphp
                        @foreach(collect(session('cart', []))->sortBy('name') as $key => $item)
                            @php
                                $lineTotal  = $item['price'] * $item['quantity'];
                                $total     += $lineTotal;
                                $itemCount += $item['quantity'];

                                // Build options array, treating missing size as medium
                                $options = [];
                                $size = $item['size'] ?? null;
                                $options[] = strtolower($size ?: 'medium');
                                if (array_key_exists('sugar_level', $item) && $item['sugar_level'] !== null && (int)$item['sugar_level'] !== 100) {
                                    $options[] = ((int)$item['sugar_level']).'%';
                                }
                                if (!empty($item['ice_option']) && strtolower($item['ice_option']) !== 'normal') {
                                    $options[] = strtolower($item['ice_option']);
                                }
                            @endphp
                            <tr data-row-id="{{ $key }}">
                                <td style="min-width: 140px;">
    <div class="fw-semibold">{{ $item['name'] }}</div>

    @php
        $optionsList = [];
        $size = $item['size'] ?? null;
        $optionsList[] = ucfirst($size ?: 'medium') . ' Size';
        if (array_key_exists('sugar_level', $item) && $item['sugar_level'] !== null && (int)$item['sugar_level'] !== 100) {
            $optionsList[] = 'Sugar: ' . (int)$item['sugar_level'] . '%';
        }
        if (!empty($item['ice_option']) && strtolower($item['ice_option']) !== 'normal') {
            $optionsList[] = ucfirst($item['ice_option']) . ' Ice';
        }
        if (!empty($item['note'])) {
            $optionsList[] = $item['note']; // ✅ just the note text
        }
    @endphp

    @if($optionsList)
        <ul class="cart-options-list mt-1">
            @foreach($optionsList as $opt)
                <li>{{ $opt }}</li>
            @endforeach
        </ul>
    @endif
</td>



                                <td class="text-center">
                                    <form method="POST" class="d-inline update-quantity-form">
                                        @csrf
                                        <input type="hidden" name="cart_key" value="{{ $key }}">
                                        <input type="hidden" name="action" value="">
                                        <input type="hidden" class="update-url" value="{{ route($routePrefix . '.pos.update') }}">
                                        <div class="d-flex align-items-center justify-content-center gap-1">
                                            <button type="button" class="btn btn-sm btn-outline-secondary rounded-circle decrease-btn" style="width: 28px; height: 28px;">−</button>
                                            <span class="px-2 qty" data-qty="{{ $item['quantity'] }}" data-confirmed="{{ $item['quantity'] }}">{{ $item['quantity'] }}</span>
                                            <button type="button" class="btn btn-sm btn-outline-secondary rounded-circle increase-btn" style="width: 28px; height: 28px;">+</button>
                                        </div>
                                    </form>
                                </td>

                                <!-- Price shows LINE TOTAL; keep unit price in data-unit for instant recalc -->
                                <td class="text-nowrap">
                                    {{ optional($setting)->currency ?? '$' }}
                                    <span class="row-price" data-unit="{{ number_format($item['price'], 2, '.', '') }}">
                                        {{ number_format($lineTotal, 2) }}
                                    </span>
                                </td>

                                <td class="d-flex gap-1">
                                    @php
    // Build safe payload for the editor
    $editPayload = $item; // $item is an array from the session
    $editPayload['size']         = $item['size'] ?? 'medium';
    $editPayload['image_url']     = !empty($item['image']) ? asset('storage/'.$item['image']) : '';
    $editPayload['price_display'] = (optional($setting)->currency ?? '$') . number_format($item['price'] ?? 0, 2);
    $productModel = \App\Models\Product::find($item['product_id']);
    $currency = optional($setting)->currency ?? '$';
    if ($productModel) {
        $editPayload['price_small_display']  = $productModel->price_small !== null ? $currency . number_format($productModel->price_small, 2) : '';
        $editPayload['price_medium_display'] = $productModel->price_medium !== null ? $currency . number_format($productModel->price_medium, 2) : $editPayload['price_display'];
        $editPayload['price_large_display']  = $productModel->price_large !== null ? $currency . number_format($productModel->price_large, 2) : '';
    }
@endphp

<button type="button"
        class="btn btn-sm btn-outline-secondary edit-item-btn"
        data-cart-key="{{ $key }}"
        data-notes='@json($item["note"] ? [$item["note"]] : [])'
        data-item='@json($editPayload)'>
    {{ __('messages.edit') }}
</button>

                                    <form method="POST" action="{{ route($routePrefix . '.pos.remove', $key) }}" class="d-inline remove-item-form">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-outline-danger">&times;</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Summary + Checkout -->
    <div class="px-3 pb-3" id="checkoutSection">
        <div class="mb-2 text-end">
            <div class="fw-semibold">
                {{ __('messages.total_items') }}:
                <span id="totalItems">{{ $itemCount }}</span>
            </div>
            <div class="fw-bold fs-5">
                {{ __('messages.grand_total') }}: {{ optional($setting)->currency ?? '$' }}
                <span id="grandTotal">{{ number_format($total, 2) }}</span>
            </div>
        </div>

        <form method="POST" action="{{ route($routePrefix . '.pos.payment') }}">
            @csrf
            <input type="hidden" name="discount" id="form-discount">
            <input type="hidden" name="cash_received" id="form-cash">
            <button type="submit" class="btn btn-success w-100">{{ __('messages.checkout') }}</button>
        </form>
    </div>
    @else
    <div class="card-body">
        <p class="text-muted text-center">{{ __('messages.cart_empty') }}</p>
    </div>
    @endif
</div>

@include('partials.comment-modal', ['routePrefix' => $routePrefix, 'comments' => $comments ?? collect()])
@include('partials.table-modal')

<style>
    .cart-options-list {
    margin: 0;
    padding-left: 1rem;
    font-size: 0.82rem;
    color: #555;
    list-style-type: disc;
}
    .cart-options-list li {
    margin: 2px 0;
}
    .cart-header th{ background-color:#d8eaff!important; color:#000!important; font-weight:bold; }
    .cart-options{ font-size:.8rem; line-height:1.2; color:#6c757d; }
    .cart-options div + div{ margin-top:2px; }
    @media (max-width:768px){
        .cart-header th,.table td{ font-size:13px; padding:.4rem; }
        .cart-panel{ max-height:48vh; overflow-y:auto; }
    }
</style>

@push('scripts')

<script>
(function(){
  const currency = @json(optional($setting)->currency ?? '$');
  const cartContainer = document.getElementById('cart-container');
    const tableUrl = @json(route($routePrefix . '.pos.table'));
    const tableLabel = @json(__('messages.table'));
    let selectedTable = @json(session('table_number'));


  // ---- helpers -------------------------------------------------------------
  const $ = (sel, ctx=document) => ctx.querySelector(sel);
  const $$ = (sel, ctx=document) => Array.from(ctx.querySelectorAll(sel));
  const fmt = n => Number(n||0).toFixed(2);

  function getCsrf(){
    return document.querySelector('meta[name="csrf-token"]')?.content
      || document.querySelector('#commentForm input[name=_token]')?.value
      || '';
  }

  function highlightTableButtons(number){
    $$('.table-btn').forEach(btn=>{
      if(btn.dataset.number == number){
        btn.classList.remove('btn-outline-primary');
        btn.classList.add('btn-primary','active');
      }else{
        btn.classList.remove('btn-primary','active');
        btn.classList.add('btn-outline-primary');
      }
    });
  }


  function rowOf(el){ return el.closest('tr[data-row-id]'); }
  function unitOf(row){ return Number($('.row-price', row)?.dataset.unit || 0); }
  function qtyEl(row){ return $('.qty', row); }
  function setQty(row, q, confirmed=false){
    const qEl = qtyEl(row);
    qEl.dataset.qty = q;
    qEl.textContent = q;
    if(confirmed) qEl.dataset.confirmed = q;
  }
  function lineTotal(row){
    const line = unitOf(row) * Number(qtyEl(row).dataset.qty || 0);
    $('.row-price', row).textContent = fmt(line);
    return line;
  }
  function recalcTotals(){
    let total = 0, items = 0;
    $$('#cartBody tr[data-row-id]').forEach(r=>{
      const q = Number(qtyEl(r).dataset.qty || 0);
      const line = Number($('.row-price', r).textContent.replace(/,/g,'') || 0);
      items += q; total += line;
    });
    $('#grandTotal').textContent = fmt(total);
    $('#totalItems').textContent = items;
  }

  function checkEmptyCart(){
    if ($$('#cartBody tr[data-row-id]').length === 0){
      const wrapper = $('.cart-wrapper');
      if(!wrapper) return;
      const panel = $('.cart-panel', wrapper);
      if(panel) panel.remove();
      const checkout = $('#checkoutSection', wrapper);
      if(checkout) checkout.remove();
      if(!$('.cart-empty', wrapper)){
        const body = document.createElement('div');
        body.className = 'card-body cart-empty';
        body.innerHTML = `<p class="text-muted text-center">{{ __('messages.cart_empty') }}</p>`;
        wrapper.appendChild(body);
      }
    }
  }

  // ---- optimistic update core ---------------------------------------------
  // Per-row state: debounce timer + abort controller
  const pending = new Map(); // cartKey -> {timer, controller}

  function getIds(row){
    const cartKey = row.getAttribute('data-row-id')
      || $('input[name=cart_key]', row)?.value;
    const url = $('.update-url', row)?.value;
    const token = $('input[name=_token]', row)?.value;
    return { cartKey, url, token };
  }

  // Try to send "set quantity" (preferred). If your backend only supports
  // increase/decrease, it falls back to sending deltas repeatedly.
  async function syncQuantity(row, targetQty){
    const { cartKey, url, token } = getIds(row);
    if(!cartKey || !url || !token) return;

    const confirmedQty = Number(qtyEl(row).dataset.confirmed || 0);

    // cancel any in-flight request for this row
    if (pending.get(cartKey)?.controller) {
      pending.get(cartKey).controller.abort();
    }
    const controller = new AbortController();
    pending.set(cartKey, { controller, timer:null });

    // Preferred payload: set exact quantity
    const fd = new FormData();
    fd.append('_token', token);
    fd.append('cart_key', cartKey);
    fd.append('action', 'set_quantity'); // ✅ implement in backend if possible
    fd.append('quantity', String(targetQty));

    let json = null;
    try{
      const res = await fetch(url, { method:'POST', headers:{'X-Requested-With':'XMLHttpRequest'}, body:fd, signal:controller.signal });
      json = await res.json().catch(()=>null);
      if(!res.ok){
        setQty(row, confirmedQty, true);
        lineTotal(row);
        recalcTotals();
        if(json?.error) showToast(json.error);
        return;
      }
    }catch(e){
      if (controller.signal.aborted) return;
      setQty(row, confirmedQty, true);
      lineTotal(row);
      recalcTotals();
      showToast('Error updating quantity');
      return;
    }

    // If server accepted set_quantity we should have numbers back
    if (json && json.ok) {
      // server authoritative values
      if (json.item) {
        const q = Number(json.item.quantity ?? targetQty);
        setQty(row, q, true);
        $('.row-price', row).textContent = fmt(json.item.line_total ?? unitOf(row)*q);
      } else {
        // compute locally if server didn’t send item
        lineTotal(row);
      }
      if (json.totals) {
        if (json.totals.grand_total != null) $('#grandTotal').textContent = fmt(json.totals.grand_total);
        if (json.totals.total_items != null) $('#totalItems').textContent = json.totals.total_items;
      } else {
        recalcTotals();
      }
      return;
    }


    // Fallback path: backend doesn't support set_quantity -> send delta
    // We compute current qty and send required +/- count as a loop.
    let delta = targetQty - confirmedQty;
    let workingQty = confirmedQty;
    while (delta !== 0) {
      const step = delta > 0 ? 'increase' : 'decrease';
      const body2 = new FormData();
      body2.append('_token', token);
      body2.append('cart_key', cartKey);
      body2.append('action', step);
      try{
        const res2 = await fetch(url, { method:'POST', body:body2, signal:controller.signal });
        const j2 = await res2.json().catch(()=>null);
        if(!res2.ok){
          setQty(row, confirmedQty, true);
          lineTotal(row);
          recalcTotals();
          if(j2?.error) showToast(j2.error);
          return;
        }
        // update UI from server when available; else update locally
        const newQty = Number(j2?.item?.quantity ?? (step==='increase'? workingQty+1 : Math.max(1, workingQty-1)));
        workingQty = newQty;
        setQty(row, newQty, true);
        if (j2?.item?.line_total != null) $('.row-price', row).textContent = fmt(j2.item.line_total);
        else lineTotal(row);
        if (j2?.totals){
          if (j2.totals.grand_total != null) $('#grandTotal').textContent = fmt(j2.totals.grand_total);
          if (j2.totals.total_items != null) $('#totalItems').textContent = j2.totals.total_items;
        } else { recalcTotals(); }
        delta += (step==='increase' ? -1 : 1);
      }catch(e){
        if (controller.signal.aborted) return;
        setQty(row, confirmedQty, true);
        lineTotal(row);
        recalcTotals();
        showToast('Error updating quantity');
        return;
      }
    }
  }

  // Debounced scheduling: coalesce rapid clicks into one syncQuantity call
  function scheduleSync(row){
    const { cartKey } = getIds(row);
    const state = pending.get(cartKey) || {};
    if (state.timer) clearTimeout(state.timer);
    const target = Number(qtyEl(row).dataset.qty || 0);
    state.timer = setTimeout(()=> syncQuantity(row, target), 200);
    pending.set(cartKey, state);
  }

  // ---- event handlers ------------------------------------------------------
  document.addEventListener('click', async (e)=>{
    const openTable = e.target.closest('#openTableModal');
    const clearTable = e.target.closest('#clearTable');
    const tableBtn = e.target.closest('.table-btn');
      const plus = e.target.closest('.increase-btn');
      const minus = e.target.closest('.decrease-btn');
      const removeForm = e.target.closest('.remove-item-form');

    if(openTable){
      bootstrap.Modal.getOrCreateInstance($('#tableModal')).show();
      highlightTableButtons(selectedTable);
      return;
    }

    if(clearTable){
      e.preventDefault();
      const fd = new FormData();
      fd.append('_token', getCsrf());
      fd.append('clear', '1');
      try{
        const res = await fetch(tableUrl,{method:'POST',headers:{'X-Requested-With':'XMLHttpRequest'},body:fd});
        const json = await res.json().catch(()=>null);
        if(res.ok){
          selectedTable = null;
          const current = $('#currentTable');
          if(current) current.textContent = '';
          const btn = $('#clearTable');
          if(btn) btn.classList.add('d-none');
          highlightTableButtons(null);
        }
      }catch(err){ showToast('Error clearing table'); }
      return;
    }

    if(tableBtn){
      e.preventDefault();
      const number = tableBtn.dataset.number;
      const fd = new FormData();
      fd.append('_token', getCsrf());
      fd.append('table_number', number);
      try{
        const res = await fetch(tableUrl,{method:'POST',headers:{'X-Requested-With':'XMLHttpRequest'},body:fd});
        const json = await res.json().catch(()=>null);
        if(res.ok){
          selectedTable = json?.table_number ?? number;
          const current = $('#currentTable');
          if(current) current.textContent = `${tableLabel}: ${selectedTable}`;
          highlightTableButtons(selectedTable);
          const clearBtn = $('#clearTable');
          if(clearBtn) clearBtn.classList.remove('d-none');
          const modal = bootstrap.Modal.getInstance($('#tableModal'));
          if(modal) modal.hide();
        }
      }catch(err){ showToast('Error setting table'); }
      return;
    }

     if (removeForm){
        e.preventDefault();
        const row = rowOf(removeForm);
      const action = removeForm.getAttribute('action');
      const fd = new FormData(removeForm);
      // optimistic: drop the row, recalc, then request
      row.remove();
      recalcTotals();
      checkEmptyCart();
      fetch(action, {
        method:'POST',
        headers:{
          'X-Requested-With':'XMLHttpRequest',
          'X-CSRF-TOKEN': getCsrf()
        },
        body:fd
      }).catch(()=>{}); // fire-and-forget
      return;
    }

    if (!plus && !minus) return;

    const row = rowOf(plus || minus);
    const qNow = Number(qtyEl(row).dataset.qty || 0);
    const qNew = Math.max(1, qNow + (plus ? 1 : -1));

    // optimistic UI
    setQty(row, qNew);
    lineTotal(row);
    recalcTotals();
    scheduleSync(row);
  });
  
})();
</script>
@endpush
