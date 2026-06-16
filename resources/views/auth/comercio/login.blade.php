@extends('layouts.guest')

@section('title', 'PETPAY-CARD | Login Comercio')

@section('content')
    <main class="petpay-auth petpay-auth--commerce">
        <header class="petpay-auth__topbar">
            <a href="{{ url('/') }}" class="petpay-auth__back" aria-label="Regresar">
                ←
            </a>

            <div class="petpay-auth__brand">
                <span class="petpay-auth__brand-mark">P</span>
                <span class="petpay-auth__brand-text">Petpay</span>
            </div>
        </header>

        <section class="petpay-auth__screen">
            <div class="petpay-auth__panel">
                <div class="petpay-auth__intro">
                    <p class="petpay-auth__eyebrow">Portal Comercio</p>
                    <h1>¿Cuál es tu número de teléfono o tu correo electrónico?</h1>
                    <p>
                        Ingresa como comercio para administrar tus ventas, productos,
                        servicios y pedidos dentro de Petpay.
                    </p>
                </div>

                @if (session('status'))
                    <div class="petpay-auth__alert petpay-auth__alert--success">
                        {{ session('status') }}
                    </div>
                @endif

                @if ($errors->any())
                    <div class="petpay-auth__alert petpay-auth__alert--danger">
                        {{ $errors->first() }}
                    </div>
                @endif

                <form method="POST" action="{{ route('comercio.login.store') }}" class="petpay-auth__form">
                    @csrf

                    <label class="petpay-auth__label" for="email_or_phone">
                        Teléfono o correo electrónico
                    </label>

                    <input
                        id="email_or_phone"
                        class="petpay-auth__field"
                        type="text"
                        name="email_or_phone"
                        value="{{ old('email_or_phone') }}"
                        placeholder="Ingresa tu teléfono o correo electrónico"
                        autocomplete="username"
                        required
                        autofocus
                    >

                    <label class="petpay-auth__label" for="password">
                        Contraseña
                    </label>

                    <input
                        id="password"
                        class="petpay-auth__field"
                        type="password"
                        name="password"
                        placeholder="Ingresa tu contraseña"
                        autocomplete="current-password"
                        required
                    >

                    <label class="petpay-auth__remember">
                        <input type="checkbox" name="remember" value="1">
                        <span>Mantener sesión iniciada</span>
                    </label>

                    <button type="submit" class="petpay-auth__primary">
                        Continuar
                    </button>
                </form>

                <div class="petpay-auth__divider">
                    <span></span>
                    <small>o</small>
                    <span></span>
                </div>

                <div class="petpay-auth__social">
                    <button type="button" class="petpay-auth__social-button" disabled>
                        <span>G</span>
                        Continúa con Google
                    </button>

                    <button type="button" class="petpay-auth__social-button" disabled>
                        <span></span>
                        Continúa con Apple
                    </button>
                </div>

                <div class="petpay-auth__divider">
                    <span></span>
                    <small>o</small>
                    <span></span>
                </div>

                <button type="button" class="petpay-auth__social-button" disabled>
                    <span>▣</span>
                    Inicia sesión con un código QR
                </button>

                <p class="petpay-auth__legal">
                    Aceptas recibir un código de verificación por mensaje de texto o WhatsApp.
                    Pueden aplicarse tarifas de mensajes y datos.
                </p>

                <div class="petpay-auth__links">
                    <a href="{{ route('comercio.password.request') }}">
                        ¿Olvidaste tu contraseña?
                    </a>

                    <a href="{{ route('comercio.register') }}">
                        Registra tu comercio
                    </a>
                </div>
            </div>
        </section>
    </main>
@endsection