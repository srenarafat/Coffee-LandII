@extends('layouts.guest')
@section('content')
<div class="mx-auto w-full max-w-md bg-white rounded-xl shadow p-6">
  <h2 class="text-xl font-semibold mb-4">Set a new password</h2>

  <form method="POST" action="{{ route('password.store') }}" class="space-y-3">
    @csrf
    <input type="hidden" name="token" value="{{ $token }}">
    <div>
      <label class="block text-sm mb-1">Email</label>
      <input name="email" type="email" class="w-full border rounded px-3 py-2"
             required value="{{ old('email', request('email')) }}">
      @error('email') <div class="text-red-600 text-sm mt-1">{{ $message }}</div> @enderror
    </div>
    <div>
      <label class="block text-sm mb-1">Password</label>
      <input name="password" type="password" class="w-full border rounded px-3 py-2" required>
      @error('password') <div class="text-red-600 text-sm mt-1">{{ $message }}</div> @enderror
    </div>
    <div>
      <label class="block text-sm mb-1">Confirm Password</label>
      <input name="password_confirmation" type="password" class="w-full border rounded px-3 py-2" required>
    </div>
    <button class="w-full bg-green-600 hover:bg-green-700 text-white rounded px-4 py-2">
      Reset Password
    </button>
</form>

</div>
@endsection
