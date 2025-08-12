@extends('layouts.guest')

@section('content')
<div class="min-h-screen bg-linear-image flex items-center justify-center px-4 py-6">
    <div class="w-full max-w-3xl h-[500px] grid grid-cols-1 md:grid-cols-2 bg-white rounded-2xl overflow-hidden shadow-lg border border-white">

        {{-- LEFT: Login Form --}}
        <div class="text-black p-6 flex flex-col justify-center bg-white">
            <div class="text-center mb-5">
                <img src="{{ asset('images/coffeeland-logo.png') }}" class="mx-auto mb-3 w-32" alt="Coffee Land">
                <h1 class="text-2xl font-extrabold tracking-wider uppercase text-[#4f2e18] drop-shadow-sm">
                    {{ optional($setting)->shop_name ?? 'Coffee Land' }}
                </h1>
            </div>

            <form method="POST" action="{{ route('login') }}" class="space-y-4">
    @csrf

    {{-- (Optional) generic auth error from other places --}}
    @if (session('status'))
        <div class="text-sm text-red-600">{{ session('status') }}</div>
    @endif

    <div>
        <label for="email" class="block mb-1 text-sm text-black">Email</label>
        <input
            id="email"
            name="email"
            type="email"
            required
            autofocus
            value="{{ old('email') }}"
            autocomplete="username"
            class="w-full px-4 py-2 rounded-md bg-gray-100 text-black placeholder-gray-500 border border-gray-300 focus:outline-none focus:ring-2 focus:ring-[#5f2e18]"
            placeholder="you@example.com"
        />
        @error('email')
            <div class="text-sm text-red-600 mt-1">{{ $message }}</div>
        @enderror
    </div>

    <div>
        <label for="password" class="block mb-1 text-sm text-black">Password</label>
        <div class="relative">
            <input
                id="password"
                name="password"
                type="password"
                required
                autocomplete="current-password"
                class="w-full px-4 py-2 pr-10 rounded-md bg-gray-100 text-black placeholder-gray-500 border border-gray-300 focus:outline-none focus:ring-2 focus:ring-[#5f2e18]"
                placeholder="••••••••"
            />
            <button type="button" class="absolute right-3 top-2.5 text-gray-500 toggle-password" aria-label="Toggle password">
                <i class="bi bi-eye-slash" id="toggleIcon"></i>
            </button>
        </div>
        @error('password')
            <div class="text-sm text-red-600 mt-1">{{ $message }}</div>
        @enderror
    </div>

    <div class="flex items-center justify-between text-sm text-gray-600">
        <label class="flex items-center">
            <input type="checkbox" name="remember" class="mr-2 text-[#5f2e18]" {{ old('remember') ? 'checked' : '' }}>
            Remember me
        </label>
        <a href="{{ route('password.request') }}" class="hover:underline">Forgot password?</a>
    </div>

    <button type="submit" class="w-full bg-[#5f2e18] text-white py-2 rounded-md font-semibold hover:bg-[#3e1b0d] transition">
        Sign In
    </button>
</form>

        </div>

        {{-- RIGHT: Background Image --}}
        <div class="hidden md:block">
            <img src="{{ asset('images/login-background.jpg') }}" alt="Coffee Image"
                class="w-full h-full object-cover" />
        </div>
    </div>
</div>

<style>
    .bg-linear-image {
        background-image: linear-gradient(
                rgba(0, 0, 0, 0.84),
                rgba(0, 0, 0, 0.83)
            ),
            url("{{ asset('images/login-background.png') }}");
        background-size: cover;
        background-position: center;
        background-repeat: no-repeat;
    }
</style>

<script>
    document.querySelector('.toggle-password')?.addEventListener('click', function () {
        const password = document.getElementById('password');
        const icon = document.getElementById('toggleIcon');
        const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
        password.setAttribute('type', type);
        icon.classList.toggle('bi-eye');
        icon.classList.toggle('bi-eye-slash');
    });
</script>
@endsection
