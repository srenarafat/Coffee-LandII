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
                            <th>{{ __('messages.Note') }}</th>
                            <th>{{ __('messages.price') }}</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody id="cartBody">
                        @php $total = 0; $itemCount = 0; @endphp
                        @foreach(session('cart', []) as $id => $item)
                            @php
                                $lineTotal  = $item['price'] * $item['quantity'];
                                $total     += $lineTotal;
                                $itemCount += $item['quantity'];
                            @endphp
                            @php $itemStock = optional(\App\Models\Product::find($id))->stock ?? 0; @endphp
                            <tr data-row-id="{{ $id }}" data-stock="{{ $itemStock }}">
                                <td style="min-width: 140px;">
                                    <div class="fw-semibold">{{ $item['name'] }}</div>
                                    @if(!empty($item['notes']))
                                        <div class="small text-muted mt-1">
                                            @foreach($item['notes'] as $note)
                                                <div class="ms-2">&ndash; {{ $note }}</div>
                                            @endforeach
                                        </div>
                                    @endif
                                </td>


                                <td class="text-center">
                                    <form method="POST" class="d-inline update-quantity-form">
                                        @csrf
                                        <input type="hidden" name="product_id" value="{{ $id }}">
                                        <input type="hidden" name="action" value="">
                                        <input type="hidden" class="update-url" value="{{ route($routePrefix . '.pos.update') }}">
                                        <div class="d-flex align-items-center justify-content-center gap-1">
                                            <button type="button" class="btn btn-sm btn-outline-secondary rounded-circle decrease-btn"
                                                    style="width: 28px; height: 28px;">−</button>
                                            <span class="px-2 qty" data-qty="{{ $item['quantity'] }}" data-confirmed="{{ $item['quantity'] }}">{{ $item['quantity'] }}</span>
                                            <button type="button" class="btn btn-sm btn-outline-secondary rounded-circle increase-btn"
                                                    style="width: 28px; height: 28px;">+</button>
                                        </div>
                                    </form>
                                </td>

                                <td>
                                    <button type="button" class="btn btn-sm btn-outline-secondary note-btn"
                                            data-product-id="{{ $id }}"
                                            data-notes='@json($item['notes'] ?? [])'>
                                        {{ __('messages.edit') }}
                                    </button>
                                </td>

                                <!-- Price shows LINE TOTAL; keep unit price in data-unit for instant recalc -->
                                <td class="text-nowrap">
                                    {{ optional($setting)->currency ?? '$' }}
                                    <span class="row-price"
                                          data-unit="{{ number_format($item['price'], 2, '.', '') }}">
                                        {{ number_format($lineTotal, 2) }}
                                    </span>
                                </td>

                                <td>
                                    <a href="{{ route($routePrefix . '.pos.remove', $id) }}"
                                       class="btn btn-sm btn-outline-danger remove-item">&times;</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Summary + Checkout -->
    <div class="px-3 pb-3">
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
    .cart-header th {
        background-color: #d8eaff !important;
        color: #000 !important;
        font-weight: bold;
    }
    @media (max-width: 768px) {
        .cart-header th, .table td { font-size: 13px; padding: 0.4rem; }
        .cart-panel { max-height: 48vh; overflow-y: auto; }
    }
