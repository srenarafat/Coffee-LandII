@extends('layouts.app')
@section('content')
<style>
html,
body {
    height: 100vh;
    overflow: hidden;
}


/* Keep unfocused inputs looking consistent */
.payment-input:not(:focus) {
    box-shadow: none !important;
    border-color: #ced4da;
}


.number-button {
    border-style: solid;
    height: 60px;
    background-color: #f8f9fa;
    border-radius: 50px;
    font-size: 18px;
    border: #646360 solid 1px;
    transition: background-color 0.2s, transform 0.1s;
}


.number-button:hover {
    background-color: #e2e6ea;
    cursor: pointer;
}


.number-button:active {
    background-color: #ced4da;
    transform: scale(0.96);
}


.special-button {
    background-color: #ffffff;
    font-size: 22px;
}


.special-button:hover {
    background-color: #ddd;
}


.special-button:active {
    background-color: #ccc;
}
</style>




<div class="d-flex justify-content-center align-items-center" style="min-height: calc(100vh - 50px);">
    <form method="POST" action="{{ route($routePrefix . '.pos.checkout') }}" style="width: 100%; max-width: 1000px;" target="invoicePopup" onsubmit="return openInvoiceWindow();">
        @csrf
        <div class="card p-4 shadow">
           <h3 class="fw-bold text-center mb-4 py-2 px-3 text-white bg-primary rounded" style="display:inline-block; border: 5px solid #1654ff;">
    {{ __('messages.payment_method') }}
</h3>
            <div class="d-flex flex-wrap justify-content-between align-items-start mb-4">
                {{-- Left Inputs --}}
                <div style="flex: 1 1 55%;">
                    <div class="mb-3 d-flex justify-content-between align-items-center">
                        <label class="fw-bold mb-0">{{ __('messages.discount_percent') }}</label>
                        <input type="number" name="discount" id="discount" value="{{ old('discount', $discountPercent) }}"
                            class="form-control w-50 payment-input" min="0" max="100" step="0.01">
                    </div>
                    <div id="discountAlert" class="text-danger small d-none">Discount cannot exceed 100%</div>

                    <div class="mb-2">
                        <label>{{ __('messages.cash_received') }} ({{ optional($setting)->currency ?? '$' }})</label>
                        <input type="number" name="cash_usd" id="cashInputUsd" class="form-control payment-input"
                            value="0" min="0" max="100" step="0.01">
                    </div>
                    <div class="mb-2">
                        <label>{{ __('messages.cash_received') }} (៛)</label>
                        <input type="number" name="cash_riel" id="cashInputRiel" class="form-control payment-input"
                            value="0" min="0" max="400000" step="1">
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


                {{-- Right Number Pad --}}
                <div style="flex: 1 1 40%; margin-left:15px;" class="text-center">
                    <div class="d-grid" style="grid-template-columns: repeat(3, 1fr); gap: 15px; min-height: 300px;">
                        @foreach ([7,8,9,4,5,6,1,2,3,0] as $num)
                        <button type="button" class="btn number-button"
                            onclick="appendNumber({{ $num }})">{{ $num }}</button>
                        @endforeach


                        {{-- Dot (.) --}}
                        <button type="button" class="btn number-button" style="font-size: 22px;"
                            onclick="appendNumber('.')">.</button>


                        {{-- Backspace --}}
                        <button type="button" class="btn number-button special-button" onclick="clearInput()">⌫</button>


                    </div>


                    {{-- Total --}}
                    <div class="mt-4 text-end pe-3">
                        <label class="fw-bold" style="font-size: 1.2rem;">
                            {{ __('messages.total') }} ({{ optional($setting)->currency ?? '$' }}):
                            <span id="totalAmount">{{ number_format($total, 2) }}</span>
                        </label>
                    </div>
                </div>
            </div>




            {{-- Payment Method --}}
<div class="mb-4">
    <div class="d-flex justify-content-between flex-wrap gap-2 pt-2">
        @foreach (['Cash' => 'Cash', 'ABA' => 'ABA', 'WING' => 'WING', 'ACLEDA' => 'ACLEDA'] as $value => $label)
            @php $key = strtolower($value); @endphp
            <div class="text-center">
                <input type="radio" class="btn-check" name="method" id="method-{{ $value }}"
                       value="{{ $value }}" {{ $key === 'cash' ? 'checked' : '' }}>
                <label class="btn btn-light" for="method-{{ $value }}">
                    <img src="{{ asset('storage/payment_logos/'.$key.'.png') }}" width="80" alt="{{ $label }}"><br>
                    <span class="fw-semibold">{{ $label }}</span>
                </label>
            </div>
        @endforeach
    </div>
</div>



            {{-- Submit --}}
            <div class="d-flex justify-content-between">
                <a href="{{ route($routePrefix . '.pos.index') }}" class="btn btn-danger">{{ __('messages.cancel') }}</a>
                <button type="submit" class="btn btn-primary">{{ __('messages.print_invoice') }}</button>
            </div>
