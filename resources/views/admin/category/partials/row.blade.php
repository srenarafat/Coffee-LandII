<tr>
    <td>{{ $index }}</td>
    <td class="text-start">
        <div id="nameDisplay{{ $category->id }}">
            <span class="fw-bold text-dark">{!! str_repeat('&mdash; ', $depth) !!}{{ $category->name }}</span>
        </div>
        <form id="editForm{{ $category->id }}"
              action="{{ route('admin.categories.update', $category->id) }}"
              method="POST"
              class="d-none mt-2 d-flex">
            @csrf @method('PUT')
            <input type="text" name="name" value="{{ $category->name }}"
                   class="form-control me-2" required>
            <button class="btn btn-outline-primary btn-sm btn-block d-flex align-items-center justify-content-center gap-1">
              ✅ <span>{{ __('messages.save') }}</span>
            </button>
        </form>
    </td>
    <td>
        <div class="d-flex justify-content-center gap-2">
            <button onclick="toggleEdit({{ $category->id }})"
                    class="btn btn-outline-primary btn-sm">
                 {{ __('messages.edit') }}
            </button>
            <form action="{{ route('admin.categories.destroy', $category->id) }}"
                  method="POST"
                  onsubmit="return confirm('{{ __('messages.delete_category_confirm') }}')">
                @csrf @method('DELETE')
                <button class="btn btn-outline-danger btn-sm"> {{ __('messages.delete') }}</button>
            </form>
        </div>
    </td>
</tr>
