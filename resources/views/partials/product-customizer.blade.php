@php
    $addRoute = match (Auth::user()->role) {
        'superadmin' => route('superadmin.pos.add'),
        'admin'      => route('admin.pos.add'),
        default      => route('cashier.pos.add'),
    };
@endphp

<div class="modal fade" id="customizerModal" tabindex="-1" aria-hidden="true">
  <!-- ⬇️ slimmer modal -->
  <div class="modal-dialog modal-dialog-centered modal-narrow">
    <div class="modal-content">
      <form id="customizerForm" class="add-to-cart-form" method="POST" action="{{ $addRoute }}">
        @csrf
        <input type="hidden" name="product_id" id="customizerProductId">

        {{-- defaults bound to tiles --}}
        <input type="hidden" name="size"        id="sizeValue"        value="medium">
        <input type="hidden" name="sugar_level" id="sugarValueInput"  value="100"> {{-- % --}}
        <input type="hidden" name="ice_option"  id="iceValue"         value="normal">

        <div class="modal-header py-2" style="background:#6f4e37;color:#fff">
          <h6 class="modal-title fw-semibold">{{ __('messages.add_to_cart') }}</h6>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>

        <div class="modal-body p-3 pb-2">
          {{-- preview --}}
          <div class="text-center mb-2">
            <img id="customizerImage" alt="" class="rounded-3 shadow-sm" style="max-height:96px">
            <div class="mt-1 fw-semibold small" id="customizerName"></div>
            <small class="text-muted fw-semibold" id="customizerPrice"></small>
          </div>

          {{-- qty (compact) --}}
          <div class="mb-2">
            <label class="form-label mb-1 small">{{ __('messages.quantity') }}</label>
            <div class="input-group input-group-sm" style="max-width:170px">
              <button type="button" id="qtyMinus" class="btn btn-outline-secondary">−</button>
              <input type="text" id="customizerQty" name="quantity" value="1" readonly
                     class="form-control text-center" style="max-width:52px">
              <button type="button" id="qtyPlus" class="btn btn-outline-secondary">+</button>
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
              <button type="button" class="opt-tile" data-group="ice" data-value="more">{{ __('messages.c') ?? 'More Ice' }}</button>
            </div>
          </div>

          {{-- note --}}
          <div class="mb-1">
            <label for="customizerNote" class="form-label mb-1 small">{{ __('messages.note_optional') }}</label>
            <input id="customizerNote" name="note" type="text" class="form-control form-control-sm"
                   placeholder="{{ __('messages.note_placeholder') }}">
          </div>
        </div>

        <div class="modal-footer py-2">
          <button type="submit" class="btn btn-primary btn-sm px-3">{{ __('messages.add_to_cart') }}</button>
        </div>
      </form>
    </div>
  </div>
</div>

<style>
  /* narrower dialog */
  .modal-narrow { max-width: 520px; }
  @media (max-width: 768px){ .modal-narrow{ max-width: 92vw; } }

  /* tighter option grid */
  .opt-grid{
    display:grid; gap:.45rem;
    grid-template-columns: repeat(2, minmax(0,1fr)); /* default 2 cols (fits small modal) */
  }
  @media (min-width: 576px){
    .opt-grid{ grid-template-columns: repeat(3, minmax(0,1fr)); } /* 3 cols on ≥ sm */
  }
  .opt-grid.compact .opt-tile{ height:42px; font-size:.9rem; padding:.2rem .4rem; }

  /* tile look */
  .opt-tile{
    display:flex; align-items:center; justify-content:center;
    border:1px solid #e5e7eb; border-radius:10px;
    background:#f8fafc; color:#111827; font-weight:600;
    transition:all .15s ease; cursor:pointer;
  }
  .opt-tile:hover{ background:#f3f6fb; border-color:#d6dde7; }
  .opt-tile.active{
    background:#eef5ff; border-color:#b6d2ff;
    box-shadow:0 0 0 2px rgba(13,110,253,.15) inset; color:#0b5ed7;
  }
</style>

<script>
document.addEventListener('DOMContentLoaded', () => {
  const modalEl = document.getElementById('customizerModal');

  const sizeIn  = document.getElementById('sizeValue');
  const sugarIn = document.getElementById('sugarValueInput');
  const iceIn   = document.getElementById('iceValue');
  const qty     = document.getElementById('customizerQty');

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

  // reset defaults each time the dialog opens
  modalEl.addEventListener('show.bs.modal', () => {
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
