@extends('layouts.guest')
@section('title','Forgot Password')
@section('content')
<div class="container py-5" style="max-width:480px">
  <h4 class="mb-2">Forgot your password?</h4>
  <p class="text-muted">Enter your email and we’ll send a reset link.</p>

  @if (session('status')) <div class="alert alert-success">{{ session('status') }}</div> @endif

  <form method="POST" action="{{ route('password.email') }}">
    @csrf
    <div class="mb-3">
      <label class="form-label">Email</label>
      <input type="email" name="email" class="form-control" required autofocus value="{{ old('email') }}">
      @error('email') <small class="text-danger">{{ $message }}</small> @enderror
    </div>
    <button class="btn btn-primary w-100">Email Password Reset Link</button>
  </form>
</div>
@endsection
