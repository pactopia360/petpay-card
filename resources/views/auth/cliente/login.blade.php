@extends('layouts.guest')

@section('title', 'PETPAY-CARD | Login Cliente')

@section('content')
    <main class="petpay-auth petpay-auth--cliente">
        <header class="petpay-auth__topbar">
            <a href="{{ route('home') }}" class="petpay-auth__back" aria-label="Regresar">
                ←
            </a>

            <div class="petpay-auth__brand">
                <span class="petpay-auth__brand-mark">P</span>
                <span class="petpay-auth__brand-text">Petpay</span>
            </div>
        </header>

        <section class="petpay-auth__screen">
            <div class="petpay-auth__panel petpay-auth__panel--login-card">
                <div class="petpay-auth__intro petpay-auth__intro--login">
                    <p class="petpay-auth__eyebrow">Portal Usuario</p>

                    <h1>
                        Registrate con tu correo electrónico o teléfono
                    </h1>

                    <p>
                       Accede a Petpay para comprar productos, servicios y beneficios para tu mascota.
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

                <form method="POST" action="{{ route('cliente.login.store') }}" class="petpay-auth__form">
                    @csrf

                    <div class="petpay-auth__group">
                        <input
                            id="email_or_phone"
                            class="petpay-auth__field petpay-auth__field--login"
                            type="text"
                            name="email_or_phone"
                            value="{{ old('email_or_phone') }}"
                            placeholder="Ingresa tu teléfono o correo electrónico"
                            autocomplete="username"
                            required
                            autofocus
                        >
                    </div>

                    <div class="petpay-auth__group">
                        <input
                            id="password"
                            class="petpay-auth__field petpay-auth__field--login"
                            type="password"
                            name="password"
                            placeholder="Ingresa tu contraseña"
                            autocomplete="current-password"
                            required
                        >
                    </div>

                    <label class="petpay-auth__remember petpay-auth__remember--login">
                        <input type="checkbox" name="remember" value="1">
                        <span>Mantener sesión iniciada</span>
                    </label>

                    <button type="submit" class="petpay-auth__primary petpay-auth__primary--login">
                        Continuar
                    </button>
                </form>

                <div class="petpay-auth__divider petpay-auth__divider--login">
                    <span></span>
                    <small>o</small>
                    <span></span>
                </div>

                <div class="petpay-auth__social">
                    <a href="{{ route('cliente.google.redirect') }}" class="petpay-auth__social-button petpay-auth__social-button--login">
                        <span>G</span>
                        Continúa con Google
                    </a>

                    <button type="button" class="petpay-auth__social-button petpay-auth__social-button--login" disabled>
                        <span></span>
                        Continúa con Apple
                    </button>
                </div>

                <div class="petpay-auth__divider petpay-auth__divider--login">
                    <span></span>
                    <small>o</small>
                    <span></span>
                </div>

                <button type="button" class="petpay-auth__social-button petpay-auth__social-button--login" disabled>
                    <span>▣</span>
                    Inicia sesión con un código QR
                </button>

                <p class="petpay-auth__legal petpay-auth__legal--login">
                    Aceptas recibir un código de verificación por mensaje de texto o WhatsApp.
                    Pueden aplicarse tarifas de mensajes y datos.
                </p>

                <div class="petpay-auth__links petpay-auth__links--login">
                    <a href="{{ route('cliente.password.request') }}">
                        ¿Olvidaste tu contraseña?
                    </a>

                    <a href="{{ route('cliente.register') }}">
                        Crear cuenta
                    </a>
                </div>
            </div>
        </section>
    </main>
@endsection