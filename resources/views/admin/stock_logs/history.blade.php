@php
  $isSuper = auth()->user()->role === 'superadmin';
  // The controller should pass $product OR $ingredient into this partial.
  $params = request()->all();
  if (isset($product))    { $params['product_id']    = $product->id; }
  if (isset($ingredient)) { $params['ingredient_id'] = $ingredient->id; }
@endphp

{{-- Export / Print toolbar --}}
<div class="d-flex justify-content-end gap-2 mb-3">
  @if(isset($product))
    <a class="btn btn-outline-success btn-sm"
       href="{{ $isSuper ? route('superadmin.stock-logs.export', $params)
                         : route('admin.stock-logs.export',       $params) }}">
      ⬇️ {{ __('messages.export_csv') }}
    </a>
    <a class="btn btn-outline-primary btn-sm"
       href="{{ $isSuper ? route('superadmin.stock-logs.pdf', $params)
                         : route('admin.stock-logs.pdf',       $params) }}">
      🖨️ {{ __('messages.print') }}
    </a>
  @elseif(isset($ingredient))
    <a class="btn btn-outline-success btn-sm"
       href="{{ $isSuper ? route('superadmin.ingredient-stock.export', $params)
                         : route('admin.ingredient-stock.export',       $params) }}">
      ⬇️ {{ __('messages.export_csv') }}
    </a>
    <a class="btn btn-outline-primary btn-sm"
       href="{{ $isSuper ? route('superadmin.ingredient-stock.pdf', $params)
                         : route('admin.ingredient-stock.pdf',       $params) }}">
      🖨️ {{ __('messages.print') }}
    </a>
  @endif
</div>

<div class="table-responsive">
  <table class="table table-bordered table-striped table-hover align-middle mb-0">
    <thead class="bg-light">
      <tr>
        <th class="text-center">{{ __('messages.type') }}</th>
        <th class="text-center">{{ __('messages.qty') }}</th>
        <th class="text-center">{{ __('messages.Note') }}</th>
        <th class="text-center">{{ __('messages.users') }}</th>
        <th class="text-center">{{ __('messages.date') }}</th>
      </tr>
    </thead>
    <tbody>
      @forelse($logs as $log)
        <tr>
          <td class="text-center">
            <span class="badge badge-type fw-normal {{ strtolower($log->type) === 'in' ? 'bg-success' : 'bg-danger' }}">
              {{ strtoupper($log->type) }}
            </span>
          </td>
          <td class="text-center">{{ $log->quantity }}</td>
          <td class="text-center">{{ $log->note }}</td>
          <td class="text-center">{{ $log->user->name }}</td>
          <td class="text-center">{{ $log->created_at->format('d/m/Y H:i') }}</td>
        </tr>
      @empty
        <tr><td colspan="5" class="text-center">{{ __('messages.no_stock_logs') }}</td></tr>
      @endforelse
    </tbody>
  </table>
</div>
