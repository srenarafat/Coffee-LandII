@php
  $hasChildren = $category->childrenRecursive->count() > 0;
  $childrenId  = 'children-'.$category->id;
  $depthClass  = 'depth-'.min((int)($depth ?? 0), 2);
@endphp

<li class="tree-node {{ $depthClass }}" data-name="{{ strtolower($category->name) }}" data-depth="{{ $depth ?? 0 }}">
  <div id="nameDisplay{{ $category->id }}" class="node-row" data-active="{{ (int)$category->is_active }}">
    {{-- depth stripe --}}
    <span class="stripe" aria-hidden="true"></span>

    {{-- caret / bullet --}}
    @if($hasChildren)
      <button type="button"
              class="caret open"
              data-toggle="children"
              data-target="{{ $childrenId }}"
              aria-label="toggle children"
              tabindex="0"></button>
    @else
      <span class="caret" style="opacity:.35;cursor:default" aria-hidden="true">•</span>
    @endif

    {{-- name + status --}}
    <span class="name">{{ $category->name }}</span>
    @if($category->is_active)
      <span class="badge bg-success">{{ __('messages.active') }}</span>
    @else
      <span class="badge bg-secondary">{{ __('messages.inactive') }}</span>
    @endif

    {{-- actions --}}
    <div class="ms-auto actions d-flex">
      @if($category->is_active)
        <form action="{{ route('admin.categories.deactivate', $category->id) }}" method="POST" class="d-inline">
          @csrf @method('PATCH')
          <button class="btn btn-warning btn-sm text-white">{{ __('messages.deactivate') }}</button>
        </form>
      @else
        <form action="{{ route('admin.categories.activate', $category->id) }}" method="POST" class="d-inline">
          @csrf @method('PATCH')
          <button class="btn btn-success btn-sm text-white">{{ __('messages.activate') }}</button>
        </form>
      @endif

      <button onclick="toggleEdit({{ $category->id }})"
              class="btn btn-info btn-sm text-white">{{ __('messages.edit') }}</button>

      <form action="{{ route('admin.categories.destroy', $category->id) }}" method="POST"
            onsubmit="return confirm('{{ __('messages.delete_category_confirm') }}')" class="d-inline">
        @csrf @method('DELETE')
        <button class="btn btn-danger btn-sm text-white">{{ __('messages.delete') }}</button>
      </form>
    </div>
  </div>

  {{-- Inline edit --}}
  <form id="editForm{{ $category->id }}" action="{{ route('admin.categories.update', $category->id) }}"
        method="POST" class="d-none mt-2">
    @csrf @method('PUT')
    <div class="row g-2 align-items-center">
      <div class="col-md-4">
        <input type="text" name="name" value="{{ $category->name }}" class="form-control" required>
      </div>
      <div class="col-md-4">
        <select name="parent_id" class="form-select">
          <option value="">{{ __('messages.no_parent') ?? 'No Parent' }}</option>
          @php
            $collectIds = function ($cat) use (&$collectIds) {
                $ids = [];
                foreach ($cat->childrenRecursive as $child) {
                    $ids[] = $child->id;
                    $ids = array_merge($ids, $collectIds($child));
                }
                return $ids;
            };
            $descendantIds = $collectIds($category);
          @endphp
          @foreach($parentCategories as $parent)
            @if($parent->id !== $category->id && !in_array($parent->id, $descendantIds))
              <option value="{{ $parent->id }}" {{ $category->parent_id == $parent->id ? 'selected' : '' }}>
                {{ $parent->name }}
              </option>
            @endif
          @endforeach
        </select>
      </div>
      <div class="col-auto">
        <button class="btn btn-primary btn-sm">{{ __('messages.save') }}</button>
      </div>
    </div>
  </form>

  {{-- Children --}}
  @if($hasChildren)
    <ul id="{{ $childrenId }}" class="children list-unstyled mt-2">
      @foreach($category->childrenRecursive as $child)
        @include('admin.category.partials.node', [
          'category' => $child,
          'parentCategories' => $parentCategories,
          'depth' => ($depth ?? 0) + 1
        ])
      @endforeach
    </ul>
  @endif
</li>
