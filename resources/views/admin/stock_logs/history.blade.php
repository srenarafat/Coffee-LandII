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
                <tr>
                    <td colspan="5" class="text-center">{{ __('messages.no_stock_logs') }}</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>