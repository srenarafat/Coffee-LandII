@php
    $addRoute = match (Auth::user()->role) {
        'superadmin' => route('superadmin.pos.add'),
        'admin'      => route('admin.pos.add'),
        default      => route('cashier.pos.add'),
    };
@endphp

<div class="modal fade" id="customizerModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg"> {{-- a little wider for tiles --}}
    <div class="modal-content">
      <form id="customizerForm" class="add-to-cart-form" method="POST" action="{{ $addRoute }}">
        @csrf
        <input type="hidden" name="product_id" id="customizerProductId">

        {{-- Hidden fields bound to the tile selections (defaults) --}}
        <input type="hidden" name="size"         id="sizeValue"         value="medium">
        <input type="hidden" name="sugar_level"  id="sugarValueInput"   value="100">
        <input type="hidden" name="ice_option"   id="iceValue"          value="normal">

        <div class="modal-header">
          <h5 class="modal-title" id="customizerTitle">{{ __('messages.add_to_cart') }}</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>

        <div class="modal-body">
          <div class="row g-4">
            {{-- product preview --}}
            <div class="col-12 text-center">
              <img id="customizerImage" src="" alt="" class="img-fluid customizer-img">
              <h5 id="customizerName" class="mt-2"></h5>
              <p id="customizerPrice" class="fw-semibold mb-0"></p>
            </div>

            {{-- quantity --}}
            <div class="col-12 col-md-6">
              <label class="form-label d-block">{{ __('messages.quantity') }}</label>
              <div class="input-group qty-control w-auto">
                <button type="button" id="qtyMinus" class="btn btn-outline-secondary" aria-label="{{ __('messages.decrease_quantity') }}">−</button>
                <input type="text" name="quantity" id="customizerQty" value="1" readonly class="form-control text-center" style="max-width:64px;">
                <button type="button" id="qtyPlus" class="btn btn-outline-secondary" aria-label="{{ __('messages.increase_quantity') }}">+</button>
              </div>
            </div>

            {{-- SIZE --}}
            <div class="col-12">
              <div class="d-flex justify-content-between align-items-center mb-2">
                <h6 class="mb-0">{{ __('messages.drink_size') }}</h6>
                <span class="badge rounded-pill bg-light text-muted border">{{ __('1 Required') }}</span>
              </div>
              <div class="option-grid">
                <button type="button" class="option-tile" data-group="size" data-value="small">
                  <div class="option-title">{{ __('messages.small') }}</div>
                </button>
                <button type="button" class="option-tile active" data-group="size" data-value="medium">
                  <div class="option-title">{{ __('messages.medium') }}</div>
                </button>
                <button type="button" class="option-tile" data-group="size" data-value="large">
                  <div class="option-title">{{ __('messages.large') }}</div>
                </button>
              </div>
            </div>

            {{-- SUGAR LEVEL --}}
            <div class="col-12">
              <div class="d-flex justify-content-between align-items-center mb-2">
                <h6 class="mb-0">{{ __('messages.sugar_level') }}</h6>
                <span class="badge rounded-pill bg-light text-muted border">{{ __('1 Required') }}</span>
              </div>
              <div class="option-grid">
                {{-- values chosen to map to your backend integer sugar % --}}
                <button type="button" class="option-tile" data-group="sugar" data-value="0">
                  <div class="option-title">{{ __('No Sweet') }}</div>
                </button>
                <button type="button" class="option-tile" data-group="sugar" data-value="50">
                  <div class="option-title">{{ __('Less Sweet') }}</div>
                </button>
                <button type="button" class="option-tile active" data-group="sugar" data-value="100">
                  <div class="option-title">{{ __('Normal Sweet') }}</div>
                </button>
                <button type="button" class="option-tile" data-group="sugar" data-value="150">
                  <div class="option-title">{{ __('More Sweet') }}</div>
                </button>
              </div>
            </div>

            {{-- ICE LEVEL --}}
            <div class="col-12">
              <div class="d-flex justify-content-between align-items-center mb-2">
                <h6 class="mb-0">{{ __('messages.ice') }}</h6>
                <span class="badge rounded-pill bg-light text-muted border">{{ __('1 Required') }}</span>
              </div>
              <div class="option-grid">
                <button type="button" class="option-tile" data-group="ice" data-value="none">
                  <div class="option-title">{{ __('messages.no_ice') }}</div>
                </button>
                <button type="button" class="option-tile" data-group="ice" data-value="less">
                  <div class="option-title">{{ __('messages.ice_less') }}</div>
                </button>
                <button type="button" class="option-tile active" data-group="ice" data-value="normal">
                  <div class="option-title">{{ __('messages.ice_normal') }}</div>
                </button>
                <button type="button" class="option-tile" data-group="ice" data-value="more">
                  <div class="option-title">{{ __('More Ice') }}</div>
                </button>
              </div>
            </div>

            {{-- NOTE --}}
            <div class="col-12">
              <label for="customizerNote" class="form-label">{{ __('messages.note_optional') }}</label>
              <input type="text" name="note" id="customizerNote" class="form-control" placeholder="{{ __('messages.note_placeholder') }}">
            </div>
          </div>
        </div>

        <div class="modal-footer">
          <button type="submit" class="btn btn-primary">{{ __('messages.add_to_cart') }}</button>
        </div>
      </form>
    </div>
  </div>
