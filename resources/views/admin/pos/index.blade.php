@extends('layouts.app')

@section('content')
<style>
body {
    font-family: 'Battambang', 'Noto Sans Khmer', sans-serif;
    font-size: 14px;
}

.hide-scrollbar {
    scrollbar-width: none;
    -ms-overflow-style: none;
}

.hide-scrollbar::-webkit-scrollbar {
    display: none;
}

.category-pill {
    color: black;
    border-radius: 50px;
    padding: 4px 16px;
    background-color: white;
    border: 2px solid transparent;
    text-decoration: none !important;
    font-weight: normal !important;
}

.category-pill.active,
.category-pill:hover {
    border-color: #5f4545;
    background-color: white;
}

.product-grid {
    display: grid;
    gap: 16px;
    grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
}
@media (min-width: 992px) {
    .product-grid {
        grid-template-columns: repeat(4, 1fr);
    }
    gap: 16px;
}

.product-card {
    background: white;
    border-radius: 10px;
    box-shadow: 0 1px 6px rgba(0, 0, 0, 0.08);
    padding: 14px;
    text-align: center;
}

.product-card img {
    width: 100%;
    height: 120px;
    object-fit: cover;
    border-radius: 6px;
    border: 1px solid #ddd;
    background-color: #f8f8f8;
    margin-bottom: 10px;
}

.cart-box {
    position: sticky;
    top: 80px;
    background: white;
    padding: 16px;
    border-radius: 10px;
    box-shadow: 0 1px 8px rgba(0, 0, 0, 0.08);
}

input::placeholder {
    font-size: 13px;
}

@media (max-width: 991px) {
    .product-cart-layout {
        grid-template-columns: 1fr;
    }

    .cart-box {
        position: static;
    }
}
</style>

<div class="container-fluid">
    <div class="bg-#ccc sticky-top z-3 py-2 px-3" style="top: 0;">
        <div class="d-flex flex-wrap align-items-center justify-content-between">
            <!-- Category Pills -->
            <div class="overflow-auto me-3" style="white-space: nowrap;">
                <div class="d-inline-flex gap-2">
                    <a class="category-pill {{ request('category') ? '' : 'active' }}" href="{{ url()->current() }}">
                        {{ __('All') }}
                    </a>
                    @foreach ($topCategories as $topName => $subs)
                        @php
                            $isActive = $subs->contains(fn($c) => request('category') == $c['id']);
                        @endphp
                        <div class="dropdown d-inline-block">
                            <button class="category-pill dropdown-toggle {{ $isActive ? 'active' : '' }}" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                {{ __($topName) }}
                            </button>
                            <ul class="dropdown-menu">
                                @foreach ($subs as $cat)
                                    <li>
                                        <a class="dropdown-item" href="{{ url()->current() }}?category={{ $cat['id'] }}">
                                            {{ $cat['label'] }}
                                        </a>
                                    </li>
                                @endforeach
                        </ul>
                </div>
            @endforeach
        </div>

            <!-- Language + Search -->
            <div class="d-flex align-items-center gap-3">
                <!-- 🌐 Language Switcher -->
                <form action="{{ route('lang.switch') }}" method="GET">
    <div class="dropdown">
        <button class="btn btn-outline-light border shadow-sm d-flex align-items-center px-2"
                type="button" id="langDropdown" data-bs-toggle="dropdown" aria-expanded="false">
           @php
    $flag = app()->getLocale() === 'kh' ? 'cambodia.png' : 'united-kingdom.png';
@endphp
<img src="{{ asset('images/flags/' . $flag) }}" width="24" height="18">


        </button>
        <ul class="dropdown-menu" aria-labelledby="langDropdown">
            <li>
                <button class="dropdown-item d-flex align-items-center" type="submit" name="locale" value="en">
                    <img src="{{ asset('images/flags/united-kingdom.png') }}" width="24" height="18" class="me-2"> English
                </button>
            </li>
            <li>
                <button class="dropdown-item d-flex align-items-center" type="submit" name="locale" value="kh">
                    <img src="{{ asset('images/flags/cambodia.png') }}" width="24" height="18" class="me-2"> ភាសាខ្មែរ
                </button>
            </li>
        </ul>
    </div>
</form>


                <!-- 🔍 Search -->
                <form method="GET" action="{{ url()->current() }}" class="position-relative" style="width: 240px;">
                    @if(request('category'))
                    <input type="hidden" name="category" value="{{ request('category') }}">
                    @endif
                    <input type="text" name="search" id="searchInput" value="{{ request('search') }}"
                        placeholder="{{ __('messages.search_placeholder') }}"
                        class="form-control shadow-sm ps-4 pe-5 rounded-pill"
                        style="height: 38px; font-size: 14px; border: 1px solid #ccc;">
                    <button type="submit" class="position-absolute top-0 end-0 mt-1 me-2 border-0 bg-transparent">
                        <i class="bi bi-search text-muted fs-5"></i>
                    </button>
                </form>
            </div>
        </div>
    </div>


    <!-- Product & Cart Area -->
    <div class="row g-4 mt-1" style="height: calc(100vh - 165px); overflow: hidden;">
         <div id="product-container" class="col-lg-8 hide-scrollbar" style="overflow-y: auto; height: 100%; padding-bottom: 120px;">
            <div class="product-grid" id="product-grid">
                @include('partials.product-grid', ['products' => $products])
            </div>
        </div>
        <div class="col-lg-4" style="overflow-y: auto; height: 100%;">
            <div id="cart-container">
                @include('partials.cart', ['routePrefix' => 'cashier', 'comments' => $comments])
            </div>
        </div>
    </div>
</div>

<!-- 🔍 Live Search -->
@include('partials.toast')

@include('partials.pos-cart-scripts', ['routePrefix' => 'admin'])

@endsection