@extends('layouts.guest')

@section('title', 'Recuperar acceso Repartidor | Petpay')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/driver-login.css') }}?v={{ filemtime(public_path('css/driver-login.css')) }}">
@endpush

@section('content')
<main class="petpay-wp-login">
    <header class="petpay-wp-login__header">
        <button type="button" class="petpay-wp-login__back" data-login-go-back aria-label="Regresar">
            <svg aria-hidden="true" viewBox="0 0 448 512">
                <path d="M257.5 445.1l-22.2 22.2c-9.4 9.4-24.6 9.4-33.9 0L7 273c-9.4-9.4-9.4-24.6 0-33.9L201.4 44.7c9.4-9.4 24.6-9.4 33.9 0l22.2 22.2c9.5 9.5 9.3 25-.4 34.3L136.6 216H424c13.3 0 24 10.7 24 24v32c0 13.3-10.7 24-24 24H136.6l120.5 114.8c9.8 9.3 10 24.8.4 34.3z"/>
            </svg>
        </button>
        <a href="{{ url('/') }}" class="petpay-wp-login__logo">
            <img src="{{ asset('images/commerce/petpay-logo-white.png') }}" alt="Petpay">
        </a>
    </header>

    <section class="petpay-wp-login__center">
        <div class="petpay-wp-login__box">
            <h1 class="petpay-wp-login__title">Recupera tu acceso</h1>
            <div class="petpay-wp-login__message petpay-wp-login__message--success">
                La recuperación por correo se integrará en el siguiente bloque. Por ahora contacta a soporte.
            </div>
            <a href="{{ route('repartidor.login') }}" class="petpay-wp-login__google">Volver al inicio de sesión</a>
        </div>
    </section>

    <footer class="petpay-wp-login__footer">
        <p>ALL CONTENT COPYRIGHT© PETPAY SAPI DE CV. 2026</p>
    </footer>
</main>
@endsection

@push('scripts')
    <script src="{{ asset('js/driver-login.js') }}?v={{ filemtime(public_path('js/driver-login.js')) }}" defer></script>
@endpush