</div>

<style>
  .customizer-img{ max-height:150px; border-radius:12px; }
  /* grid of tiles */
  .option-grid{
    display:grid;
    grid-template-columns: repeat(4, minmax(0,1fr));
    gap:.75rem;
  }
  @media (max-width: 576px){
    .option-grid{ grid-template-columns: repeat(2, minmax(0,1fr)); }
  }

  .option-tile{
    display:flex; align-items:center; justify-content:center;
    height:64px; width:100%;
    border:1px solid #e5e7eb; border-radius:12px;
    background:#fff;
    transition:.15s ease;
    padding:.5rem .75rem;
    cursor:pointer;
  }
  .option-tile:hover{ box-shadow:0 0 0 3px rgba(13,110,253,.15) inset; }
  .option-tile.active{
    background:#fff7e6;               /* soft highlight like your sample */
    border-color:#f7d48a;
    box-shadow:0 0 0 2px #fde7b1 inset;
  }
  .option-title{ font-weight:600; font-size:.95rem; color:#111827; }
  .qty-control .btn{ width:42px; }
</style>

<script>
document.addEventListener('DOMContentLoaded', () => {
  const sizeInput   = document.getElementById('sizeValue');
  const sugarInput  = document.getElementById('sugarValueInput');
  const iceInput    = document.getElementById('iceValue');

  // tile selection handler (radio behavior per group)
  document.getElementById('customizerModal').addEventListener('click', (e) => {
    const tile = e.target.closest('.option-tile');
    if(!tile) return;

    const group = tile.dataset.group;
    const value = tile.dataset.value;

    // clear active in the same group
    document.querySelectorAll(`.option-tile[data-group="${group}"]`).forEach(t => t.classList.remove('active'));
    tile.classList.add('active');

    if(group === 'size')   sizeInput.value  = value;
    if(group === 'sugar')  sugarInput.value = value;    // numeric string (e.g., "100")
    if(group === 'ice')    iceInput.value   = value;    // "normal" | "less" | "none" | "more"
  });

  // keep your quantity buttons working
  const qty = document.getElementById('customizerQty');
  document.getElementById('qtyMinus')?.addEventListener('click', () => {
    const n = Math.max(1, (parseInt(qty.value,10)||1) - 1);
    qty.value = n;
  });
  document.getElementById('qtyPlus')?.addEventListener('click', () => {
    const n = Math.max(1, (parseInt(qty.value,10)||1) + 1);
    qty.value = n;
  });

  // when opening the modal from your script, also reset selections to defaults
  const modalEl = document.getElementById('customizerModal');
  modalEl.addEventListener('show.bs.modal', () => {
    // defaults: medium / 100 / normal (as before)
    sizeInput.value  = 'medium';
    sugarInput.value = '100';
    iceInput.value   = 'normal';

    // visual reset
    ['size','sugar','ice'].forEach(g=>{
      const tiles = document.querySelectorAll(`.option-tile[data-group="${g}"]`);
      tiles.forEach(t=>t.classList.remove('active'));
    });
    document.querySelector('.option-tile[data-group="size"][data-value="medium"]')?.classList.add('active');
    document.querySelector('.option-tile[data-group="sugar"][data-value="100"]')?.classList.add('active');
    document.querySelector('.option-tile[data-group="ice"][data-value="normal"]')?.classList.add('active');

    // reset qty & note like before
    const qtyInput = document.getElementById('customizerQty');
    if (qtyInput) qtyInput.value = 1;
    const note = document.getElementById('customizerNote');
    if (note) note.value = '';
  });
});
</script>
