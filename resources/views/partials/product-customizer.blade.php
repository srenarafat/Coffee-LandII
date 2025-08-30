@php
    $addRoute = match (Auth::user()->role) {
        'superadmin' => route('superadmin.pos.add'),
        'admin'      => route('admin.pos.add'),
        default      => route('cashier.pos.add'),
    };
@endphp

<div class="modal fade" id="customizerModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-compact">
    <div class="modal-content customizer-card">
      <form id="customizerForm" class="add-to-cart-form" method="POST" action="{{ $addRoute }}">
        @csrf
        <input type="hidden" name="product_id" id="customizerProductId">
        <input type="hidden" name="cart_key"   id="customizerCartKey">

        {{-- defaults bound to tiles --}}
        <input type="hidden" name="size"        id="sizeValue"        value="medium">
        <input type="hidden" name="sugar_level" id="sugarValueInput"  value="100">
        <input type="hidden" name="ice_option"  id="iceValue"         value="normal">

        <div class="modal-header py-2 customizer-header">
          <h6 class="modal-title fw-semibold">{{ __('messages.add_to_cart') }}</h6>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>

        <div class="modal-body p-3 pb-2 customizer-body">
          {{-- preview --}}
          <div class="text-center mb-2">
            <img id="customizerImage" alt="" class="rounded-3 shadow-sm" style="max-height:84px">
            <div class="mt-1 fw-semibold small" id="customizerName"></div>
            <small class="text-muted fw-semibold" id="customizerPrice"></small>
          </div>

          {{-- Quantity --}}
          <div class="mb-2 text-center">
            <div class="qty-control mx-auto" aria-label="Quantity">
              <button type="button" id="qtyMinus" class="qty-btn" aria-label="Decrease">−</button>
              <input
                type="text"
                id="customizerQty"
                name="quantity"
                value="1"
                readonly
                class="qty-input"
                inputmode="numeric"
                autocomplete="off">
              <button type="button" id="qtyPlus" class="qty-btn" aria-label="Increase">+</button>
            </div>
          </div>

          <div class="drink-options">
            {{-- size --}}
            <div class="mb-2">
              <div class="d-flex justify-content-between align-items-center mb-1">
                <label class="form-label mb-0 small">{{ __('messages.drink_size') }}</label>
                <span class="badge rounded-pill bg-body-secondary text-muted border">1 Required</span>
              </div>
              <div class="opt-grid compact">
                <button type="button" class="opt-tile" data-group="size"  data-value="small">{{ __('messages.small') }}</button>
                <button type="button" class="opt-tile active" data-group="size" data-value="medium">{{ __('messages.medium') }}</button>
                <button type="button" class="opt-tile" data-group="size"  data-value="large">{{ __('messages.large') }}</button>
              </div>
            </div>

            {{-- sugar --}}
            <div class="mb-2">
              <div class="d-flex justify-content-between align-items-center mb-1">
                <label class="form-label mb-0 small">{{ __('messages.sugar_level') }}</label>
                <span class="badge rounded-pill bg-body-secondary text-muted border">1 Required</span>
              </div>
              <div class="opt-grid compact">
                <button type="button" class="opt-tile"        data-group="sugar" data-value="10">10%</button>
                <button type="button" class="opt-tile"        data-group="sugar" data-value="30">30%</button>
                <button type="button" class="opt-tile"        data-group="sugar" data-value="50">50%</button>
                <button type="button" class="opt-tile"        data-group="sugar" data-value="70">70%</button>
                <button type="button" class="opt-tile active" data-group="sugar" data-value="100">100%</button>
                <button type="button" class="opt-tile"        data-group="sugar" data-value="150">150%</button>
              </div>
            </div>

            {{-- ice --}}
            <div class="mb-2">
              <div class="d-flex justify-content-between align-items-center mb-1">
                <label class="form-label mb-0 small">{{ __('messages.ice_level') ?? __('messages.ice') }}</label>
                <span class="badge rounded-pill bg-body-secondary text-muted border">1 Required</span>
              </div>
              <div class="opt-grid compact">
                <button type="button" class="opt-tile" data-group="ice" data-value="none">{{ __('messages.no_ice') }}</button>
                <button type="button" class="opt-tile" data-group="ice" data-value="less">{{ __('messages.ice_less') }}</button>
                <button type="button" class="opt-tile active" data-group="ice" data-value="normal">{{ __('messages.ice_normal') }}</button>
                <button type="button" class="opt-tile" data-group="ice" data-value="more">{{ __('messages.more_ice') ?? 'More Ice' }}</button>
              </div>
            </div>
          </div>

          {{-- food options (styled like drink tiles; still checkboxes) --}}
          <div class="food-options d-none mb-2">
            <div class="d-flex justify-content-between align-items-center mb-1">
              <label class="form-label mb-0 small">{{ __('messages.options') }}</label>
              <span class="badge rounded-pill bg-body-secondary text-muted border">Optional</span>
            </div>

            <div class="chip-grid">
              <label class="opt-chip">
                <input type="checkbox" class="chip-check" id="optNoVeg"   name="options[]" value="No Vegetables">
                <span>No Vegetables</span>
              </label>

              <label class="opt-chip">
                <input type="checkbox" class="chip-check" id="optNoSweet" name="options[]" value="No Sweet">
                <span>No Sweet</span>
              </label>

              <label class="opt-chip">
                <input type="checkbox" class="chip-check" id="optNoSalty" name="options[]" value="No Salty">
                <span>No Salty</span>
              </label>

              <label class="opt-chip">
                <input type="checkbox" class="chip-check" id="optNoSpicy" name="options[]" value="No Spicy">
                <span>No Spicy</span>
              </label>
            </div>
          </div>

          {{-- note --}}
          <div class="mb-1">
            <label for="customizerNote" class="form-label mb-1 small">{{ __('messages.note_optional') }}</label>
            <input id="customizerNote" name="note" type="text" class="form-control form-control-sm"
                   placeholder="{{ __('messages.note_placeholder') }}">
          </div>
        </div>

        <div class="modal-footer py-4 customizer-footer">
          <button type="submit" class="btn btn-primary btn-sm px-3">{{ __('messages.add_to_cart') }}</button>
        </div>
      </form>
    </div>
  </div>