</style>
@push('scripts')
<script>
(function(){
  const currency = @json(optional($setting)->currency ?? '$');
  const cartContainer = document.getElementById('cart-container');
  const noteUrl = @json(route($routePrefix . '.pos.note'));
  const removeLabel = @json(__('messages.remove_command'));

  // ---- helpers -------------------------------------------------------------
  const $ = (sel, ctx=document) => ctx.querySelector(sel);
  const $$ = (sel, ctx=document) => Array.from(ctx.querySelectorAll(sel));
  const fmt = n => Number(n||0).toFixed(2);

  function getCsrf(){
    return document.querySelector('meta[name="csrf-token"]')?.content
      || document.querySelector('#commentForm input[name=_token]')?.value
      || '';
  }

  function renderNotes(notes){
    const list = $('#currentNotes');
    if(!list) return;
    list.innerHTML='';
    notes.forEach(n=>{
      const li=document.createElement('li');
      li.className='list-group-item d-flex justify-content-between align-items-center';
      li.textContent=n;
      const btn=document.createElement('button');
      btn.className='btn btn-sm btn-danger remove-note';
      btn.textContent=removeLabel;
      btn.dataset.note=n;
      li.appendChild(btn);
      list.appendChild(li);
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

  // ---- optimistic update core ---------------------------------------------
  // Per-row state: debounce timer + abort controller
  const pending = new Map(); // productId -> {timer, controller}

  function getIds(row){
    const productId = row.getAttribute('data-row-id')
      || $('input[name=product_id]', row)?.value;
    const url = $('.update-url', row)?.value;
    const token = $('input[name=_token]', row)?.value;
    return { productId, url, token };
  }

  // Try to send "set quantity" (preferred). If your backend only supports
  // increase/decrease, it falls back to sending deltas repeatedly.
  async function syncQuantity(row, targetQty){
    const { productId, url, token } = getIds(row);
    if(!productId || !url || !token) return;

    const confirmedQty = Number(qtyEl(row).dataset.confirmed || 0);

    // cancel any in-flight request for this row
    if (pending.get(productId)?.controller) {
      pending.get(productId).controller.abort();
    }
    const controller = new AbortController();
    pending.set(productId, { controller, timer:null });

    // Preferred payload: set exact quantity
    const fd = new FormData();
    fd.append('_token', token);
    fd.append('product_id', productId);
    fd.append('action', 'set_quantity'); // ✅ implement in backend if possible
    fd.append('quantity', String(targetQty));

    let json = null;
    try{
      const res = await fetch(url, { method:'POST', body:fd, signal:controller.signal });
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
      body2.append('product_id', productId);
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
        const newQty = Number(j2?.item?.quantity ?? (step==='increase'? workingQty+1 : Math.max(0, workingQty-1)));
        if (newQty <= 0){ row.remove(); recalcTotals(); return; }
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
    const { productId } = getIds(row);
    const state = pending.get(productId) || {};
    if (state.timer) clearTimeout(state.timer);
    const target = Number(qtyEl(row).dataset.qty || 0);
    state.timer = setTimeout(()=> syncQuantity(row, target), 200);
    pending.set(productId, state);
  }

  // ---- event handlers ------------------------------------------------------
  document.addEventListener('click', async (e)=>{
    const noteBtn = e.target.closest('.note-btn');
    const removeNoteBtn = e.target.closest('.remove-note');
    const plus = e.target.closest('.increase-btn');
    const minus = e.target.closest('.decrease-btn');
    const remove = e.target.closest('.remove-item');

     if (noteBtn){
      const productId = noteBtn.dataset.productId;
      const notes = JSON.parse(noteBtn.dataset.notes || '[]');
      $('#commentProductId').value = productId;
      $('#commentInput').value = '';
      renderNotes(notes);
      bootstrap.Modal.getOrCreateInstance($('#commentModal')).show();
      return;
    }

    if (removeNoteBtn){
      const productId = $('#commentProductId').value;
      const fd = new FormData();
      fd.append('_token', getCsrf());
      fd.append('product_id', productId);
      fd.append('remove_note', removeNoteBtn.dataset.note || '');
      try{
        const res = await fetch(noteUrl, {
          method:'POST',
          headers:{'X-Requested-With':'XMLHttpRequest'},
          body:fd
        });
        const json = await res.json().catch(()=>null);
        if(res.ok && json?.cart){
          const modal = bootstrap.Modal.getInstance($('#commentModal'));
          if(modal) modal.hide();
          cartContainer.innerHTML = json.cart;
          const btn = cartContainer.querySelector(`[data-product-id="${productId}"]`);
          const newNotes = btn ? JSON.parse(btn.dataset.notes || '[]') : [];
          $('#commentProductId').value = productId;
          renderNotes(newNotes);
          bootstrap.Modal.getOrCreateInstance($('#commentModal')).show();
        }
      }catch(err){ showToast('Error updating note'); }
      return;
    }

    if (remove){
      e.preventDefault();
      const row = rowOf(remove);
      // optimistic: drop the row, recalc, then request
      const href = remove.getAttribute('href');
      row.remove();
      recalcTotals();
      fetch(href, { method:'GET' }).catch(()=>{}); // fire-and-forget
      return;
    }

    if (!plus && !minus) return;

    const row = rowOf(plus || minus);
    const qNow = Number(qtyEl(row).dataset.qty || 0);
    const stock = Number(row?.dataset.stock ?? Infinity);
    if (plus && qNow >= stock) {
      showToast(`❌ Out of Stock: Only ${stock} left`);
      return;
    }
    const qNew = Math.max(0, qNow + (plus ? 1 : -1));

    // optimistic UI
    if (qNew === 0){
      row.remove();
      recalcTotals();
    } else {
      setQty(row, qNew);
      lineTotal(row);
      recalcTotals();
      scheduleSync(row);
    }
  });
  
  document.addEventListener('submit', async (e)=>{
    if(e.target.matches('#commentForm')){
      e.preventDefault();
      const form = e.target;
      const productId = $('#commentProductId').value;
      const fd = new FormData(form);
      try{
        const res = await fetch(noteUrl, {
          method:'POST',
          headers:{'X-Requested-With':'XMLHttpRequest'},
          body:fd
        });
        const json = await res.json().catch(()=>null);
        if(res.ok && json?.cart){
          const modal = bootstrap.Modal.getInstance($('#commentModal'));
          if(modal) modal.hide();
          cartContainer.innerHTML = json.cart;
          const btn = cartContainer.querySelector(`[data-product-id="${productId}"]`);
          const notes = btn ? JSON.parse(btn.dataset.notes || '[]') : [];
          $('#commentProductId').value = productId;
          renderNotes(notes);
          $('#commentInput').value = '';
          bootstrap.Modal.getOrCreateInstance($('#commentModal')).show();
        }
      }catch(err){ showToast('Error updating note'); }
    }
  });
})();
</script>
@endpush
