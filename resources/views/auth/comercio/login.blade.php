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
                    <a
                        href="{{ route('comercio.google.redirect') }}"
                        class="petpay-auth__social-button"
                        style="text-decoration: none;"
                        aria-label="Continúa con Google"
                    >
                        <span style="display:inline-flex;align-items:center;justify-content:center;width:20px;height:20px;">
                            <svg width="20" height="20" viewBox="0 0 48 48" aria-hidden="true" focusable="false">
                                <path fill="#FFC107" d="M43.611 20.083H42V20H24v8h11.303C33.654 32.657 29.202 36 24 36c-6.627 0-12-5.373-12-12s5.373-12 12-12c3.059 0 5.842 1.154 7.961 3.039l5.657-5.657C34.046 6.053 29.27 4 24 4C12.955 4 4 12.955 4 24s8.955 20 20 20s20-8.955 20-20c0-1.341-.138-2.65-.389-3.917z"/>
                                <path fill="#FF3D00" d="M6.306 14.691l6.571 4.819C14.655 16.108 19.001 12 24 12c3.059 0 5.842 1.154 7.961 3.039l5.657-5.657C34.046 6.053 29.27 4 24 4c-7.682 0-14.347 4.337-17.694 10.691z"/>
                                <path fill="#4CAF50" d="M24 44c5.17 0 9.86-1.977 13.409-5.193l-6.19-5.238C29.143 35.091 26.715 36 24 36c-5.181 0-9.624-3.329-11.287-7.946l-6.522 5.025C9.5 39.556 16.227 44 24 44z"/>
                                <path fill="#1976D2" d="M43.611 20.083H42V20H24v8h11.303c-.793 2.24-2.231 4.166-4.084 5.569c.001-.001 6.19 5.238 6.19 5.238C36.971 39.205 44 34 44 24c0-1.341-.138-2.65-.389-3.917z"/>
                            </svg>
                        </span>
                        Continúa con Google
                    </a>

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