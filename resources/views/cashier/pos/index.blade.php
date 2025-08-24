@extends('layouts.app')

@section('content')
<style>
  /* ===== Base ===== */
  body { font-family: 'Battambang','Noto Sans Khmer',sans-serif; font-size:14px; }

  .hide-scrollbar{ scrollbar-width:none; -ms-overflow-style:none; }
  .hide-scrollbar::-webkit-scrollbar{ display:none; }

  /* ===== Category pills ===== */
  .category-pill{
    color:#111; background:#fff; border:2px solid transparent;
    border-radius:999px; padding:6px 14px; text-decoration:none!important;
    display:inline-flex; align-items:center; gap:.25rem; line-height:1.15;
    transition:border-color .15s, box-shadow .15s;
  }
  .category-pill:hover{ border-color:#5f4545; box-shadow:0 1px 4px rgba(0,0,0,.06); }
  .category-pill.active{ border-color:#5f4545; }
  .category-pill.dropdown-toggle::after{
    margin-left:.4rem; border-top-width:.35em; border-right-width:.35em; border-left-width:.35em;
  }

  /* ===== Dropdown menu ===== */
  .cat-ddmenu{
    min-width:268px; max-height:380px; overflow:auto;
    border-radius:14px; padding:.25rem 0; box-shadow:0 10px 24px rgba(0,0,0,.10);
  }
  .cat-ddmenu .dropdown-divider{ margin:.35rem 0; }

  /* sticky "All Drinks / All Food" row */
  .cat-ddmenu .cat-sticky{
    position:sticky; top:0; z-index:1; background:#fff;
    border-bottom:1px solid #eee;
  }
  .cat-ddmenu .cat-sticky > a{
    display:block; padding:.60rem .85rem; font-weight:700; border-radius:0;
  }

  /* generic row + links */
  .cat-ddmenu .dd-row{ display:block; padding:.50rem .85rem; border-radius:8px; }
  .cat-ddmenu .dd-link{ color:inherit; text-decoration:none; display:block; }
  .cat-ddmenu .dd-link:hover{ background:#f3f4f6; }

  /* active link */
  .cat-ddmenu .dd-link.active{ background:#dbeafe; color:#1d4ed8; font-weight:600; }

  /* depth styling */
  .cat-ddmenu .dd-depth-1{ padding-left:.85rem; font-weight:700; } /* section header */
  .cat-ddmenu .dd-depth-2{ padding-left:1.65rem; }
  .cat-ddmenu .dd-depth-3{ padding-left:2.35rem; }
  .cat-ddmenu .dd-depth-4{ padding-left:3.05rem; }

  /* subtle bullet for depth >= 2 */
  .cat-ddmenu .dd-link.dd-depth-2::before,
  .cat-ddmenu .dd-link.dd-depth-3::before,
  .cat-ddmenu .dd-link.dd-depth-4::before{
    content:"•"; font-size:.6rem; margin-right:.45rem; vertical-align:middle; opacity:.55;
  }

  /* section separators between depth-1 blocks */
  .cat-ddmenu .cat-split{ margin:.25rem 0; border-top:1px dashed #e5e7eb; }

  /* ===== Products ===== */
  .product-grid{ display:grid; gap:16px; grid-template-columns:repeat(auto-fit,minmax(150px,1fr)); }
  @media (min-width:992px){ .product-grid{ grid-template-columns:repeat(4,1fr); } }

  .product-card{ background:#fff; border-radius:10px; box-shadow:0 1px 6px rgba(0,0,0,.08); padding:14px; text-align:center; }
  .product-card img{ width:100%; height:120px; object-fit:cover; border-radius:6px; border:1px solid #ddd; background:#f8f8f8; margin-bottom:10px; }

  /* ===== Cart ===== */
  .cart-box{ position:sticky; top:80px; background:#fff; padding:16px; border-radius:10px; box-shadow:0 1px 8px rgba(0,0,0,.08); }
  input::placeholder{ font-size:13px; }

  @media (max-width:991px){ .product-cart-layout{ grid-template-columns:1fr; } .cart-box{ position:static; } }

  /* ===== Header responsive tweak ===== */
  @media (max-width:768px){ .pos-search { width:200px !important; } }
</style>

<div class="container-fluid">
  {{-- ===== Sticky Header: Categories (left) + Language & Search (right) ===== --}}
  <div class="sticky-top z-3 py-2 px-3 bg-white" style="top:0;">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">

      {{-- LEFT: Category pills (All | Food▾ | Drinks▾) --}}
      <div class="d-flex align-items-center gap-2 flex-wrap">
        <a class="category-pill {{ request('category') ? '' : 'active' }}" href="{{ url()->current() }}">
          {{ __('All') }}
        </a>

        @foreach ($topCategories as $top)
          @php
            $active = request('category') == $top['id']
              || collect($top['subs'])->contains(fn($c) => request('category') == $c['id']);
          @endphp

          <div class="dropdown d-inline-block">
            <button class="category-pill dropdown-toggle {{ $active ? 'active' : '' }}"
                    type="button" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false">
              {{ __($top['name']) }}
            </button>

            {{-- Hierarchical dropdown (uses $top['subs'] which already has name,id,depth) --}}
            <ul class="dropdown-menu cat-ddmenu">
              <li class="cat-sticky">
                <a class="dd-link {{ request('category') == $top['id'] ? 'active' : '' }}"
                   href="{{ url()->current() }}?category={{ $top['id'] }}">
                  {{ __('All') }} {{ __($top['name']) }}
                </a>
              </li>

              @php $isFirstSection = true; $prevDepth = 1; @endphp
              @foreach ($top['subs'] as $i => $item)
                @php
                  $isActive = request('category') == $item['id'];
                  $depthCls = 'dd-depth-' . (int)$item['depth'];
                  // Add a dashed separator before each new depth-1 section (except the first)
                  $showSplit = (!$isFirstSection && (int)$item['depth'] === 1);
                @endphp

                @if($showSplit)
                  <li><div class="cat-split"></div></li>
                @endif

                <li>
                  {{-- Keep all items clickable, but style depth-1 as a section header --}}
                  <a class="dd-row dd-link {{ $depthCls }} {{ $isActive ? 'active' : '' }}"
                     href="{{ url()->current() }}?category={{ $item['id'] }}">
                    {{ $item['name'] }}
                  </a>
                </li>

                @php
                  if ((int)$item['depth'] === 1) { $isFirstSection = false; }
                  $prevDepth = (int)$item['depth'];
                @endphp
              @endforeach
            </ul>
          </div>
        @endforeach
      </div>

      {{-- RIGHT: Language switcher + Search --}}
      <div class="d-flex align-items-center gap-3 ms-auto">
        {{-- 🌐 Language Switcher --}}
        <form action="{{ route('lang.switch') }}" method="GET">
          <div class="dropdown">
            <button class="btn btn-outline-light border shadow-sm d-flex align-items-center px-2"
                    type="button" id="langDropdown" data-bs-toggle="dropdown" aria-expanded="false">
              @php $flag = app()->getLocale() === 'kh' ? 'cambodia.png' : 'united-kingdom.png'; @endphp
              <img src="{{ asset('images/flags/' . $flag) }}" width="24" height="18" alt="lang">
            </button>
            <ul class="dropdown-menu" aria-labelledby="langDropdown">
              <li>
                <button class="dropdown-item d-flex align-items-center" type="submit" name="locale" value="en">
                  <img src="{{ asset('images/flags/united-kingdom.png') }}" width="24" height="18" class="me-2" alt="">
                  English
                </button>
              </li>
              <li>
                <button class="dropdown-item d-flex align-items-center" type="submit" name="locale" value="kh">
                  <img src="{{ asset('images/flags/cambodia.png') }}" width="24" height="18" class="me-2" alt="">
                  ភាសាខ្មែរ
                </button>
              </li>
            </ul>
          </div>
        </form>

        {{-- 🔍 Search (preserves category in query) --}}
        <form method="GET" action="{{ url()->current() }}" class="position-relative pos-search" style="width:260px;">
          @if(request('category'))
            <input type="hidden" name="category" value="{{ request('category') }}">
          @endif
          <input type="text" name="search" id="searchInput" value="{{ request('search') }}"
                 placeholder="{{ __('messages.search_placeholder') }}"
                 class="form-control shadow-sm ps-4 pe-5 rounded-pill"
                 style="height:38px; font-size:14px; border:1px solid #ccc;">
          <button type="submit" class="position-absolute top-0 end-0 mt-1 me-2 border-0 bg-transparent">
            <i class="bi bi-search text-muted fs-5"></i>
          </button>
        </form>
      </div>

    </div>
  </div>

  {{-- ===== Product & Cart Area ===== --}}
  <div class="row g-4 mt-1" style="height: calc(100vh - 165px); overflow:hidden;">
    <div id="product-container" class="col-lg-8 hide-scrollbar" style="overflow-y:auto; height:100%; padding-bottom:120px;">
      <div class="product-grid" id="product-grid">
        @include('partials.product-grid', ['products' => $products])
      </div>
    </div>

    <div class="col-lg-4" style="overflow-y:auto; height:100%;">
      <div id="cart-container">
        @include('partials.cart', ['routePrefix' => 'cashier', 'comments' => $comments])
      </div>
  </div>
</div>
</div>

{{-- 🔔 Toasts / scripts --}}
@include('partials.product-customizer')
@include('partials.toast')
@include('partials.pos-cart-scripts', ['routePrefix' => 'cashier'])
@endsection