</div>

<style>
  .modal-compact { max-width: 470px; }
  @media (max-width: 768px){ .modal-compact{ max-width: 90vw; } }
  .customizer-body{ max-height: 62vh; overflow:auto; }
  .customizer-card{
    border:0; border-radius:16px;
    box-shadow: 0 20px 60px rgba(0,0,0,.25);
    overflow:hidden;
    animation: c-fade-in .12s ease-out;
    background: #ffffff;
  }
  .customizer-header{ background:#198754; color:#fff; border-bottom:0; }
  .customizer-footer{ border-top:0; }
  @keyframes c-fade-in{ from{ transform:translateY(4px); opacity:0 } to{ transform:none; opacity:1 } }

  .opt-grid{ display:grid; gap:.42rem; grid-template-columns: repeat(2, minmax(0,1fr)); }
  @media (min-width: 576px){ .opt-grid{ grid-template-columns: repeat(3, minmax(0,1fr)); } }
  .opt-grid.compact .opt-tile{ height:40px; font-size:.88rem; padding:.18rem .4rem; }

  .opt-tile{
    display:flex; align-items:center; justify-content:center;
    border:1px solid #e7e9ee; border-radius:10px;
    background: linear-gradient(#f9fafc, #f3f6fb);
    color:#1f2937; font-weight:600; cursor:pointer;
    transition:all .15s ease;
  }
  .opt-tile:hover{ background:linear-gradient(#f6f8fb,#eef3fb); border-color:#d6dde7; }
  .opt-tile.active{
    background:linear-gradient(#eaf2ff,#dbe8ff);
    border-color:#b6d2ff;
    box-shadow:0 0 0 2px rgba(13,110,253,.14) inset, 0 2px 8px rgba(13,110,253,.08);
    color:#0b5ed7;
  }

  .qty-control{
    display:inline-flex; align-items:center;
    border:2px solid #198754; border-radius:999px; padding:2px;
    background:#fff; width:auto;
    box-shadow:0 6px 16px rgba(0,0,0,.08);
  }
  .qty-btn{
    width:38px; height:38px; border:none; border-radius:999px;
    background:#198754; color:#fff; font-weight:800; font-size:1.1rem; line-height:1;
    display:flex; align-items:center; justify-content:center; cursor:pointer;
    transition:transform .08s ease, background .2s ease, box-shadow .2s;
    box-shadow:0 2px 6px rgba(25,135,84,.25);
  }
  .qty-btn:hover{ background:#157347; }
  .qty-btn:active{ transform:scale(.95); }
  .qty-input{
    width:60px; border:none; outline:none; text-align:center;
    background:transparent; font-weight:700; font-size:1.05rem; color:#111; padding:0 .25rem;
  }
  .qty-input::-webkit-outer-spin-button, .qty-input::-webkit-inner-spin-button{ -webkit-appearance:none; margin:0; }
  .qty-input[type=number]{ -moz-appearance:textfield; }
  .qty-control:focus-within{ box-shadow:0 0 0 3px rgba(25,135,84,.18); }

  /* === Food chips (multi-select) === */
  .chip-grid{
    display:grid; gap:.42rem;
    grid-template-columns: repeat(2, minmax(0,1fr));
  }
  @media (min-width:576px){
    .chip-grid{ grid-template-columns: repeat(3, minmax(0,1fr)); }
  }
  .opt-chip{
    display:flex; align-items:center; justify-content:center;
    border:1px solid #e7e9ee; border-radius:10px;
    background: linear-gradient(#f9fafc, #f3f6fb);
    color:#1f2937; font-weight:600; padding:.5rem .6rem;
    cursor:pointer; user-select:none; min-height:40px;
    transition:all .15s ease;
  }
  .opt-chip:hover{ background:linear-gradient(#f6f8fb,#eef3fb); border-color:#d6dde7; }
  .opt-chip.active{
    background:linear-gradient(#eaf2ff,#dbe8ff);
    border-color:#b6d2ff;
    box-shadow:0 0 0 2px rgba(13,110,253,.14) inset, 0 2px 8px rgba(13,110,253,.08);
    color:#0b5ed7;
  }
  .chip-check{ position:absolute; opacity:0; }
</style>

<script>
/* Guard to avoid double binding if this partial is injected twice */
if (!window.__customizerBound__) {
  window.__customizerBound__ = true;

  document.addEventListener('DOMContentLoaded', () => {
    const modalEl = document.getElementById('customizerModal');
    const form    = document.getElementById('customizerForm');

    const sizeIn  = document.getElementById('sizeValue');
    const sugarIn = document.getElementById('sugarValueInput');
    const iceIn   = document.getElementById('iceValue');
    const qtyEl   = document.getElementById('customizerQty');
    const priceEl = document.getElementById('customizerPrice');

    // === S/M/L prefix + per-size display ===
    const updatePrice = () => {
      const size  = (sizeIn.value || 'medium').toLowerCase();
      const map   = { small: 'S', medium: 'M', large: 'L' };
      const label = map[size] || (size[0] || '').toUpperCase();

      const key   = 'price' + size.charAt(0).toUpperCase() + size.slice(1); // priceSmall|priceMedium|priceLarge
      let price   = (form?.dataset?.[key] || '').trim();

      // fallback: base price captured on open
      if (!price) {
        const base = (priceEl?.dataset?.basePrice || priceEl?.textContent || '').replace(/^[A-Z]:\s*/, '');
        price = base;
      }

      const hasSizePricing = form?.dataset?.priceSmall || form?.dataset?.priceMedium || form?.dataset?.priceLarge;
      if (priceEl) priceEl.textContent = hasSizePricing ? `${label}: ${price}` : price;
    };

    const clampQty = (n) => Math.max(1, (parseInt(n, 10) || 1));

    // Single delegated click handler for drink tiles & qty
    modalEl.addEventListener('click', (e) => {
      // Option tiles
      const tile = e.target.closest('.opt-tile');
      if (tile) {
        const g = tile.dataset.group, v = tile.dataset.value;
        modalEl.querySelectorAll(`.opt-tile[data-group="${g}"]`).forEach(x=>x.classList.remove('active'));
        tile.classList.add('active');
        if (g==='size')  { sizeIn.value  = v; updatePrice(); }
        if (g==='sugar') { sugarIn.value = v; }
        if (g==='ice')   { iceIn.value   = v; }
        return;
      }

      // Quantity buttons
      if (e.target.closest('#qtyPlus'))  qtyEl.value = clampQty((qtyEl.value || 1) * 1 + 1);
      if (e.target.closest('#qtyMinus')) qtyEl.value = clampQty((qtyEl.value || 1) * 1 - 1);
    });

    // --- Food option chips (checkboxes styled as tiles) ---
    const initChips = () => {
      const grid = document.querySelector('#customizerModal .chip-grid');
      if (!grid) return;
      grid.querySelectorAll('.opt-chip').forEach(lbl => {
        const cb = lbl.querySelector('.chip-check');
        // sync initial state
        lbl.classList.toggle('active', cb.checked);
        // keep label state synced with checkbox
        cb.addEventListener('change', () => {
          lbl.classList.toggle('active', cb.checked);
        });
      });
    };

    // Reset defaults each time the dialog opens unless editing
    modalEl.addEventListener('show.bs.modal', () => {
      // capture a clean base price once when opening
      if (priceEl) {
        const clean = (priceEl.textContent || '').replace(/^[A-Z]:\s*/, '');
        priceEl.dataset.basePrice = clean;
      }

      if (form?.dataset?.mode === 'edit') { updatePrice(); initChips(); return; }

      sizeIn.value  = 'medium';
      sugarIn.value = '100';
      iceIn.value   = 'normal';
      qtyEl.value   = 1;
      const note = document.getElementById('customizerNote');
      if (note) note.value = '';

      ['size','sugar','ice'].forEach(g=>{
        modalEl.querySelectorAll(`.opt-tile[data-group="${g}"]`).forEach(x=>x.classList.remove('active'));
      });
      modalEl.querySelector('.opt-tile[data-group="size"][data-value="medium"]')?.classList.add('active');
      modalEl.querySelector('.opt-tile[data-group="sugar"][data-value="100"]')?.classList.add('active');
      modalEl.querySelector('.opt-tile[data-group="ice"][data-value="normal"]')?.classList.add('active');

      // clear chips and sync
      document.querySelectorAll('#customizerModal .chip-check').forEach(cb => cb.checked = false);
      initChips();
      updatePrice();
    });
  });
}
</script>
