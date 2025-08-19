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

          {{-- 👇 Hierarchical Parent selector (clean tree) --}}
          <div class="col-md-4">
            <select name="parent_id" class="form-select shadow-sm">
              <option value="">{{ __('messages.no_parent') ?? 'No Parent' }}</option>

              @php
                // Build a small tree in-view for rendering options (keeps controller unchanged)
                $rootCats = \App\Models\Category::with('childrenRecursive')
                              ->whereNull('parent_id')
                              ->orderBy('name')->get();

                $renderOptions = function($nodes, $depth = 0) use (&$renderOptions) {
                    foreach ($nodes as $n) {
                        $indent = str_repeat('— ', $depth);
                        echo '<option value="'.$n->id.'">'.$indent.e($n->name).'</option>';
                        if ($n->childrenRecursive && $n->childrenRecursive->count()) {
                            $renderOptions($n->childrenRecursive, $depth + 1);
                        }
                    }
                };
                $renderOptions($rootCats);
              @endphp
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
  /* =========================
     Color tokens (high-contrast)
     ========================= */
  :root{
    --ink:#0b1324;
    --muted:#4b5563;
    --line:#e6e8ee;

    --blue-600:#2563eb;  --blue-50:#eef2ff;
    --violet-600:#7c3aed;--violet-50:#f3e8ff;
    --emerald-600:#059669;--emerald-50:#e8fff4;

    --amber-600:#d97706; --amber-500:#f59e0b; --amber-50:#fff7e6;
    --cyan-600:#0891b2;  --cyan-50:#e6fbff;
    --rose-600:#e11d48;  --rose-50:#fff1f2;
    --gray-row:#f8fafc;  --gray-hover:#eef2ff;
  }

  /* Depth accents */
  .depth-0 { --accent: var(--blue-600);   --stripe: var(--blue-50); }
  .depth-1 { --accent: var(--violet-600); --stripe: var(--violet-50); }
  .depth-2 { --accent: var(--emerald-600);--stripe: var(--emerald-50); }

  /* =========================
     Tree connectors
     ========================= */
  .tree li{ position:relative; margin:.25rem 0 .6rem 1.25rem; }
  .tree li::before{
    content:""; position:absolute; top:.55rem; left:-.75rem;
    width:.75rem; height:1.25rem;
    border-left:1px solid var(--line); border-bottom:1px solid var(--line);
  }
  .tree > li::before{ top:.75rem; }

  /* =========================
     Node row
     ========================= */
  .node-row{
    display:flex; align-items:center; gap:.55rem;
    background: var(--gray-row);
    border-radius:.65rem; padding:.55rem .75rem;
    border-left:.38rem solid var(--accent, #a3b6f7);
    box-shadow:0 0 0 1px rgba(0,0,0,.03) inset;
    transition: background .15s ease, box-shadow .15s ease;
  }
  .node-row:hover{ background: var(--gray-hover); }
  .node-row[data-active="0"]{ opacity:.8; filter:saturate(.85); }

  .stripe{
    width:.7rem; height:1.2rem; border-radius:.25rem;
    background: var(--stripe);
    box-shadow: inset 0 0 0 1px rgba(0,0,0,.05);
  }

  .caret{
    width:1.25rem; height:1.25rem; line-height:1.2rem;
    text-align:center; border:none; background:transparent; cursor:pointer;
    user-select:none; font-size:1rem; color:var(--muted);
  }
  .caret::before{ content:"▸"; }
  .caret.open::before{ content:"▾"; }
  .caret:focus-visible{ outline:2px solid var(--blue-600); outline-offset:2px; border-radius:.25rem; }

  .name{ font-weight:700; color:var(--ink); letter-spacing:.2px; }
  .badge{ font-size:.72rem; border-radius:.5rem; padding:.12rem .45rem; }
  .badge-active{ background:#d1fae5; color:#065f46; border:1px solid #a7f3d0; }

  .children{ margin-left:1rem; }

  .actions{ gap:.4rem; flex-wrap:wrap; margin-left:auto; }
  .actions .btn{ padding:.25rem .6rem; border-radius:.55rem; font-weight:600; }

  /* Clearer action colors */
  .actions .btn-warning, .btn-deactivate{
    background:var(--amber-500); border-color:var(--amber-500); color:#111;
  }
  .actions .btn-warning:hover, .btn-deactivate:hover{
    background:var(--amber-600); border-color:var(--amber-600); color:#fff;
  }
  .actions .btn-info, .btn-edit{
    background:#3b82f6; border-color:#3b82f6; color:#fff;
  }
  .actions .btn-info:hover, .btn-edit:hover{
    background:#1d4ed8; border-color:#1d4ed8; color:#fff;
  }
  .actions .btn-danger, .btn-delete{
    background:var(--rose-600); border-color:var(--rose-600); color:#fff;
  }
  .actions .btn-danger:hover, .btn-delete:hover{
    box-shadow:0 0 0 3px var(--rose-50) inset;
  }

  /* Dropdown readability */
  select.form-select option[disabled]{ color:#9ca3af; font-style:italic; }

  /* Search highlight */
  .hit .name{ background:var(--amber-50); padding:.05rem .3rem; border-radius:.25rem; }

  /* Small screen tweaks */
  @media (max-width: 576px){
    .actions .btn{ padding:.25rem .5rem; font-size:.75rem; }
    .name{ font-size:.95rem; }
  }
</style>
@endpush

@push('scripts')
<script>
  // toast auto-hide
  document.addEventListener('DOMContentLoaded', () => {
    const toast = document.getElementById('successToast');
    if (toast) setTimeout(() => toast.remove(), 2200);
  });

  // expand/collapse single node
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

  // expand/collapse all
  document.getElementById('expandAll')?.addEventListener('click', () => {
    document.querySelectorAll('.children').forEach(ul => ul.classList.remove('d-none'));
    document.querySelectorAll('.caret').forEach(c => c.classList.add('open'));
  });
  document.getElementById('collapseAll')?.addEventListener('click', () => {
    document.querySelectorAll('.children').forEach(ul => ul.classList.add('d-none'));
    document.querySelectorAll('.caret').forEach(c => c.classList.remove('open'));
  });

  // search highlight + auto-expand
  const search = document.getElementById('categorySearch');
  function expandAncestors(el){
    let cur = el.parentElement;
    while(cur){
      if(cur.classList?.contains('children')){
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
