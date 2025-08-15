<div class="modal fade" id="tableModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ __('messages.select_table') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center">
                @for ($i = 1; $i <= config('app.table_limit'); $i++)
                    @php $selected = session('table_number') == $i; @endphp
                    <button type="button" class="btn m-1 table-btn {{ $selected ? 'btn-primary active' : 'btn-outline-primary' }}" data-number="{{ $i }}">{{ $i }}</button>
                @endfor
            </div>
        </div>
    </div>
</div>