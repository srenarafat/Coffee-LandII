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
                @if($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                <div class="row g-4">
                    <!-- Left Column -->
                    <div class="col-md-6">
                        <div class="form-floating mb-2">
                            <input type="text" name="name" value="{{ $user->name }}" class="form-control" placeholder="Name" required>
                            <label>{{ __('messages.name') }}</label>
                        </div>
                        @error('name')
                            <div class="text-danger small">{{ $message }}</div>
                        @enderror
                        <div class="form-floating mb-2">
                            <input type="email" name="email" value="{{ $user->email }}" class="form-control" placeholder="Email" required>
                            <label>{{ __('messages.email') }}</label>
                        </div>
                        @error('email')
                            <div class="text-danger small">{{ $message }}</div>
                        @enderror
                        <div class="form-floating mb-2">
                            <select name="role" class="form-select" required>
                                <option value="cashier" {{ $user->role === 'cashier' ? 'selected' : '' }}>
                                    {{ __('messages.cashier') }}
                                </option>
                            </select>
                            <label>{{ __('messages.role') }}</label>
                        </div>
                        @error('role')
                            <div class="text-danger small">{{ $message }}</div>
                        @enderror
                        <div class="form-floating mb-2 position-relative">
                            <input type="password" id="password" name="password" class="form-control" placeholder="New Password">
                            <label for="password">{{ __('messages.new_password_optional') }}</label>
                            <i class="bi bi-eye-slash position-absolute top-50 end-0 translate-middle-y me-3 toggle-password" data-target="password" style="cursor: pointer;"></i>
                        </div>
                        @error('password')
                            <div class="text-danger small">{{ $message }}</div>
                        @enderror
                        <div class="form-floating mb-2 position-relative">
                            <input type="password" id="password_confirmation" name="password_confirmation" class="form-control" placeholder="Confirm Password">
                            <label for="password_confirmation">{{ __('messages.confirm_new_password') }}</label>
                            <i class="bi bi-eye-slash position-absolute top-50 end-0 translate-middle-y me-3 toggle-password" data-target="password_confirmation" style="cursor: pointer;"></i>
                        </div>
                        @error('password_confirmation')
                            <div class="text-danger small">{{ $message }}</div>
                        @enderror
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
                        @error('profile_image')
                            <div class="text-danger small">{{ $message }}</div>
                        @enderror
                    </div>


                    <!-- Right Column -->
                    <div class="col-md-6">
                        <div class="form-floating mb-2">
                            <input type="text" name="phone" value="{{ $user->phone }}" class="form-control" placeholder="Phone">
                            <label>{{ __('messages.phone') }}</label>
                        </div>
                        @error('phone')
                            <div class="text-danger small">{{ $message }}</div>
                        @enderror
                        <div class="form-floating mb-2">
                            <textarea name="address" class="form-control" style="height: 80px" placeholder="Address">{{ $user->address }}</textarea>
                            <label>{{ __('messages.address') }}</label>
                        </div>
                        @error('address')
                            <div class="text-danger small">{{ $message }}</div>
                        @enderror
                        <div class="form-floating mb-2">
                            <select name="gender" class="form-select">
                                <option value="" disabled>Select</option>
                                <option value="male" {{ $user->gender === 'male' ? 'selected' : '' }}>{{ __('messages.male') }}</option>
                                <option value="female" {{ $user->gender === 'female' ? 'selected' : '' }}>{{ __('messages.female') }}</option>
                            </select>
                            <label>{{ __('messages.gender') }}</label>
                        </div>
                        @error('gender')
                            <div class="text-danger small">{{ $message }}</div>
                        @enderror
                        <div class="form-floating mb-2">
                            <input type="date" name="dob" value="{{ $user->dob }}" class="form-control" placeholder="Date of Birth">
                            <label>{{ __('messages.date_of_birth') }}</label>
                        </div>
                    </div>
                        @error('dob')
                            <div class="text-danger small">{{ $message }}</div>
                        @enderror
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

@push('scripts')
<script>
    document.querySelectorAll('.toggle-password').forEach(icon => {
        icon.addEventListener('click', function () {
            const target = document.getElementById(this.dataset.target);
            if (target.type === 'password') {
                target.type = 'text';
                this.classList.remove('bi-eye-slash');
                this.classList.add('bi-eye');
            } else {
                target.type = 'password';
                this.classList.remove('bi-eye');
                this.classList.add('bi-eye-slash');
            }
        });
    });
</script>
@endpush


