<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('messages.pos') }} System - {{ optional($setting)->shop_name ?? 'Coffee Land' }}</title>


    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">

    <!-- Favicon (optional) -->
    <link rel="icon" href="{{ asset('images/coffeeland-logo.png') }}" type="image/png">

    <!-- Custom Styles (optional) -->
    <style>
        body {
            background-color:rgb(0, 0, 0);
        }
    </style>
</head>
<body class="antialiased text-gray-700">

    {{-- Main Content --}}
    <main>
        @yield('content')
    </main>

</body>
</html>
