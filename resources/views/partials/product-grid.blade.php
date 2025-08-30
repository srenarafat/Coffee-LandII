@php $currency = optional($setting)->currency ?? '$'; @endphp
@forelse ($products as $product)
<div class="product-card position-relative">
    <!-- Product Image -->
    <img src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->name }}">

    <!-- Product Name (Khmer or English) -->
    <h6 class="fw-bold">
        {{ app()->getLocale() === 'kh' && $product->name_km ? $product->name_km : $product->name }}
    </h6>

    <!-- Price -->
    <p class="fw-semibold mb-5"> {{-- add bottom space so text doesn't collide with the button --}}
        {{ $currency }}{{ number_format($product->priceForSize('medium'), 2) }}
    </p>

    <!-- Customize Button -->
    <button type="button"
            class="btn rounded-circle d-flex align-items-center justify-content-center position-absolute open-customizer"
            data-id="{{ $product->id }}"
            data-name="{{ app()->getLocale() === 'kh' && $product->name_km ? $product->name_km : $product->name }}"
            data-price-small="{{ $product->price_small !== null ? $currency . number_format($product->price_small, 2) : '' }}"
            data-price-medium="{{ $product->price_medium !== null ? $currency . number_format($product->price_medium, 2) : $currency . number_format($product->priceForSize('medium'), 2) }}"
            data-price-large="{{ $product->price_large !== null ? $currency . number_format($product->price_large, 2) : '' }}"
            data-image="{{ asset('storage/' . $product->image) }}"
            data-is-food="{{ $product->isFood() ? 'true' : 'false' }}"
            data-is-water="{{ $product->isWater() ? 'true' : 'false' }}"
            style="color:#fff;width:36px;height:36px;background-color:#5f4545;bottom:10px;right:10px;">
        <i class="bi bi-plus fs-6"></i>
    </button>
</div>
@empty
<div class="text-muted text-center">{{ __('messages.no_product_found') }}</div>
@endforelse
<style>
  .product-card{
    background:#fff;border:1px solid #eee;border-radius:16px;padding:12px;
    box-shadow:0 2px 10px rgba(0,0,0,.04);
    display:flex;flex-direction:column;align-items:center;text-align:center;
    height:100%;
  }
  .product-card img{
    width:100%;height:140px;object-fit:contain;border-radius:12px;background:#f7f7f7;
  }
  .product-card .open-customizer{
    box-shadow:0 6px 16px rgba(0,0,0,.18);
  }
</style>
