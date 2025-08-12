@forelse ($products as $product)
<div class="product-card">
    <!-- Product Image -->
    <img src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->name }}">

    <!-- Product Name (Khmer or English) -->
    <h6 class="fw-bold">
        {{ app()->getLocale() === 'kh' && $product->name_km ? $product->name_km : $product->name }}
    </h6>

    <!-- Price -->
    <p class="fw-semibold">
        {{ optional($setting)->currency ?? '$' }}{{ number_format($product->price, 2) }}
    </p>

    <!-- Stock Badge -->
    @if ($product->stock <= 0)
        <span class="badge bg-danger">Out of Stock</span>
    @else
        <span class="badge bg-success">In Stock: {{ $product->stock }}</span>
    @endif

    <!-- Add to Cart Form -->
    @php
        $addRoute = match (Auth::user()->role) {
            'superadmin' => route('superadmin.pos.add'),
            'admin' => route('admin.pos.add'),
            default => route('cashier.pos.add'),
        };
    @endphp
    <form method="POST" action="{{ $addRoute }}"
        class="d-flex align-items-center justify-content-between mt-2 add-to-cart-form">
        @csrf
        <input type="hidden" name="product_id" value="{{ $product->id }}">
        <input type="number"
               name="quantity"
               value="1"
               min="1"
               max="{{ $product->stock }}"
               class="form-control form-control-sm me-2 text-center"
               style="width: 60px; border-radius: 10px;"
               {{ $product->stock <= 0 ? 'disabled' : '' }}>

        <button type="submit"
                class="btn rounded-circle d-flex align-items-center justify-content-center"
                style="color: white; width: 36px; height: 36px; background-color: #5f4545;"
                {{ $product->stock <= 0 ? 'disabled' : '' }}>
            <i class="bi bi-plus fs-6"></i>
        </button>
    </form>
</div>
@empty
<div class="text-muted text-center">{{ __('messages.no_product_found') }}</div>
@endforelse
