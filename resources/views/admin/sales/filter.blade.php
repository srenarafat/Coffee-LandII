<form method="GET"
      action="{{ auth()->user()->role === 'superadmin' ? route('superadmin.sales.report') : route('admin.sales.report') }}"
      class="mb-4 d-print-none">
  <div class="row g-3 align-items-end">

    <div class="col-md-3">
      <label class="form-label fw-semibold">👤 {{ __('messages.users') }}</label>
      <select name="user_id" class="form-select form-select-sm shadow-sm fw-bold text-dark">
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
      <select name="category_id" class="form-select form-select-sm shadow-sm fw-bold text-dark">
        <option value="">{{ __('messages.all_categories') }}</option>
        @foreach($categories as $category)
          <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>
            {{ $category->name }}
          </option>
        @endforeach
      </select>
    </div>

    <div class="col-md-2">
      <label class="form-label fw-semibold">📅 From</label>
      <input type="date" name="start_date"
             class="form-control form-control-sm shadow-sm fw-bold text-dark"
             value="{{ request('start_date') }}">
    </div>

    <div class="col-md-2">
      <label class="form-label fw-semibold">📅 To</label>
      <input type="date" name="end_date"
             class="form-control form-control-sm shadow-sm fw-bold text-dark"
             value="{{ request('end_date') }}">
    </div>

    <div class="col-auto d-grid">
    <button type="submit"
        class="btn btn-primary shadow-sm d-flex align-items-center justify-content-center gap-2 px-5"
        style="min-width: 210px;">
        🔍 {{ __('messages.filter') }}
    </button>
</div>


  </div>
</form>
