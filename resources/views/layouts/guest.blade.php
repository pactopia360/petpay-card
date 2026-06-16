<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'PETPAY-CARD')</title>

    <link rel="stylesheet" href="{{ asset('css/petpay-auth.css') }}">

    @stack('styles')
</head>
<body>
    @yield('content')

    @stack('scripts')
</body>
</html>