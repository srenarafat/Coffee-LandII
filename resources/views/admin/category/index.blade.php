@extends('layouts.app')

@section('content')
<div class="container-fluid mt-4">
  <div class="card shadow-sm border-0 rounded-4">
    <div class="card-body position-relative">

      {{-- Toast --}}
      @if(session('success'))
        <div id="successToast"
             class="alert alert-success d-flex align-items-center justify-content-between shadow-sm px-4 py-2"
             style="position:absolute;top:12px;right:12px;max-width:360px;border-left:6px solid #198754;">
          🎉 <strong class="ms-2">{{ session('success') }}</strong>
        </div>
      @endif

      {{-- Header --}}
      <div class="d-flex flex-wrap align-items-center justify-content-between mb-3 gap-2">
        <h4 class="fw-bold m-0">🗄️ {{ __('messages.category_list') }}</h4>
        <div class="d-flex gap-2">
          <input id="categorySearch" type="text" class="form-control form-control-sm"
                 placeholder="{{ __('messages.search') ?: 'Search categories…' }}">
          <button id="expandAll"  class="btn btn-outline-secondary btn-sm">Expand all</button>
          <button id="collapseAll" class="btn btn-outline-secondary btn-sm">Collapse all</button>
        </div>
      </div>

      {{-- Create --}}
      <form action="{{ route('admin.categories.store') }}" method="POST" class="mb-4">
        @csrf
        <div class="row g-2 align-items-center">
          <div class="col-md-4">
            <input type="text" name="name" class="form-control shadow-sm"
                   placeholder="{{ __('messages.new_category_placeholder') }}" required>
          </div>
          <div class="col-md-4">
            <select name="parent_id" class="form-select shadow-sm">
              <option value="">{{ __('messages.no_parent') ?? 'No Parent' }}</option>
              @foreach($parentCategories as $parent)
                <option value="{{ $parent->id }}">{{ $parent->name }}</option>
              @endforeach
            </select>
          </div>
          <div class="col-auto">
            <button type="submit" class="btn btn-primary shadow-sm px-4">
              {{ __('messages.add_category') }}
            </button>
          </div>
        </div>
      </form>

      {{-- Tree --}}
      <div class="category-tree">
        <ul class="tree list-unstyled ps-0">
          @foreach($categories as $cat)
            @include('admin.category.partials.node', [
              'category' => $cat,
              'parentCategories' => $parentCategories,
              'depth' => 0
            ])
          @endforeach
        </ul>
      </div>

    </div>
  </div>
</div>
@endsection

@push('styles')
<style>
  /* ===== Palette & tokens ===== */
  .category-tree { --line:#e6e6e6; --row:#f8f9fa; --rowHover:#eef2ff; }
  .depth-0 { --accent:#3b82f6; --stripe:#e0f2fe; }   /* top level */
  .depth-1 { --accent:#8b5cf6; --stripe:#ede9fe; }   /* first child */
  .depth-2 { --accent:#10b981; --stripe:#d1fae5; }   /* deeper nodes */

  /* ===== tree connectors ===== */
  .tree li { position: relative; margin: .25rem 0 .5rem 1.25rem; }
  .tree li::before {
    content:""; position:absolute; top:.4rem; left:-.75rem;
    width:.75rem; height:1.25rem; border-left:1px solid var(--line); border-bottom:1px solid var(--line);
  }
  .tree > li::before { top:.6rem; }

  /* ===== node row ===== */
  .node-row{
    display:flex; align-items:center; gap:.5rem;
    background: var(--row); border-radius:.6rem; padding:.5rem .75rem;
    border-left: .35rem solid var(--accent, #cbd5e1);
    box-shadow: 0 0 0 1px rgba(0,0,0,.02) inset;
  }
  .node-row[data-active="0"]{ opacity:.7; }
  .node-row:hover{ background: var(--rowHover); }

  .stripe{
    width:.7rem; height:1.25rem; border-radius:.25rem; background: var(--stripe,#f1f5f9);
  }

  .caret{
    width:1.25rem;height:1.25rem;line-height:1.25rem;text-align:center;
    border:none;background:transparent;cursor:pointer;user-select:none;font-size:1rem;
  }
  .caret::before{ content:"▸"; }
  .caret.open::before{ content:"▾"; }

  .name{ font-weight:600; color:#111827; }
  .badge{ font-size:.7rem; }
  .children{ margin-left:1rem; }

  .actions{ gap:.35rem; flex-wrap:wrap; }
  .actions .btn{ padding:.2rem .55rem; border-radius:.5rem; }

  /* search highlight */
  .hit .name{ background:#fff3cd; padding:.05rem .3rem; border-radius:.25rem; }

  /* keyboard focus */
  .caret:focus-visible, .actions .btn:focus-visible { outline:2px solid #2563eb; outline-offset:2px; }
</style>
@endpush

@push('scripts')
<script>
  // toast auto-hide
  document.addEventListener('DOMContentLoaded', () => {
    const toast = document.getElementById('successToast');
    if (toast) setTimeout(() => toast.remove(), 2200);
  });

  // expand / collapse single node (click & keyboard)
  document.addEventListener('click', (e) => {
    const btn = e.target.closest('[data-toggle="children"]');
    if (!btn) return;
    const target = document.getElementById(btn.dataset.target);
    if (!target) return;
    target.classList.toggle('d-none');
    btn.classList.toggle('open');
  });
  document.addEventListener('keydown', (e) => {
    const btn = e.target.closest('[data-toggle="children"]');
    if (!btn) return;
    if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); btn.click(); }
  });

  // expand / collapse all
  document.getElementById('expandAll')?.addEventListener('click', () => {
    document.querySelectorAll('.children').forEach(ul => ul.classList.remove('d-none'));
    document.querySelectorAll('.caret').forEach(c => c.classList.add('open'));
  });
  document.getElementById('collapseAll')?.addEventListener('click', () => {
    document.querySelectorAll('.children').forEach(ul => ul.classList.add('d-none'));
    document.querySelectorAll('.caret').forEach(c => c.classList.remove('open'));
  });

  // search: highlight & auto-expand parents of hits
  const search = document.getElementById('categorySearch');
  function expandAncestors(el){
    let cur = el.parentElement;
    while(cur){
      if(cur.classList?.contains('children')) {
        cur.classList.remove('d-none');
        const caret = cur.previousElementSibling?.querySelector('.caret');
        caret?.classList.add('open');
      }
      cur = cur.parentElement;
    }
  }
  if (search) {
    search.addEventListener('input', () => {
      const q = search.value.trim().toLowerCase();
      document.querySelectorAll('.tree .tree-node').forEach(li => {
        li.classList.remove('hit');
        const name = li.dataset.name || '';
        if (q && name.includes(q)) {
          li.classList.add('hit');
          expandAncestors(li);
        }
      });
    });
  }

  // expose for inline edit in partial
  window.toggleEdit = function(id){
    document.getElementById('nameDisplay' + id).classList.add('d-none');
    document.getElementById('editForm' + id).classList.remove('d-none');
  };
</script>
@endpush
