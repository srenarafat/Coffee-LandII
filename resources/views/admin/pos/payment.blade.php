@extends('layouts.app')
@section('content')
<style>
  html, body { max-width:100%; overflow-x:hidden; }
  .payment-wrap { max-width: 1080px; margin: 0 auto; }
  .payment-card { padding: 1.25rem; }
  .payment-title { display:inline-block; border: 4px solid #1654ff; }

  /* 2 cols on desktop, 1 on mobile */
  .payment-grid { display: grid; gap: 1rem; }
  @media (min-width: 992px) { .payment-grid { grid-template-columns: 1fr 1fr; } }
  @media (max-width: 991.98px) { .payment-grid { grid-template-columns: 1fr; } }

  .keypad-grid{
    display:grid;
    grid-template-columns: repeat(auto-fit, minmax(80px,1fr));
    gap:12px; width:100%;
  }
  .number-button{
    height:56px; border-radius:48px; font-size:18px;
    border:1px solid #646360; background:#f8f9fa; width:100%;
    transition:background .2s, transform .1s;
  }
  .number-button:hover{ background:#e2e6ea; }
  .number-button:active{ background:#ced4da; transform:scale(.97); }
  .special-button{ font-size:22px; }
  .payment-input:not(:focus){ box-shadow:none!important; border-color:#ced4da; }
  .payment-card *{ max-width:100%; box-sizing:border-box; }

  /* keep footer buttons visible */
  .sticky-actions{
    position: sticky; bottom: 0; background: #fff; padding-top: .75rem;
  }
</style>

<div class="payment-wrap px-2 py-3">
  <form method="POST" action="{{ route($routePrefix.'.pos.checkout') }}" id="paymentForm">
    @csrf

    <div class="card shadow payment-card">
      <h3 class="fw-bold text-center mb-3 py-2 px-3 text-white bg-primary rounded payment-title">
        {{ __('messages.payment_method') }}
      </h3>

      <div class="payment-grid mb-3">
        {{-- LEFT --}}
        <div>
          <div class="mb-3">
            <label class="fw-bold mb-1">Customer</label>
            <select name="customer_id" id="customerSelect" class="form-select">
              <option value="">Walk-in</option>
              @foreach ($customers as $c)
                <option value="{{ $c->id }}">{{ $c->name }}</option>
              @endforeach
              <option value="add_new">+ Add Customer</option>
            </select>
          </div>

          <div class="mb-3 d-none" id="newCustomerBox">
            <label class="fw-bold mb-1">Customer Name</label>
            <input type="text" id="newCustomerInput" class="form-control mb-2" autocomplete="off">
            <input type="text" id="newCustomerPhone" class="form-control mb-2" placeholder="Phone" autocomplete="off">
            <input type="email" id="newCustomerEmail" class="form-control mb-2" placeholder="Email" autocomplete="off">
            <input type="text" id="newCustomerAddress" class="form-control mb-2" placeholder="Address" autocomplete="off">
            <small class="text-muted">Press Enter to create, or it will create automatically on Print.</small>
          </div>

          <div class="mb-3 d-flex align-items-center gap-3">
            <label class="fw-bold mb-0">{{ __('messages.discount_percent') }}</label>
            <input type="text" inputmode="decimal" name="discount" id="discount"
                   value="{{ old('discount', $discountPercent) }}"
                   class="form-control payment-input w-50">
          </div>

          <div class="mb-2">
            <label>{{ __('messages.cash_received') }} ({{ optional($setting)->currency ?? '$' }})</label>
            <input type="text" inputmode="decimal" name="cash_usd" id="cashInputUsd" value="0"
                   class="form-control payment-input">
          </div>
          <div class="mb-2">
            <label>{{ __('messages.cash_received') }} (៛)</label>
            <input type="text" inputmode="decimal" name="cash_riel" id="cashInputRiel" value="0"
                   class="form-control payment-input">
          </div>
          <div class="mb-2">
            <label>{{ __('messages.change') }} ({{ optional($setting)->currency ?? '$' }})</label>
            <input type="text" name="change_usd" id="changeUsd" class="form-control" readonly>
          </div>
          <div class="mb-2">
            <label>{{ __('messages.change') }} (៛)</label>
            <input type="text" name="change_riel" id="changeRiel" class="form-control" readonly>
          </div>
        </div>

        {{-- RIGHT --}}
        <div class="d-flex flex-column">
          <div class="keypad-grid">
            @foreach ([7,8,9,4,5,6,1,2,3,0] as $n)
              <button type="button" class="btn number-button" data-val="{{ $n }}">{{ $n }}</button>
            @endforeach
            <button type="button" class="btn number-button" data-val=".">.</button>
            <button type="button" class="btn number-button special-button" id="btnClear">⌫</button>
          </div>

          <div class="mt-3 text-end">
            <label class="fw-bold" style="font-size:1.1rem;">
              {{ __('messages.total') }} ({{ optional($setting)->currency ?? '$' }}):
              <span id="totalAmount">{{ number_format($total,2) }}</span>
            </label>
          </div>
        </div>
      </div>

      {{-- Method --}}
      <div class="mb-3">
        <div class="d-flex justify-content-between flex-wrap gap-2 pt-2">
          @foreach (['Cash'=>'Cash','ABA'=>'ABA','WING'=>'WING','ACLEDA'=>'ACLEDA'] as $v=>$label)
            <div class="text-center">
              <input type="radio" class="btn-check" name="method" id="method-{{ $v }}"
                     value="{{ $v }}" {{ strtolower($v)==='cash' ? 'checked':'' }}>
              <label class="btn btn-light" for="method-{{ $v }}">
                <img src="{{ asset("storage/payment_logos/{$v}.png") }}" width="72" alt="{{ $label }}"><br>
                <span class="fw-semibold">{{ $label }}</span>
              </label>
            </div>
          @endforeach
        </div>
      </div>

      {{-- Actions (sticky) --}}
      <div class="sticky-actions">
        <div class="d-flex justify-content-between">
          <a href="{{ route($routePrefix.'.pos.index') }}" class="btn btn-danger">{{ __('messages.cancel') }}</a>
          <button type="submit" class="btn btn-primary" id="btnPrint">{{ __('messages.print_invoice') }}</button>
        </div>
      </div>
    </div>
  </form>
</div>

@include('partials.toast')
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
  const discountInput  = document.getElementById('discount');
  const cashInputUsd   = document.getElementById('cashInputUsd');
  const cashInputRiel  = document.getElementById('cashInputRiel');
  const changeUsd      = document.getElementById('changeUsd');
  const changeRiel     = document.getElementById('changeRiel');
  const totalAmount    = document.getElementById('totalAmount');

  const customerSelect = document.getElementById('customerSelect');
  const newCustomerBox = document.getElementById('newCustomerBox');
  const newCustomerInput = document.getElementById('newCustomerInput');
  const newCustomerPhone = document.getElementById('newCustomerPhone');
  const newCustomerEmail = document.getElementById('newCustomerEmail');
  const newCustomerAddress = document.getElementById('newCustomerAddress');

  const form = document.getElementById('paymentForm');
  const createCustomerUrl = @json(route($routePrefix.'.customers.store'));
  const isSuperadmin = @json(Auth::user()->role === 'superadmin');
  const currentShopId = @json(request('shop_id') ?? Auth::user()->shop_id);

  const exchangeRate  = {{ (int)($setting->exchange_rate ?? 4100) }};
  const originalTotal = {{ $total ?? 0 }};

  let selectedInput = cashInputUsd;
  document.querySelectorAll('.payment-input').forEach(el => {
    el.addEventListener('focus', () => selectedInput = el);
  });

  // show/hide quick-create box
  customerSelect.addEventListener('change', () => {
    if (customerSelect.value === 'add_new') {
      newCustomerBox.classList.remove('d-none');
      newCustomerInput.focus();
    } else {
      newCustomerBox.classList.add('d-none');
      newCustomerInput.value = '';
      if (newCustomerPhone) newCustomerPhone.value = '';
      if (newCustomerEmail) newCustomerEmail.value = '';
      if (newCustomerAddress) newCustomerAddress.value = '';
    }
  });

  // Create on Enter
  newCustomerInput.addEventListener('keydown', (e) => {
    if (e.key === 'Enter') { e.preventDefault(); createCustomer(); }
  });

  async function createCustomer(){
    const name = (newCustomerInput.value || '').trim();
    const phone = newCustomerPhone ? newCustomerPhone.value.trim() : undefined;
    const email = newCustomerEmail ? newCustomerEmail.value.trim() : undefined;
    const address = newCustomerAddress ? newCustomerAddress.value.trim() : undefined;
    if (!name) { showToast("{{ __('messages.customer_name_required') }}"); return false; }
    try{
      const payload = { name };
      if (phone) payload.phone = phone;
      if (email) payload.email = email;
      if (address) payload.address = address;
      if (isSuperadmin && currentShopId) payload.shop_id = currentShopId;
      const res = await fetch(createCustomerUrl, {
  method: 'POST',
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
    'X-Requested-With': 'XMLHttpRequest',
    'X-CSRF-TOKEN': '{{ csrf_token() }}'
  },
  body: JSON.stringify(payload)
});

      if (!res.ok) {
        showToast(`Failed to add customer (${res.status})`);
        return false;
      }
      const data = await res.json();
      // insert new option and select it
      const opt = document.createElement('option');
      opt.value = data.id; opt.textContent = data.name;
      customerSelect.insertBefore(opt, customerSelect.querySelector('option[value="add_new"]'));
      customerSelect.value = data.id;
      newCustomerInput.value = '';
      if (newCustomerPhone) newCustomerPhone.value = '';
      if (newCustomerEmail) newCustomerEmail.value = '';
      if (newCustomerAddress) newCustomerAddress.value = '';
      newCustomerBox.classList.add('d-none');
      return true;
    }catch{
      showToast('Failed to add customer');
      return false;
    }
  }

  // keypad
  document.querySelectorAll('.number-button[data-val]').forEach(b=>{
    b.addEventListener('click', ()=>{
      if(!selectedInput) return;
      const v = b.dataset.val;
      let cur = selectedInput.value || '0';
      if(v === '.'){
        if(cur.includes('.')) return;
        selectedInput.value = cur + '.';
      }else{
        if(cur === '0' || /^0(?:\.0+)?$/.test(cur)) selectedInput.value = String(v);
        else selectedInput.value = cur + String(v);
      }
      selectedInput.dispatchEvent(new Event('input'));
      selectedInput.focus();
    })
  });
  document.getElementById('btnClear').addEventListener('click', ()=>{
    if(!selectedInput) return; selectedInput.value='0';
    selectedInput.dispatchEvent(new Event('input')); selectedInput.focus();
  });

  // totals
  function updateChange(){
    const discountPercent = parseFloat(discountInput.value) || 0;
    const discountedTotal = originalTotal * ((100 - discountPercent)/100);
    const usd  = parseFloat(cashInputUsd.value)  || 0;
    const riel = parseFloat(cashInputRiel.value) || 0;
    const totalPaidUsd = usd + (riel / exchangeRate);
    const change = totalPaidUsd - discountedTotal;

    totalAmount.textContent = discountedTotal.toFixed(2);
    changeUsd.value  = change >= 0 ? change.toFixed(2) : '0';
    changeRiel.value = change >= 0 ? Math.round(change * exchangeRate).toLocaleString() : '0';
  }
  [discountInput, cashInputUsd, cashInputRiel].forEach(i=>i.addEventListener('input', updateChange));
  updateChange();

  // popup + submit
  form.addEventListener('submit', async (e)=>{
    e.preventDefault();

    // Block submit while "+ Add Customer" is selected
    if (customerSelect.value === 'add_new') {
      const ok = await createCustomer();
      if (!ok) return;
    }

    // Validate cash enough before opening
    const discountPercent = parseFloat(discountInput.value) || 0;
    const discountedTotal = originalTotal * ((100 - discountPercent)/100);
    const usd  = parseFloat(cashInputUsd.value)  || 0;
    const riel = parseFloat(cashInputRiel.value) || 0;
    const totalPaidUsd = usd + (riel / exchangeRate);
    if (totalPaidUsd < discountedTotal) {
      showToast("{{ __('messages.insufficient_payment') }}");
      return;
    }

    // Single popup target; posting HTML to it
    const w = window.open('about:blank','invoicePopup','width=900,height=700');
    if(!w){ showToast('Popup blocked. Please allow popups.'); return; }

    // make sure the temp inputs never submit
    newCustomerInput.setAttribute('disabled','disabled');
    if (newCustomerPhone) newCustomerPhone.setAttribute('disabled','disabled');
    if (newCustomerEmail) newCustomerEmail.setAttribute('disabled','disabled');
    if (newCustomerAddress) newCustomerAddress.setAttribute('disabled','disabled');

    form.target = 'invoicePopup';
    form.submit();
  });
});
</script>
@endpush
