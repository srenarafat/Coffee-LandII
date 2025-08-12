@extends('layouts.guest')
@section('title','Reset Password')
@section('content')
<div class="container py-5" style="max-width:480px">
  <h4 class="mb-3">Set a new password</h4>
  <form method="POST" action="{{ route('password.update') }}">
    @csrf
    <input type="hidden" name="token" value="{{ $token }}">
    <div class="mb-3">
      <label class="form-label">Email</label>
      <input type="email" name="email" class="form-control" required value="{{ request('email') }}">
      @error('email') <small class="text-danger">{{ $message }}</small> @enderror
    </div>
    <div class="mb-3">
      <label class="form-label">Password</label>
      <input type="password" name="password" class="form-control" required>
      @error('password') <small class="text-danger">{{ $message }}</small> @enderror
    </div>
    <div class="mb-3">
      <label class="form-label">Confirm Password</label>
      <input type="password" name="password_confirmation" class="form-control" required>
    </div>
    <button class="btn btn-success w-100">Reset Password</button>
  </form>
</div>
@endsection
