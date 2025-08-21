<form method="GET" class="row g-3 align-items-end mb-4 d-print-none">
    <div class="col-md-4">
        <label class="form-label fw-semibold">📅 From</label>
        <input type="date" name="from" value="{{ request('from') }}" class="form-control shadow-sm fw-bold text-dark" onchange="this.form.submit()">
    </div>
    <div class="col-md-4">
        <label class="form-label fw-semibold">📅 To</label>
        <input type="date" name="to" value="{{ request('to') }}" class="form-control shadow-sm fw-bold text-dark" onchange="this.form.submit()">
    </div>
    <div class="col-md-4">
        <label class="form-label fw-semibold">📂 Category</label>
        <select name="category_id" class="form-select shadow-sm fw-bold text-dark" onchange="this.form.submit()">
            <option value="">{{ __('messages.all_categories') }}</option>
            {!! render_category_options($categories, request('category_id')) !!}
        </select>
    </div>
</form>