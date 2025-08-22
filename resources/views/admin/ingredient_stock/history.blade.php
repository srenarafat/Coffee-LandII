<div class="table-responsive">
    <table class="table table-bordered table-striped table-hover align-middle mb-0">
        <thead class="table-light">
            <tr>
                <th class="text-center">{{ __('messages.type') }}</th>
                <th class="text-center">{{ __('messages.qty') }}</th>
                <th class="text-center">Unit</th>
                <th class="text-center">{{ __('messages.current_stock') }}</th>
                <th class="text-center">{{ __('messages.Note') }}</th>
                <th class="text-center">{{ __('messages.users') }}</th>
                <th class="text-center">{{ __('messages.date') }}</th>
            </tr>
        </thead>
        <tbody>
            @php $running = null; @endphp
            @forelse($logs as $log)
                @php
                    $qty  = rtrim(rtrim(number_format($log->quantity, 2, '.', ''), '0'), '.');
                    $unit = $log->unit ?? $ingredient->unit;
                    if (!is_null($log->stock_after)) {
                        $after = (float) $log->stock_after;
                    } else {
                        if ($running === null) { $running = (float) $ingredient->stock; }
                        $after = $running;
                        $delta = ($log->type === 'in') ? (float)$log->quantity : -(float)$log->quantity;
                        $running -= $delta;
                    }
                    $afterFmt = rtrim(rtrim(number_format($after, 2, '.', ''), '0'), '.');
                @endphp
                <tr>
                    <td class="text-center">
                        <span class="badge fw-normal badge-type {{ strtolower($log->type) === 'in' ? 'bg-success' : 'bg-danger' }}">
                            {{ strtoupper($log->type) }}
                        </span>
                    </td>
                    <td class="text-center">{{ $qty }}</td>
                    <td class="text-center">{{ $unit }}</td>
                    <td class="text-center">{{ $afterFmt }} {{ $unit }}</td>
                    <td class="text-center">{{ $log->note }}</td>
                    <td class="text-center">{{ $log->user->name }}</td>
                    <td class="text-center">{{ $log->created_at->format('d/m/Y H:i') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="text-center">{{ __('messages.no_stock_logs') }}</td>
                </tr>
            @endforelse
        </tbody>
    </table>
    <div class="mt-2 d-flex justify-content-center">
        {{ $logs->links('pagination::bootstrap-5') }}
    </div>
</div>