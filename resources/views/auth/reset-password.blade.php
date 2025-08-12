@extends('layouts.guest')

@section('content')
<div class="min-h-screen flex items-center justify-center px-4 py-8 bg-gray-100">
  <div class="w-full max-w-md">
    <div class="bg-white shadow-xl rounded-2xl p-8">
      <div class="text-center mb-6">
        <img src="{{ asset('images/coffeeland-logo.png') }}" class="mx-auto w-16 h-16" alt="Coffee Land">
        <h1 class="mt-3 text-2xl font-extrabold tracking-wide text-brown-700">Set a new password</h1>
        <p class="text-gray-500 text-sm mt-1">Choose a strong password you haven’t used before.</p>
      </div>

      <form method="POST" action="{{ route('password.store') }}" class="space-y-4">
        @csrf
        <input type="hidden" name="token" value="{{ $token }}">

        <div>
          <label class="block text-sm font-medium text-gray-700">Email</label>
          <input
            type="email" name="email" required
            value="{{ old('email', request('email')) }}"
            class="mt-1 w-full rounded-xl border border-gray-300 focus:ring-2 focus:ring-brown-500 focus:border-brown-500 px-4 py-3">
          @error('email') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700">New password</label>
          <div class="relative mt-1">
            <input type="password" id="password" name="password" required
              class="w-full rounded-xl border border-gray-300 focus:ring-2 focus:ring-brown-500 focus:border-brown-500 px-4 py-3 pr-12"
              placeholder="••••••••">
            <button type="button" onclick="togglePassword('password', this)"
              class="absolute inset-y-0 right-3 my-auto text-gray-500 hover:text-gray-700">
              👁
            </button>
          </div>
          @error('password') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700">Confirm password</label>
          <div class="relative mt-1">
            <input type="password" id="password_confirmation" name="password_confirmation" required
              class="w-full rounded-xl border border-gray-300 focus:ring-2 focus:ring-brown-500 focus:border-brown-500 px-4 py-3 pr-12"
              placeholder="••••••••">
            <button type="button" onclick="togglePassword('password_confirmation', this)"
              class="absolute inset-y-0 right-3 my-auto text-gray-500 hover:text-gray-700">
              👁
            </button>
          </div>
        </div>

        <button
          type="submit"
          class="w-full rounded-xl border-2 border-brown-700 bg-white hover:bg-brown-700 hover:text-white text-black font-semibold py-3 transition">
          Reset Password
        </button>

        <div class="text-center text-sm text-gray-500">
          <a href="{{ route('login') }}" class="hover:underline">Back to sign in</a>
        </div>
      </form>
    </div>
    <p class="text-center text-xs text-gray-400 mt-4">© {{ date('Y') }} Coffee Land</p>
  </div>
</div>

<script>
function togglePassword(id, el) {
    let input = document.getElementById(id);
    if (input.type === "password") {
        input.type = "text";
        el.textContent = "🙈";
    } else {
        input.type = "password";
        el.textContent = "👁";
    }
}
</script>
@endsection
