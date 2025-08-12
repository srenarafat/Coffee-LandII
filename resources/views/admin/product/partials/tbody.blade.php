@foreach ($products as $key => $prod)
<tr>
    <td>{{ $key + 1 }}</td>
    <td>
        @if($prod->image && file_exists(public_path('storage/' . $prod->image)))
            <img src="{{ asset('storage/' . $prod->image) }}" alt="Product Image" width="80" height="80" class="rounded shadow-sm">
        @else
            <span class="text-muted">{{ __('messages.no_image') }}</span>
        @endif
    </td>
      <td class="fw-semibold text-center">{{ $prod->name }}</td>
    <td>{{ optional($setting)->currency ?? '$' }}{{ number_format($prod->price, 2) }}</td>
    <td>{{ $prod->category->name ?? 'N/A' }}</td>
    <td>
        <div class="d-flex justify-content-center gap-2">
            <a href="{{ auth()->user()->role === 'superadmin' ? route('superadmin.products.edit', $prod->id) : route('admin.products.edit', $prod->id) }}" class="btn btn-outline-primary btn-sm d-flex align-items-center gap-1 shadow-sm">
                ✏️ <span>{{ __('messages.edit') }}</span>
            </a>
            <form action="{{ auth()->user()->role === 'superadmin' ? route('superadmin.products.destroy', $prod->id) : route('admin.products.destroy', $prod->id) }}" method="POST" onsubmit="return confirm('{{ __('messages.delete_confirm') }}')" class="d-inline">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-outline-danger btn-sm d-flex align-items-center gap-1 shadow-sm">
                    🗑 <span>{{ __('messages.delete') }}</span>
                </button>
            </form>
        </div>
    </td>
</tr>
@endforeach
