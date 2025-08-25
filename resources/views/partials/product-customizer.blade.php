@php
    $addRoute = match (Auth::user()->role) {
        'superadmin' => route('superadmin.pos.add'),
        'admin' => route('admin.pos.add'),
        default => route('cashier.pos.add'),
    };
@endphp
<div class="modal fade" id="customizerModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="customizerForm" class="add-to-cart-form" method="POST" action="{{ $addRoute }}">
                @csrf
                <input type="hidden" name="product_id" id="customizerProductId">
                <div class="modal-header">
                    <h5 class="modal-title" id="customizerTitle">{{ __('messages.add_to_cart') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-12 text-center">
                            <img id="customizerImage" src="" alt="" class="img-fluid customizer-img">
                            <h5 id="customizerName" class="mt-2"></h5>
                            <p id="customizerPrice" class="fw-semibold"></p>
                        </div>

                        <div class="col-6">
                            <label class="form-label">{{ __('messages.quantity') }}</label>
                            <div class="input-group qty-control">
                                <button type="button" id="qtyMinus" class="btn btn-outline-secondary" aria-label="{{ __('messages.decrease_quantity') }}">-</button>
                                <input type="text" name="quantity" id="customizerQty" value="1" readonly class="form-control text-center" aria-label="{{ __('messages.quantity') }}">
                                <button type="button" id="qtyPlus" class="btn btn-outline-secondary" aria-label="{{ __('messages.increase_quantity') }}">+</button>
                            </div>
                        </div>

                        <div class="col-6">
                            <label for="customizerSize" class="form-label">{{ __('messages.drink_size') }}</label>
                            <select name="size" id="customizerSize" class="form-select" aria-label="{{ __('messages.drink_size') }}">
                                <option value="small">{{ __('messages.small') }}</option>
                                <option value="medium">{{ __('messages.medium') }}</option>
                                <option value="large">{{ __('messages.large') }}</option>
                            </select>
                        </div>

                        <div class="col-6">
                            <label for="customizerSugar" class="form-label">{{ __('messages.sugar_level') }}: <span id="sugarValue">0</span>%</label>
                            <input type="range" name="sugar_level" id="customizerSugar" min="0" max="100" value="0" class="form-range" aria-label="{{ __('messages.sugar_level') }}">
                        </div>

                        <div class="col-6">
                            <label for="customizerIce" class="form-label">{{ __('messages.ice') }}</label>
                            <select name="ice" id="customizerIce" class="form-select" aria-label="{{ __('messages.ice') }}">
                                <option value="normal">{{ __('messages.ice_normal') }}</option>
                                <option value="less">{{ __('messages.ice_less') }}</option>
                                <option value="none">{{ __('messages.no_ice') }}</option>
                            </select>
                        </div>

                        <div class="col-12">
                            <label for="customizerNote" class="form-label">{{ __('messages.note_optional') }}</label>
                            <input type="text" name="note" id="customizerNote" class="form-control" aria-label="{{ __('messages.note_optional') }}" placeholder="{{ __('messages.note_placeholder') }}">
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
    .customizer-img {
        max-height: 150px;
    }
    .qty-control input {
        width: 60px;
    }
</style>