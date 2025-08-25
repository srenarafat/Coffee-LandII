@php
    $addRoute = match (Auth::user()->role) {
        'superadmin' => route('superadmin.pos.add'),
        'admin'      => route('admin.pos.add'),
        default      => route('cashier.pos.add'),
    };
@endphp

<div class="modal fade" id="customizerModal" tabindex="-1" aria-hidden="true">
  {{-- custom smaller width --}}
  <div class="modal-dialog modal-dialog-centered modal-custom">
    <div class="modal-content border-0 rounded-3 shadow">
      <form id="customizerForm" class="add-to-cart-form" method="POST" action="{{ $addRoute }}">
        @csrf
        <input type="hidden" name="product_id" id="customizerProductId">
        <input type="hidden" name="size"        id="sizeValue"        value="medium">
        <input type="hidden" name="sugar_level" id="sugarValueInput"  value="100">
        <input type="hidden" name="ice_option"  id="iceValue"         value="normal">

        {{-- header --}}
        <div class="modal-header py-2 bg-brown text-white">
          <h6 class="modal-title fw-semibold">{{ __('messages.add_to_cart') }}</h6>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>

        {{-- body --}}
        <div class="modal-body p-2">
          {{-- product preview --}}
          <div class="text-center mb-2">
            <img id="customizerImage" alt="" class="rounded-3 shadow-sm" style="max-height:95px">
            <div class="fw-semibold mt-1 small" id="customizerName"></div>
            <small class="text-muted" id="customizerPrice"></small>
          </div>

          {{-- quantity --}}
          <div class="mb-2">
            <label class="form-label small mb-1">{{ __('messages.quantity') }}</label>
            <div class="input-group input-group-sm" style="max-width:160px">
              <button type="button" id="qtyMinus" class="btn btn-outline-brown">−</button>
              <input type="text" id="customizerQty" name="quantity" value="1" readonly class="form-control text-center" style="max-width:52px">
              <button type="button" id="qtyPlus" class="btn btn-outline-brown">+</button>
            </div>
          </div>

          {{-- size --}}
          <div class="mb-2">
            <div class="d-flex justify-content-between align-items-center mb-1">
              <label class="form-label small mb-0">{{ __('messages.drink_size') }}</label>
              <span class="badge rounded-pill bg-light text-muted border small">{{ __('1 Required') }}</span>
            </div>
            <div class="opt-grid">
              <button type="button" class="opt-tile"        data-group="size"  data-value="small">{{ __('messages.small') }}</button>
              <button type="button" class="opt-tile active" data-group="size"  data-value="medium">{{ __('messages.medium') }}</button>
              <button type="button" class="opt-tile"        data-group="size"  data-value="large">{{ __('messages.large') }}</button>
            </div>
          </div>

          {{-- sugar --}}
          <div class="mb-2">
            <div class="d-flex justify-content-between align-items-center mb-1">
              <label class="form-label small mb-0">{{ __('messages.sugar_level') }}</label>
              <span class="badge rounded-pill bg-light text-muted border small">{{ __('1 Required') }}</span>
            </div>
            <div class="opt-grid">
              <button type="button" class="opt-tile"        data-group="sugar" data-value="0">{{ __('No Sweet') }}</button>
              <button type="button" class="opt-tile"        data-group="sugar" data-value="50">{{ __('Less Sweet') }}</button>
              <button type="button" class="opt-tile active" data-group="sugar" data-value="100">{{ __('Normal Sweet') }}</button>
              <button type="button" class="opt-tile"        data-group="sugar" data-value="150">{{ __('More Sweet') }}</button>
            </div>
          </div>

          {{-- ice --}}
          <div class="mb-2">
            <div class="d-flex justify-content-between align-items-center mb-1">
              <label class="form-label small mb-0">{{ __('messages.ice_level') }}</label>
              <span class="badge rounded-pill bg-light text-muted border small">{{ __('1 Required') }}</span>
            </div>
            <div class="opt-grid">
              <button type="button" class="opt-tile"        data-group="ice" data-value="none">{{ __('messages.no_ice') }}</button>
              <button type="button" class="opt-tile"        data-group="ice" data-value="less">{{ __('messages.ice_less') }}</button>
              <button type="button" class="opt-tile active" data-group="ice" data-value="normal">{{ __('messages.ice_normal') }}</button>
              <button type="button" class="opt-tile"        data-group="ice" data-value="more">{{ __('More Ice') }}</button>
            </div>
          </div>

          {{-- note --}}
          <div>
            <label for="customizerNote" class="form-label small mb-1">{{ __('messages.note_optional') }}</label>
            <input id="customizerNote" name="note" type="text" class="form-control form-control-sm" placeholder="{{ __('messages.note_placeholder') }}">
          </div>
        </div>

        {{-- footer --}}
        <div class="modal-footer py-2">
          <button type="submit" class="btn btn-brown btn-sm px-3">{{ __('messages.add_to_cart') }}</button>
        </div>
      </form>
    </div>
  </div>
</div>

<style>
  /* Custom smaller modal */
  .modal-custom { max-width: 420px; }

  /* Brown theme */
  .bg-brown{ background:#6f4e37 !important; }
  .btn-brown{ background:#6f4e37; color:#fff; }
  .btn-brown:hover{ background:#5c3f2e; color:#fff; }
  .btn-outline-brown{ border:1px solid #6f4e37; color:#6f4e37; }
  .btn-outline-brown:hover{ background:#6f4e37; color:#fff; }

  /* option grid */
  .opt-grid{
    display:grid;
    grid-template-columns:repeat(3,1fr);
    gap:.4rem;
  }
  @media(max-width:480px){ .opt-grid{ grid-template-columns:repeat(2,1fr); } }

  /* option tiles */
  .opt-tile{
    display:flex; align-items:center; justify-content:center;
    height:40px; font-size:.85rem;
    border:1px solid #e0d7d2; border-radius:8px;
    background:#faf8f6; color:#4a372b; font-weight:600;
    transition:all .15s ease; cursor:pointer;
  }
  .opt-tile:hover{ background:#f1ebe7; }
  .opt-tile.active{
    background:#eaddd1; border-color:#c8a98b;
    box-shadow:0 0 0 2px rgba(111,78,55,.25) inset;
    color:#5c3f2e;
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

  // qty +/- (min 1)
  document.getElementById('qtyMinus')?.addEventListener('click', () => {
    qty.value = Math.max(1, (parseInt(qty.value,10)||1) - 1);
  });
  document.getElementById('qtyPlus')?.addEventListener('click', () => {
    qty.value = Math.max(1, (parseInt(qty.value,10)||1) + 1);
  });

  // reset defaults every time the modal opens
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
