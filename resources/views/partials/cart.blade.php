<div class="card shadow-sm border-0 rounded-4 d-flex flex-column cart-wrapper">
    <!-- Header -->
    <div class="card-header bg-white border-0 fw-bold fs-5 d-flex justify-content-between align-items-center">
        <span>🛒 {{ __('messages.cart') }}</span>
        <div class="d-flex align-items-center gap-2">
            <span id="currentTable" class="fw-normal">
                @if(session('table_number'))
                    {{ __('messages.table') }}: {{ session('table_number') }}
                @endif
            </span>
            <button type="button" id="clearTable"
                    class="btn btn-sm btn-outline-danger rounded-circle d-flex align-items-center justify-content-center {{ session('table_number') ? '' : 'd-none' }}">
                <i class="bi bi-x-lg"></i>
            </button>
            <button type="button" id="openTableModal"
                    class="btn btn-sm btn-outline-secondary rounded-circle d-flex align-items-center justify-content-center">
                <i class="bi bi-person-lines-fill"></i>
            </button>
        </div>
    </div>

    @if (session('cart') && count(session('cart')) > 0)
    <!-- Product List -->
    <div class="px-3 pt-3 pb-2 overflow-auto cart-panel" style="max-height: 400px;">
        <div class="table-responsive mb-2">
            <div style="overflow-x: auto;">
                <table class="table align-middle text-nowrap">
                    <thead class="cart-header text-white text-center align-middle">
                        <tr>
                            <th>{{ __('messages.product') }}</th>
                            <th>{{ __('messages.qty') }}</th>
                            <th>{{ __('messages.Note') }}</th>
                            <th>{{ __('messages.price') }}</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody id="cartBody">
                        @php $total = 0; $itemCount = 0; @endphp
                        @foreach(session('cart', []) as $key => $item)
                            @php
                                $lineTotal  = $item['price'] * $item['quantity'];
                                $total     += $lineTotal;
                                $itemCount += $item['quantity'];
                                $options    = array_filter([
                                    $item['size'] ?? null,
                                    $item['sugar'] ?? null,
                                    $item['ice'] ?? null,
                                ]);
                            @endphp
                            <tr data-row-id="{{ $key }}">
                                <td style="min-width: 140px;">
                                    <div class="fw-semibold">{{ $item['name'] }}</div>
                                    @if($options || !empty($item['note']))
                                        <div class="cart-options mt-1">
                                            @if($options)
                                                <div class="option-badges d-flex flex-wrap gap-1">
                                                    @foreach($options as $option)
                                                        <span class="badge bg-secondary">{{ $option }}</span>
                                                    @endforeach
                                                </div>
                                            @endif
                                            @if(!empty($item['note']))
                                                <div>&ndash; {{ $item['note'] }}</div>
                                            @endif
                                        </div>
                                    @endif
                                </td>


                                <td class="text-center">
                                    <form method="POST" class="d-inline update-quantity-form">
                                        @csrf
                                        <input type="hidden" name="cart_key" value="{{ $key }}">
                                        <input type="hidden" name="action" value="">
                                        <input type="hidden" class="update-url" value="{{ route($routePrefix . '.pos.update') }}">
                                        <div class="d-flex align-items-center justify-content-center gap-1">
                                            <button type="button" class="btn btn-sm btn-outline-secondary rounded-circle decrease-btn"
                                                    style="width: 28px; height: 28px;">−</button>
                                            <span class="px-2 qty" data-qty="{{ $item['quantity'] }}" data-confirmed="{{ $item['quantity'] }}">{{ $item['quantity'] }}</span>
                                            <button type="button" class="btn btn-sm btn-outline-secondary rounded-circle increase-btn"
                                                    style="width: 28px; height: 28px;">+</button>
                                        </div>
                                    </form>
                                </td>

                                <td>
                                    <button type="button" class="btn btn-sm btn-outline-secondary note-btn"
                                            data-cart-key="{{ $key }}"
                                            data-note="{{ $item['note'] ?? '' }}">
                                        {{ __('messages.edit') }}
                                    </button>
                                </td>

                                <!-- Price shows LINE TOTAL; keep unit price in data-unit for instant recalc -->
                                <td class="text-nowrap">
                                    {{ optional($setting)->currency ?? '$' }}
                                    <span class="row-price"
                                          data-unit="{{ number_format($item['price'], 2, '.', '') }}">
                                        {{ number_format($lineTotal, 2) }}
                                    </span>
                                </td>

                                <td>
                                    <form method="POST" action="{{ route($routePrefix . '.pos.remove', $key) }}" class="d-inline remove-item-form">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-outline-danger">&times;</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Summary + Checkout -->
    <div class="px-3 pb-3" id="checkoutSection">
        <div class="mb-2 text-end">
            <div class="fw-semibold">
                {{ __('messages.total_items') }}:
                <span id="totalItems">{{ $itemCount }}</span>
            </div>
            <div class="fw-bold fs-5">
                {{ __('messages.grand_total') }}: {{ optional($setting)->currency ?? '$' }}
                <span id="grandTotal">{{ number_format($total, 2) }}</span>
            </div>
        </div>

        <form method="POST" action="{{ route($routePrefix . '.pos.payment') }}">
            @csrf
            <input type="hidden" name="discount" id="form-discount">
            <input type="hidden" name="cash_received" id="form-cash">
            <button type="submit" class="btn btn-success w-100">{{ __('messages.checkout') }}</button>
        </form>
    </div>
    @else
    <div class="card-body">
        <p class="text-muted text-center">{{ __('messages.cart_empty') }}</p>
    </div>
    @endif
</div>

@include('partials.comment-modal', ['routePrefix' => $routePrefix, 'comments' => $comments ?? collect()])
@include('partials.table-modal')


<style>
    .cart-header th {
        background-color: #d8eaff !important;
        color: #000 !important;
        font-weight: bold;
    }
    .cart-options {
        font-size: 0.8rem;
        line-height: 1.2;
        color: #6c757d;
    }
    .cart-options .option-badges {
        display: flex;
        flex-wrap: wrap;
        gap: 0.25rem;
    }
    .cart-options .badge {
        font-size: 0.75rem;
    }
    .cart-options .cart-note {
        font-size: 0.75rem;
    }
    @media (max-width: 768px) {
        .cart-header th, .table td { font-size: 13px; padding: 0.4rem; }
        .cart-panel { max-height: 48vh; overflow-y: auto; }
    }
</style>
@push('scripts')
@include('partials.pos-cart-scripts', [
        'config' => [
            'currency' => optional($setting)->currency ?? '$',
            'noteUrl' => route($routePrefix . '.pos.note'),
            'removeLabel' => __('messages.remove_command'),
            'tableUrl' => route($routePrefix . '.pos.table'),
            'tableLabel' => __('messages.table'),
            'selectedTable' => session('table_number'),
            'liveSearchUrl' => route($routePrefix . '.pos.liveSearch'),
            'commentStoreUrl' => route('comments.store'),
            'csrfToken' => csrf_token(),
            'emptyLabel' => __('messages.cart_empty'),
        ],
    ])
@endpush