</div>
    </form>
</div>
@include('partials.toast')
@endsection
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const discountInput = document.getElementById('discount');
    const cashInputUsd = document.getElementById('cashInputUsd');
    const cashInputRiel = document.getElementById('cashInputRiel');
    const changeUsd = document.getElementById('changeUsd');
    const changeRiel = document.getElementById('changeRiel');
    const totalAmount = document.getElementById('totalAmount');
    const discountAlert = document.getElementById('discountAlert');


    const exchangeRate = {{ $setting->exchange_rate }};
    const originalTotal = {{ $total ?? 0 }};
    let selectedInput = cashInputUsd;
    let dynamicMax = 100;
    let dynamicMaxRiel = 400000;
    const paymentLimitMessage = "{{ __('messages.payment_limit_exceeded', ['limit' => ':limit']) }}";
    cashInputRiel.max = dynamicMaxRiel;


    const allInputs = document.querySelectorAll('.payment-input');
    allInputs.forEach(input => {
        input.addEventListener('focus', () => {
            selectedInput = input;
            allInputs.forEach(i => i.classList.remove('focused-input'));
            input.classList.add('focused-input');
        });
    });


    cashInputUsd.focus();


    window.appendNumber = function(num) {
        if (!selectedInput) return;

        selectedInput.focus(); // 🟢 Force focus so box-shadow works correctly
        
        if (num === '.') {
            if (selectedInput === cashInputRiel || selectedInput.value.includes('.')) return;
            selectedInput.value += '.';
            selectedInput.dispatchEvent(new Event('input'));
            return;
        }

        const val = selectedInput.value;
        if (val === '0' || /^0(?:\.0+)?$/.test(val)) {
            selectedInput.value = num === '.' ? '0.' : num;
        } else {
            selectedInput.value += num;
        }

        if (!selectedInput.value.endsWith('.')) {
            clampValues();
        }
        selectedInput.dispatchEvent(new Event('input'));
    };






    window.clearInput = function() {
        if (!selectedInput) return;
        selectedInput.value = '0';
        selectedInput.focus(); // ✅ keep caret visible
        clampValues();
        selectedInput.dispatchEvent(new Event('input'));
    };

     window.openInvoiceWindow = function() {
        const discountPercent = parseFloat(discountInput.value) || 0;
        const discountedTotal = originalTotal * ((100 - discountPercent) / 100);
        const usd = parseFloat(cashInputUsd.value) || 0;
        const riel = parseFloat(cashInputRiel.value) || 0;
        const totalPaidUsd = usd + (riel / exchangeRate);
        if (totalPaidUsd < discountedTotal) {
            showToast("{{ __('messages.insufficient_payment') }}");
            return false;
        }
        window.open('', 'invoicePopup', 'width=800,height=600');
        return true;
    };





    
    function clampValues() {
        let usd = parseFloat(cashInputUsd.value) || 0;
        if (usd > dynamicMax) {
            cashInputUsd.value = dynamicMax.toString();
            usd = dynamicMax;
            showToast(paymentLimitMessage.replace(':limit', dynamicMax.toString()));
        }

        let riel = parseFloat(cashInputRiel.value) || 0;
        if (riel > dynamicMaxRiel) {
            cashInputRiel.value = dynamicMaxRiel.toString();
            riel = dynamicMaxRiel;
            showToast(paymentLimitMessage.replace(':limit', dynamicMaxRiel.toLocaleString()));
        }

        return { usd, riel };
    }

    function updateChange() {
        const discountPercent = parseFloat(discountInput.value) || 0;
        const discountedTotal = originalTotal * ((100 - discountPercent) / 100);
        dynamicMax = Math.min(1000, Math.max(100, Math.floor(discountedTotal / 100) * 100 + 100));
        cashInputUsd.max = dynamicMax;
        dynamicMaxRiel = Math.min(4000000, Math.max(400000, Math.floor(discountedTotal / 100) * 400000 + 400000));
        cashInputRiel.max = dynamicMaxRiel;
        const { usd, riel } = clampValues();

        const totalPaidUsd = usd + (riel / exchangeRate);
        const change = totalPaidUsd - discountedTotal;

        totalAmount.textContent = discountedTotal.toFixed(2);
        changeUsd.value = change >= 0 ? change.toFixed(2) : '0';
        changeRiel.value = change >= 0 ? Math.round(change * exchangeRate).toLocaleString() : '0';
    }


    
    [cashInputUsd, cashInputRiel].forEach(input => {
        input.addEventListener('input', updateChange);
    });

    discountInput.addEventListener('input', () => {
        if (parseFloat(discountInput.value) > 100) {
            discountInput.value = 100;
            discountAlert.classList.remove('d-none');
        } else {
            discountAlert.classList.add('d-none');
        }
        updateChange();
    });

    updateChange();
});
</script>
@endpush



