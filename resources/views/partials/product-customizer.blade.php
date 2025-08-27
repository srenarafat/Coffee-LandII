@php
    $addRoute = match (Auth::user()->role) {
        'superadmin' => route('superadmin.pos.add'),
        'admin'      => route('admin.pos.add'),
        default      => route('cashier.pos.add'),
    };
@endphp

<div class="modal fade" id="customizerModal" tabindex="-1" aria-hidden="true">
  <!-- smaller, tighter dialog -->
  <div class="modal-dialog modal-dialog-centered modal-compact">
    <div class="modal-content customizer-card">
      <form id="customizerForm" class="add-to-cart-form" method="POST" action="{{ $addRoute }}">
        @csrf
        <input type="hidden" name="product_id" id="customizerProductId">
        <input type="hidden" name="cart_key" id="customizerCartKey">

        {{-- defaults bound to tiles --}}
        <input type="hidden" name="size"        id="sizeValue"        value="medium">
        <input type="hidden" name="sugar_level" id="sugarValueInput"  value="100"> {{-- % --}}
        <input type="hidden" name="ice_option"  id="iceValue"         value="normal">

        <div class="modal-header py-2 customizer-header">
          <h6 class="modal-title fw-semibold">{{ __('messages.add_to_cart') }}</h6>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>

        {{-- Body is scrollable to reduce perceived height --}}
        <div class="modal-body p-3 pb-2 customizer-body">
          {{-- preview --}}
          <div class="text-center mb-2">
            <img id="customizerImage" alt="" class="rounded-3 shadow-sm" style="max-height:84px">
            <div class="mt-1 fw-semibold small" id="customizerName"></div>
            <small class="text-muted fw-semibold" id="customizerPrice"></small>
          </div>

          {{-- Quantity (centered, pill style) --}}
          <div class="mb-2 text-center">
            <!-- <label class="form-label mb-1 fw-semibold">{{ __('messages.quantity') }}</label> -->

            <div class="qty-control mx-auto">
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

          {{-- size --}}
          <div class="mb-2">
            <div class="d-flex justify-content-between align-items-center mb-1">
              <label class="form-label mb-0 small">{{ __('messages.drink_size') }}</label>
              <span class="badge rounded-pill bg-body-secondary text-muted border">1 Required</span>
            </div>
            <div class="opt-grid compact">
              <button type="button" class="opt-tile" data-group="size" data-value="small">{{ __('messages.small') }}</button>
              <button type="button" class="opt-tile active" data-group="size" data-value="medium">{{ __('messages.medium') }}</button>
              <button type="button" class="opt-tile" data-group="size" data-value="large">{{ __('messages.large') }}</button>
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
  /* width ↓   (was ~520px) */
  .modal-compact { max-width: 470px; }
  @media (max-width: 768px){ .modal-compact{ max-width: 90vw; } }

  /* reduce visible height; body scrolls if needed */
  .customizer-body{ max-height: 62vh; overflow:auto; }

  /* prettier card */
  .customizer-card{
    border:0; border-radius:16px;
    box-shadow: 0 20px 60px rgba(0,0,0,.25);
    overflow:hidden;
    animation: c-fade-in .12s ease-out;
    background: #ffffff;
  }
  .customizer-header{
    background: #198754;   /* Bootstrap success green or replace with your sidebar green hex */
    color:#fff;
    border-bottom:0;
}
  .customizer-footer{ border-top:0; }

  @keyframes c-fade-in{ from{ transform:translateY(4px); opacity:0 } to{ transform:none; opacity:1 } }

  /* tighter option grid */
  .opt-grid{
    display:grid; gap:.42rem;
    grid-template-columns: repeat(2, minmax(0,1fr));
  }
  @media (min-width: 576px){
    .opt-grid{ grid-template-columns: repeat(3, minmax(0,1fr)); }
  }
  .opt-grid.compact .opt-tile{ height:40px; font-size:.88rem; padding:.18rem .4rem; }

  /* glossy tile look */
  .opt-tile{
    display:flex; align-items:center; justify-content:center;
    border:1px solid #e7e9ee; border-radius:10px;
    background: linear-gradient(#f9fafc, #f3f6fb);
    color:#1f2937; font-weight:600;
    transition:all .15s ease; cursor:pointer;
  }
  .opt-tile:hover{ background:linear-gradient(#f6f8fb,#eef3fb); border-color:#d6dde7; }
  .opt-tile.active{
    background:linear-gradient(#eaf2ff,#dbe8ff);
    border-color:#b6d2ff;
    box-shadow:0 0 0 2px rgba(13,110,253,.14) inset, 0 2px 8px rgba(13,110,253,.08);
    color:#0b5ed7;
  }
    /* Centered, attractive quantity control */
.qty-control{
  display:inline-flex; align-items:center;
  border:2px solid #198754;         /* your POS green */
  border-radius:999px; padding:2px;
  background:#fff; width:auto;      /* prevent full-width stretch */
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
  background:transparent; font-weight:700; font-size:1.05rem; color:#111;
  padding:0 .25rem;
}
/* remove number spinners across browsers if the type changes later */
.qty-input::-webkit-outer-spin-button,
.qty-input::-webkit-inner-spin-button{ -webkit-appearance:none; margin:0; }
.qty-input[type=number]{ -moz-appearance:textfield; }

.qty-control:focus-within{ box-shadow:0 0 0 3px rgba(25,135,84,.18); }

/* Optional smaller size: add 'qty-sm' to .qty-control */
.qty-control.qty-sm .qty-btn{ width:32px; height:32px; font-size:1rem; }
.qty-control.qty-sm .qty-input{ width:48px; font-size:.95rem; }
</style>

<script>
document.addEventListener('DOMContentLoaded', () => {
  const modalEl = document.getElementById('customizerModal');

  const sizeIn  = document.getElementById('sizeValue');
  const sugarIn = document.getElementById('sugarValueInput');
  const iceIn   = document.getElementById('iceValue');
  const qty     = document.getElementById('customizerQty');
  const form    = document.getElementById('customizerForm');

  // tile toggle
  modalEl.addEventListener('click', (e) => {
    const t = e.target.closest('.opt-tile'); if(!t) return;
    const g = t.dataset.group, v = t.dataset.value;
    modalEl.querySelectorAll(`.opt-tile[data-group="${g}"]`).forEach(x=>x.classList.remove('active'));
    t.classList.add('active');
    if (g==='size')  sizeIn.value  = v;
    if (g==='sugar') sugarIn.value = v;
    if (g==='ice')   iceIn.value   = v;
  });

  // qty +/- (compact)
  document.getElementById('qtyMinus')?.addEventListener('click', () => {
    qty.value = Math.max(1, (parseInt(qty.value,10)||1) - 1);
  });
  document.getElementById('qtyPlus')?.addEventListener('click', () => {
    qty.value = Math.max(1, (parseInt(qty.value,10)||1) + 1);
  });

  // reset defaults each time the dialog opens unless editing
  modalEl.addEventListener('show.bs.modal', () => {
    if (form.dataset.mode === 'edit') return;
    sizeIn.value  = 'medium';
    sugarIn.value = '100';
    iceIn.value   = 'normal';
    qty.value     = 1;
    document.getElementById('customizerNote').value = '';

    ['size','sugar','ice'].forEach(g=>{
      modalEl.querySelectorAll(`.opt-tile[data-group="${g}"]`).forEach(x=>x.classList.remove('active'));
    });
    modalEl.querySelector('.opt-tile[data-group="size"][data-value="medium"]')?.classList.add('active');
    modalEl.querySelector('.opt-tile[data-group="sugar"][data-value="100"]')?.classList.add('active');
    modalEl.querySelector('.opt-tile[data-group="ice"][data-value="normal"]')?.classList.add('active');
  });
});
</script>
