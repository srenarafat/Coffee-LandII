@extends('layouts.guest')

@section('content')
<div class="min-h-screen flex items-center justify-center px-4 py-8 bg-gray-100">
  <div class="w-full max-w-md">
    <div class="bg-white shadow-xl rounded-2xl p-8">
      <div class="text-center mb-6">
        <img src="{{ asset('images/coffeeland-logo.png') }}" class="mx-auto w-16 h-16" alt="Coffee Land">
        <h1 class="mt-3 text-2xl font-extrabold tracking-wide text-brown-700">COFFEE LAND</h1>
        <p class="text-gray-500 text-sm mt-1">We’ll email you a secure password reset link.</p>
      </div>

      @if (session('status'))
        <div class="mb-4 text-sm text-green-700 bg-green-100 border border-green-200 rounded-lg px-3 py-2">
          {{ session('status') }}
        </div>
      @endif

      <form method="POST" action="{{ route('password.email') }}" class="space-y-4">
        @csrf

        <div>
          <label class="block text-sm font-medium text-gray-700">Email</label>
          <input
            type="email" name="email" value="{{ old('email') }}" required
            class="w-full rounded-xl border border-gray-300 focus:ring-2 focus:ring-brown-500 focus:border-brown-500 px-4 py-3"
            placeholder="you@example.com">
          @error('email') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>

        <button
          type="submit"
          class="w-full rounded-xl border-2 border-brown-700 bg-white hover:bg-brown-700 hover:text-white text-black font-semibold py-3 transition">
          Send Reset Link
        </button>

        <div class="text-center text-sm text-gray-500">
          <a href="{{ route('login') }}" class="hover:underline">Back to sign in</a>
        </div>
      </form>
    </div>
    <p class="text-center text-xs text-gray-400 mt-4">© {{ date('Y') }} Coffee Land</p>
  </div>
</div>
@endsection
