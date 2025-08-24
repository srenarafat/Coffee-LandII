<div class="modal fade" id="commentModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="commentForm" action="{{ route($routePrefix . '.pos.note') }}" method="POST">
                @csrf
                <input type="hidden" name="cart_key" id="commentCartKey">
                <div class="modal-header">
                    <h5 class="modal-title">{{ __('messages.Note') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <ul id="currentNotes" class="list-group mb-3"></ul>
                    <input type="text" name="note" id="commentInput" class="form-control mb-2" list="commentList">
                    <datalist id="commentList">
                        @foreach($comments as $c)
                            <option value="{{ $c->text }}"></option>
                        @endforeach
                    </datalist>
                    <button type="button" class="btn btn-sm btn-secondary" id="saveComment">{{ __('messages.save_to_list') }}</button>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">{{ __('messages.apply_to_item') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>