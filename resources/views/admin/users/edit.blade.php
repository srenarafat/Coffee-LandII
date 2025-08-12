@extends('layouts.app')


@section('content')
<div class="container my-4" style="max-width: 950px;">
    <div class="card shadow border-0 rounded-4">
        <!-- Blue Header -->
        <div class="card-header bg-primary text-white rounded-top-4">
            <h5 class="mb-0 fw-bold">
                <i class="bi bi-pencil-square me-2"></i> {{ __('messages.edit_user') }}
            </h5>
        </div>


        <div class="card-body bg-white rounded-bottom-4">
            <form action="{{ route('admin.users.update', $user->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')


                <div class="row g-4">
                    <!-- Left Column -->
                    <div class="col-md-6">
                        <div class="form-floating mb-2">
                            <input type="text" name="name" value="{{ $user->name }}" class="form-control" placeholder="Name" required>
                            <label>{{ __('messages.name') }}</label>
                        </div>


                        <div class="form-floating mb-2">
                            <input type="email" name="email" value="{{ $user->email }}" class="form-control" placeholder="Email" required>
                            <label>{{ __('messages.email') }}</label>
                        </div>


                        <div class="form-floating mb-2">
                            <select name="role" class="form-select" required>
                                <option value="cashier" {{ $user->role === 'cashier' ? 'selected' : '' }}>
                                    {{ __('messages.cashier') }}
                                </option>
                            </select>
                            <label>{{ __('messages.role') }}</label>
                        </div>


                        <div class="form-floating mb-2">
                            <input type="password" name="password" class="form-control" placeholder="New Password">
                            <label>{{ __('messages.new_password_optional') }}</label>
                        </div>


                        <div class="form-floating mb-2">
                            <input type="password" name="password_confirmation" class="form-control" placeholder="Confirm Password">
                            <label>{{ __('messages.confirm_new_password') }}</label>
                        </div>


                        @if ($user->profile_image)
                        <div class="mb-3">
                            
                            <img src="{{ asset($user->profile_image) }}" alt="Profile"
                                 class="rounded-circle shadow-sm mb-2" width="64" height="64"
                                 style="object-fit: cover;">
                        </div>
                        @endif


                        <div class="mb-3">
                            <label class="form-label">{{ __('messages.profile_image') }}</label>
                            <input type="file" name="profile_image" class="form-control" accept="image/*">
                        </div>
                    </div>


                    <!-- Right Column -->
                    <div class="col-md-6">
                        <div class="form-floating mb-2">
                            <input type="text" name="phone" value="{{ $user->phone }}" class="form-control" placeholder="Phone">
                            <label>{{ __('messages.phone') }}</label>
                        </div>


                        <div class="form-floating mb-2">
                            <textarea name="address" class="form-control" style="height: 80px" placeholder="Address">{{ $user->address }}</textarea>
                            <label>{{ __('messages.address') }}</label>
                        </div>


                        <div class="form-floating mb-2">
                            <select name="gender" class="form-select">
                                <option value="" disabled>Select</option>
                                <option value="male" {{ $user->gender === 'male' ? 'selected' : '' }}>{{ __('messages.male') }}</option>
                                <option value="female" {{ $user->gender === 'female' ? 'selected' : '' }}>{{ __('messages.female') }}</option>
                            </select>
                            <label>{{ __('messages.gender') }}</label>
                        </div>


                        <div class="form-floating mb-2">
                            <input type="date" name="dob" value="{{ $user->dob }}" class="form-control" placeholder="Date of Birth">
                            <label>{{ __('messages.date_of_birth') }}</label>
                        </div>
                    </div>


                    <!-- Buttons -->
                    <div class="col-12 d-flex justify-content-end gap-3 mt-2">
                        <a href="{{ route('admin.users.index') }}" class="btn btn-danger px-4 rounded-pill">
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



