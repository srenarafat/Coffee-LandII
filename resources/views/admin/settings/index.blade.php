@extends('layouts.app')

@section('content')
<div class="container my-5">
    <div class="card shadow border-0 rounded-4">
        <div class="card-header bg-white border-0 py-3">
            <h5 class="mb-0 fw-bold d-flex align-items-center">
                <i class="bi bi-gear-fill me-2 text-primary"></i>
                {{ __('messages.shop_settings') }}
            </h5>
        </div>
        <div class="card-body">

        {{-- ✅ Flash Success Message --}}
        @if(session('success'))
            <div id="successToast"
                 class="alert alert-success d-flex align-items-center justify-content-between shadow-sm px-4 py-2 animate__animated animate__fadeInDown"
                 style="position: absolute; top: 0; right: 0; margin: 12px; max-width: 360px;">
                🎉 <strong>{{ session('success') }}</strong>
            </div>
        @endif

        <form method="POST"
            action="{{ auth()->user()->role === 'superadmin'
                ? route('superadmin.settings.update')
                : route('admin.settings.update') }}">
            @csrf

            <div class="row g-3">
                <!-- Shop Name -->
                <div class="col-md-6">
                    <label for="shop_name" class="form-label fw-semibold">
                        🏪 {{ __('messages.shop_name') }}
                    </label>
                    <input type="text" class="form-control" id="shop_name" name="shop_name"
                        value="{{ old('shop_name', optional($setting)->shop_name) }}" required>
                </div>

                <!-- Currency -->
                <div class="col-md-6">
                    <label for="currency" class="form-label fw-semibold">
                        💵 {{ __('messages.currency') }}
                    </label>
                    <input type="text" class="form-control" id="currency" name="currency"
                        value="{{ old('currency', optional($setting)->currency) }}" required>
                </div>

                <div class="col-md-6">
                    <label for="exchange_rate" class="form-label fw-semibold">
                        💱 {{ __('messages.exchange_rate') }}
                    </label>
                    <input type="number" step="0.01" class="form-control" id="exchange_rate"
                           name="exchange_rate"
                           value="{{ old('exchange_rate', optional($setting)->exchange_rate) }}" required>
                </div>

                <!-- Discount -->
                <div class="col-md-6">
                    <label for="discount_percent" class="form-label fw-semibold">
                        🎁 {{ __('messages.discount_percent') }}
                    </label>
                    <input type="number" step="0.01" min="0" max="100" class="form-control" id="discount_percent" name="discount_percent"
                        value="{{ old('discount_percent', optional($setting)->discount_percent) }}" required>
                        <div id="discountAlert" class="text-danger small d-none">Discount cannot exceed 100%</div>
                </div>
            </div>

            <div class="text-end mt-4">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-save me-1"></i> {{ __('messages.update_settings') }}
                </button>
            </div>
        </form>
        </div>
    </div>
</div>
@endsection

@push('styles')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" />
<style>
    #successToast {
        border-left: 6px solid #198754;
        background-color: #d1e7dd;
        font-size: 14px;
        border-radius: 6px;
        z-index: 1050;
    }
</style>
@endpush

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const toast = document.getElementById('successToast');
        if (toast) {
            setTimeout(() => {
                toast.classList.remove('animate__fadeInDown');
                toast.classList.add('animate__fadeOutUp');
                setTimeout(() => toast.remove(), 800);
            }, 2000);
        }
        
        const discountInput = document.getElementById('discount_percent');
        const discountAlert = document.getElementById('discountAlert');

        if (discountInput && discountAlert) {
            discountInput.addEventListener('input', function () {
                const value = parseFloat(this.value);
                if (value > 100) {
                    this.value = 100;
                    discountAlert.classList.remove('d-none');
                } else {
                    discountAlert.classList.add('d-none');
                }
            });
        }
    });
</script>
@endpush
