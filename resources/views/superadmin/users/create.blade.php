@extends('layouts.app')


@section('content')
<div class="container my-4">
    <div class="card shadow border-0 rounded-4">
        <div class="card-header bg-primary text-white rounded-top-4">
            <h5 class="mb-0 fw-bold"><i class="bi bi-person-plus-fill me-2"></i> {{ __('messages.create_user') }}</h5>
        </div>


        <div class="card-body bg-light rounded-bottom-4">
            <form action="{{ route('superadmin.users.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="row g-4">
                    <!-- Left Column -->
                    <div class="col-md-6">
                        <div class="form-floating mb-3">
                            <input type="text" name="name" class="form-control" placeholder="Name" required>
                            <label>{{ __('messages.name') }}</label>
                        </div>


                        <div class="form-floating mb-3">
                            <input type="email" name="email" class="form-control" placeholder="Email" required>
                            <label>{{ __('messages.email') }}</label>
                        </div>


                        <div class="form-floating mb-3">
                            <select name="role" class="form-select" required>
                                <option value="superadmin">{{ __('messages.super_admin') }}</option>
                                <option value="admin">Admin</option>
                                <option value="cashier">{{ __('messages.cashier') }}</option>
                            </select>
                            <label>{{ __('messages.role') }}</label>
                        </div>


                        <div class="form-floating mb-3">
                            <input type="password" name="password" class="form-control" placeholder="Password" required>
                            <label>{{ __('messages.password') }}</label>
                        </div>


                        <div class="form-floating mb-3">
                            <input type="password" name="password_confirmation" class="form-control" placeholder="Confirm Password" required>
                            <label>{{ __('messages.confirm_password') }}</label>
                        </div>


                        <div class="mb-3">
                            <label class="form-label">{{ __('messages.profile_image') }}</label>
                            <input type="file" name="profile_image" class="form-control" accept="image/*">
                        </div>
                    </div>


                    <!-- Right Column -->
                    <div class="col-md-6">
                        <div class="form-floating mb-3">
                            <input type="text" name="phone" value="{{ old('phone') }}" class="form-control" placeholder="Phone">
                            <label>{{ __('messages.phone') }}</label>
                        </div>


                        <div class="mb-3">
                            <label class="form-label">{{ __('messages.address') }}</label>
                            <textarea name="address" class="form-control" rows="4" placeholder="Enter address">{{ old('address') }}</textarea>
                        </div>


                        <div class="form-floating mb-3">
                            <select name="gender" class="form-select">
                                <option value="" disabled selected>Select</option>
                                <option value="male">{{ __('messages.male') }}</option>
                                <option value="female">{{ __('messages.female') }}</option>
                            </select>
                            <label>{{ __('messages.gender') }}</label>
                        </div>


                        <div class="form-floating mb-3">
                            <input type="date" name="dob" value="{{ old('dob') }}" class="form-control" placeholder="Date of Birth">
                            <label>{{ __('messages.date_of_birth') }}</label>
                        </div>
                    </div>


                    <!-- Footer Buttons -->
                    <div class="col-12 d-flex justify-content-end gap-3">
                       <a href="{{ route('superadmin.users.index') }}" class="btn btn-danger px-4 rounded-pill text-white">
    <i class="bi bi-arrow-left"></i> {{ __('messages.back') }}
</a>
                        <button type="submit" class="btn btn-primary px-4 rounded-pill">
                            <i class="bi bi-plus-circle"></i> {{ __('messages.create_user') }}
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection



