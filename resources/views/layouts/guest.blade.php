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
        .border-brown-700 { border-color:#4e2e1c; }
        .bg-brown-700 { background-color:#4e2e1c; }
        .hover\:bg-brown-700:hover { background-color:#4e2e1c; }
        .hover\:bg-brown-800:hover { background-color:#3e2416; }
        body {
            background-color:rgba(215, 211, 211, 1);
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
