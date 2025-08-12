<form method="GET" class="row g-3 align-items-end mb-4 d-print-none">
    <div class="col-md-3">
        <label class="form-label fw-semibold">📅 From</label>
        <input type="date" name="from" value="{{ request('from') }}" class="form-control shadow-sm fw-bold text-dark">
    </div>
    <div class="col-md-3">
        <label class="form-label fw-semibold">📅 To</label>
        <input type="date" name="to" value="{{ request('to') }}" class="form-control shadow-sm fw-bold text-dark">
    </div>
    <div class="col-md-3">
        <label class="form-label fw-semibold">📂 Category</label>
        <select name="category_id" class="form-select shadow-sm fw-bold text-dark">
            <option value="">{{ __('messages.all_categories') }}</option>
            @foreach ($categories as $category)
                <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>
                    {{ $category->name }}
                </option>
            @endforeach
        </select>
    </div>
    <div class="col-md-3 d-grid">
        <button type="submit" class="btn btn-primary shadow-sm d-flex align-items-center justify-content-center gap-2">
            🔍 {{ __('messages.filter') }}
        </button>
    </div>
</form>