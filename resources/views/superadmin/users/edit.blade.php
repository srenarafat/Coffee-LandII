@extends('layouts.app')


@section('content')
<div class="container my-4" style="max-width: 950px;">
    <div class="card shadow border-0 rounded-4">
        <div class="card-header bg-primary text-white rounded-top-4">
            <h5 class="mb-0 fw-bold">
                <i class="bi bi-pencil-square me-2"></i> {{ __('messages.edit_user') }}
            </h5>
        </div>


        <div class="card-body bg-light rounded-bottom-4">
            <form action="{{ route('superadmin.users.update', $user->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')


                <div class="row g-2">
                    <!-- Left Column -->
                    <div class="col-md-6">
                        <div class="form-floating">
                            <input type="text" name="name" value="{{ $user->name }}" class="form-control" placeholder="Name" required>
                            <label>{{ __('messages.name') }}</label>
                        </div>


                        <div class="form-floating mt-2">
                            <input type="email" name="email" value="{{ $user->email }}" class="form-control" placeholder="Email" required>
                            <label>{{ __('messages.email') }}</label>
                        </div>


                        <div class="form-floating mt-2">
                            <select name="role" class="form-select" required>
                                <option value="superadmin" {{ $user->role === 'superadmin' ? 'selected' : '' }}>{{ __('messages.super_admin') }}</option>
                                <option value="admin" {{ $user->role === 'admin' ? 'selected' : '' }}>Admin</option>
                                <option value="cashier" {{ $user->role === 'cashier' ? 'selected' : '' }}>{{ __('messages.cashier') }}</option>
                            </select>
                            <label>{{ __('messages.role') }}</label>
                        </div>


                        <div class="form-floating mt-2">
                            <input type="password" name="password" class="form-control" placeholder="New Password">
                            <label>{{ __('messages.new_password_optional') }}</label>
                        </div>


                        <div class="form-floating mt-2">
                            <input type="password" name="password_confirmation" class="form-control" placeholder="Confirm Password">
                            <label>{{ __('messages.confirm_new_password') }}</label>
                        </div>


                        @if ($user->profile_image)
                        <div class="mt-3">
                            <img src="{{ asset($user->profile_image) }}" alt="Profile" class="rounded-circle shadow-sm" width="56" height="56" style="object-fit: cover;">
                        </div>
                        @endif


                        <div class="mt-3">
                            <label class="form-label">{{ __('messages.profile_image') }}</label>
                            <input type="file" name="profile_image" class="form-control" accept="image/*">
                        </div>
                    </div>


                    <!-- Right Column -->
                    <div class="col-md-6">
                        <div class="form-floating">
                            <input type="text" name="phone" value="{{ $user->phone }}" class="form-control" placeholder="Phone">
                            <label>{{ __('messages.phone') }}</label>
                        </div>


                        <div class="form-floating mt-2">
                            <textarea name="address" class="form-control" placeholder="Enter address" style="height: 80px">{{ $user->address }}</textarea>
                            <label>{{ __('messages.address') }}</label>
                        </div>


                        <div class="form-floating mt-2">
                            <select name="gender" class="form-select">
                                <option value="" disabled>Select</option>
                                <option value="male" {{ $user->gender === 'male' ? 'selected' : '' }}>{{ __('messages.male') }}</option>
                                <option value="female" {{ $user->gender === 'female' ? 'selected' : '' }}>{{ __('messages.female') }}</option>
                            </select>
                            <label>{{ __('messages.gender') }}</label>
                        </div>


                        <div class="form-floating mt-2">
                            <input type="date" name="dob" value="{{ $user->dob }}" class="form-control" placeholder="Date of Birth">
                            <label>{{ __('messages.date_of_birth') }}</label>
                        </div>
                    </div>


                    <!-- Buttons -->
                    <div class="col-12 d-flex justify-content-end gap-3 mt-4">
                       <a href="{{ route('superadmin.users.index') }}" class="btn btn-danger px-4 rounded-pill text-white">
    <i class="bi bi-arrow-left"></i> {{ __('messages.back') }}
</a>
                        <button type="submit" class="btn btn-primary px-4 rounded-pill">
                            <i class="bi bi-save"></i> {{ __('messages.update_user') }}
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection



