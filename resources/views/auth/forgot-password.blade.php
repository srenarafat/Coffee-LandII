@extends('layouts.guest')
@section('content')
<div class="mx-auto w-full max-w-md bg-white rounded-xl shadow p-6">
  <h2 class="text-xl font-semibold mb-1">Forgot your password?</h2>
  <p class="text-sm text-gray-600 mb-4">Enter your email and we’ll send a reset link.</p>

  @if (session('status'))
    <div class="mb-3 text-green-700 bg-green-100 border border-green-200 rounded p-2">
      {{ session('status') }}
    </div>
  @endif

  <form method="POST" action="{{ route('password.email') }}" class="space-y-3">
    @csrf
    <div>
      <label class="block text-sm mb-1">Email</label>
      <input name="email" type="email" class="w-full border rounded px-3 py-2" required autofocus value="{{ old('email') }}">
      @error('email') <div class="text-red-600 text-sm mt-1">{{ $message }}</div> @enderror
    </div>
    <button class="w-full bg-blue-600 hover:bg-blue-700 text-white rounded px-4 py-2">
      Email Password Reset Link
    </button>
  </form>
</div>
@endsection
