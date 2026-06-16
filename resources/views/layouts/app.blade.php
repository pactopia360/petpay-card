@php
    $portal = $portal ?? 'public';
@endphp

<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'PETPAY-CARD')</title>

    {{-- Base PETPAY-CARD --}}
    <link rel="stylesheet" href="{{ asset('assets/petpay-card/css/app.css') }}">

    @if (($portal ?? null) === 'cliente')
        <link rel="stylesheet" href="{{ asset('assets/petpay-card/css/portals/cliente.css') }}">
    @endif

    {{-- CSS por portal --}}
    @if ($portal !== 'public')
        <link rel="stylesheet" href="{{ asset('assets/petpay-card/css/portals/' . $portal . '.css') }}">
    @endif

    {{-- CSS por pantalla --}}
    @stack('styles')
</head>
<body>
    <div class="petpay-page petpay-page-{{ $portal }}">
        @includeIf('partials.headers.' . $portal)

        <main class="petpay-main">
            @yield('content')
        </main>
    </div>

    @stack('scripts')
</body>
</html>