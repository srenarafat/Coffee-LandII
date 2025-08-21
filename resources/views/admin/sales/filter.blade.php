<form method="GET"
      action="{{ auth()->user()->role === 'superadmin' ? route('superadmin.sales.report') : route('admin.sales.report') }}"
      class="row g-3 align-items-end mb-4 d-print-none">

    <div class="col-md-3">
      <label class="form-label fw-semibold">👤 {{ __('messages.users') }}</label>
      <select name="user_id" onchange="this.form.submit()" class="form-select form-select-sm shadow-sm fw-bold text-dark">
        <option value="">{{ __('messages.all_users') }}</option>
        @foreach($users as $user)
          <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>
            {{ $user->name }} ({{ $user->role === 'superadmin' ? __('messages.super_admin') : $user->role }})
          </option>
        @endforeach
      </select>
    </div>

    <div class="col-md-3">
      <label class="form-label fw-semibold">📂 {{ __('messages.category') }}</label>
      <select name="category_id" onchange="this.form.submit()" class="form-select form-select-sm shadow-sm fw-bold text-dark">
        <option value="">{{ __('messages.all_categories') }}</option>
        {!! render_category_options($categories, request('category_id')) !!}
      </select>
    </div>

    <div class="col-md-3">
      <label class="form-label fw-semibold">📅 From</label>
      <input type="date" name="start_date"
              onchange="this.form.submit()"
             class="form-control form-control-sm shadow-sm fw-bold text-dark"
             value="{{ request('start_date') }}">
    </div>

    <div class="col-md-3">
      <label class="form-label fw-semibold">📅 To</label>
      <input type="date" name="end_date"
              onchange="this.form.submit()"
             class="form-control form-control-sm shadow-sm fw-bold text-dark"
             value="{{ request('end_date') }}">
    </div>

</form>
