@extends('layouts.guest')

@section('title', 'Registro en revisión | Petpay')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/driver-login.css') }}?v={{ filemtime(public_path('css/driver-login.css')) }}">
@endpush

@section('content')
<main class="petpay-wp-login">
    <header class="petpay-wp-login__header">
        <span></span>
        <a href="{{ url('/') }}" class="petpay-wp-login__logo">
            <img src="{{ asset('images/commerce/petpay-logo-white.png') }}" alt="Petpay">
        </a>
    </header>

    <section class="petpay-wp-login__center">
        <div class="petpay-wp-login__box driver-pending-box">
            <div class="driver-pending-icon">🛵</div>
            <h1 class="petpay-wp-login__title">Tu registro está en revisión</h1>

            @if (session('status'))
                <div class="petpay-wp-login__message petpay-wp-login__message--success">{{ session('status') }}</div>
            @endif

            <p class="driver-pending-text">
                Recibimos tu solicitud, {{ $driver->first_name }}. Admin revisará tus datos antes de habilitar tu acceso.
            </p>

            <div class="driver-pending-summary">
                <span><small>Correo</small><strong>{{ $driver->email }}</strong></span>
                <span><small>Vehículo</small><strong>{{ ucfirst((string) $driver->vehicle_type) }}</strong></span>
                <span><small>Zona</small><strong>{{ $driver->operation_zone ?: 'Pendiente' }}</strong></span>
            </div>

            <form method="POST" action="{{ route('repartidor.logout') }}">
                @csrf
                <button type="submit" class="petpay-wp-login__submit">Cerrar sesión</button>
            </form>
        </div>
    </section>

    <footer class="petpay-wp-login__footer">
        <p>ALL CONTENT COPYRIGHT© PETPAY SAPI DE CV. 2026</p>
    </footer>
</main>
@endsection
