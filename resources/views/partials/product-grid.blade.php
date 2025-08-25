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

    <!-- Customize Button -->
    <button type="button"
            class="btn rounded-circle d-flex align-items-center justify-content-center mt-2 open-customizer"
            data-id="{{ $product->id }}"
            data-name="{{ app()->getLocale() === 'kh' && $product->name_km ? $product->name_km : $product->name }}"
            data-price="{{ optional($setting)->currency ?? '$' }}{{ number_format($product->price, 2) }}"
            data-image="{{ asset('storage/' . $product->image) }}"
            style="color: white; width: 36px; height: 36px; background-color: #5f4545;">
        <i class="bi bi-plus fs-6"></i>
    </button>
</div>
@empty
<div class="text-muted text-center">{{ __('messages.no_product_found') }}</div>
@endforelse
