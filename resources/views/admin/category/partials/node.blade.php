<th>{{ __('messages.status') }}</th>
<th style="width: 300px;">{{ __('messages.action') }}</th>
<td>
                                @if($cat->is_active)
                                    <span class="badge bg-success">{{ __('messages.active') }}</span>
                                @else
                                    <span class="badge bg-secondary">{{ __('messages.inactive') }}</span>
                                @endif
                            </td>
                            @if($cat->is_active)
                                        <form action="{{ route('admin.categories.deactivate', $cat->id) }}" method="POST">
                                            @csrf @method('PATCH')
                                            <button class="btn btn-outline-warning btn-sm">{{ __('messages.deactivate') }}</button>
                                        </form>
                                    @else
                                        <form action="{{ route('admin.categories.activate', $cat->id) }}" method="POST">
                                            @csrf @method('PATCH')
                                            <button class="btn btn-outline-success btn-sm">{{ __('messages.activate') }}</button>
                                        </form>
                                    @endif
<li class="mb-2">
    <div id="nameDisplay{{ $category->id }}" class="d-flex justify-content-between align-items-center">
        <span class="fw-bold text-dark">{{ $category->name }}</span>
        <div class="d-flex gap-2">
            <button onclick="toggleEdit({{ $category->id }})" class="btn btn-outline-primary btn-sm">{{ __('messages.edit') }}</button>
            <form action="{{ route('admin.categories.destroy', $category->id) }}" method="POST" onsubmit="return confirm('{{ __('messages.delete_category_confirm') }}')">
                @csrf
                @csrf @method('DELETE')
                                      <button class="btn btn-outline-danger btn-sm"> {{ __('messages.delete') }}</button>
            </form>
        </div>
    </div>
    <form id="editForm{{ $category->id }}" action="{{ route('admin.categories.update', $category->id) }}" method="POST" class="d-none mt-2">
        @csrf
        @method('PUT')
        <div class="row g-2 align-items-center">
            <div class="col-md-4">
                <input type="text" name="name" value="{{ $category->name }}" class="form-control" required>
            </div>
            <div class="col-md-4">
                <select name="parent_id" class="form-select">
                    <option value="">{{ __('messages.no_parent') ?? 'No Parent' }}</option>
                    @foreach($parentCategories as $parent)
                        @if($parent->id !== $category->id)
                            <option value="{{ $parent->id }}" {{ $category->parent_id == $parent->id ? 'selected' : '' }}>{{ $parent->name }}</option>
                        @endif
                    @endforeach
                </select>
            </div>
            <div class="col-auto">
                <button class="btn btn-outline-primary btn-sm">{{ __('messages.save') }}</button>
            </div>
        </div>
    </form>
    @if($category->children->count())
        <ul class="mt-2 ms-4 list-unstyled">
            @foreach($category->children as $child)
                @include('admin.category.partials.node', ['category' => $child, 'parentCategories' => $parentCategories])
            @endforeach
        </ul>
    @endif
</li>
