@php
    $portal = $portal ?? 'public';
@endphp

<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'PETPAY-CARD')</title>

    <link rel="stylesheet" href="{{ asset('assets/petpay-card/css/app.css') }}?v=20260711-04">

    @if (($portal ?? null) === 'cliente')
        <link rel="stylesheet" href="{{ asset('assets/petpay-card/css/portals/cliente.css') }}?v=20260711-04">
    @endif

    @if ($portal !== 'public')
        <link
            rel="stylesheet"
            href="{{ asset('assets/petpay-card/css/portals/' . $portal . '.css') }}?v=20260711-04"
        >
    @endif

    @stack('styles')
</head>
<body class="petpay-body petpay-body--{{ $portal }}">
    <div class="petpay-page petpay-page-{{ $portal }}">
        @if ($portal === 'comercio')
            @include('partials.headers.comercio')
        @else
            @includeIf('partials.headers.' . $portal)
        @endif

        <main class="petpay-main">
            @yield('content')
        </main>
    </div>

    @stack('scripts')
</body>
</html>
