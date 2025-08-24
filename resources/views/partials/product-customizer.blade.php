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
                    <div class="text-center mb-3">
                        <img id="customizerImage" src="" alt="" class="img-fluid" style="max-height:150px;">
                    </div>
                    <div class="mb-3">
                        <label for="customizerQty" class="form-label">{{ __('messages.quantity') }}</label>
                        <input type="number" name="quantity" id="customizerQty" value="1" min="1" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label for="customizerSize" class="form-label">{{ __('messages.drink_size') }}</label>
                        <select name="size" id="customizerSize" class="form-select">
                            <option value="small">{{ __('messages.small') }}</option>
                            <option value="medium">{{ __('messages.medium') }}</option>
                            <option value="large">{{ __('messages.large') }}</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="customizerSugar" class="form-label">{{ __('messages.sugar_level') }}: <span id="sugarValue">0</span>%</label>
                        <input type="range" name="sugar_level" id="customizerSugar" min="0" max="100" value="0" class="form-range">
                    </div>
                    <div class="mb-3">
                        <label for="customizerIce" class="form-label">{{ __('messages.ice') }}</label>
                        <select name="ice" id="customizerIce" class="form-select">
                            <option value="normal">{{ __('messages.ice_normal') }}</option>
                            <option value="less">{{ __('messages.ice_less') }}</option>
                            <option value="none">{{ __('messages.no_ice') }}</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="customizerNote" class="form-label">{{ __('messages.note_optional') }}</label>
                        <input type="text" name="note" id="customizerNote" class="form-control">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">{{ __('messages.add_to_cart') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>