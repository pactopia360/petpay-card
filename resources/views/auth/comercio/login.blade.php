@extends('layouts.guest')

@section('title', 'Login | Petpay')

@push('styles')
    <link rel="stylesheet"
          href="{{ asset('css/commerce-login.css') }}?v={{ filemtime(public_path('css/commerce-login.css')) }}">
@endpush

@section('content')
<main class="petpay-wp-login">

    <header class="petpay-wp-login__header">
        <button
            type="button"
            class="petpay-wp-login__back"
            data-login-go-back
            aria-label="Regresar"
        >
            <svg aria-hidden="true" viewBox="0 0 448 512">
                <path d="M257.5 445.1l-22.2 22.2c-9.4 9.4-24.6 9.4-33.9 0L7 273c-9.4-9.4-9.4-24.6 0-33.9L201.4 44.7c9.4-9.4 24.6-9.4 33.9 0l22.2 22.2c9.5 9.5 9.3 25-.4 34.3L136.6 216H424c13.3 0 24 10.7 24 24v32c0 13.3-10.7 24-24 24H136.6l120.5 114.8c9.8 9.3 10 24.8.4 34.3z"/>
            </svg>
        </button>

        <a href="{{ url('/') }}" class="petpay-wp-login__logo">
            <img
                src="{{ asset('images/commerce/petpay-logo-white.png') }}"
                alt="Petpay"
            >
        </a>
    </header>

    <section class="petpay-wp-login__center">
        <div class="petpay-wp-login__box">

            <h1 class="petpay-wp-login__title">
                Ingresa con tus datos de usuario
            </h1>

            @if (session('status'))
                <div class="petpay-wp-login__message petpay-wp-login__message--success">
                    {{ session('status') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="petpay-wp-login__message petpay-wp-login__message--error">
                    {{ $errors->first() }}
                </div>
            @endif

            <form
                method="POST"
                action="{{ route('comercio.login.store') }}"
                class="petpay-wp-login__form"
            >
                @csrf

                <label class="petpay-wp-login__sr-only" for="email_or_phone">
                    Teléfono o correo electrónico
                </label>
                <input
                    id="email_or_phone"
                    class="petpay-wp-login__input"
                    type="text"
                    name="email_or_phone"
                    value="{{ old('email_or_phone') }}"
                    placeholder="Ingresa tu teléfono o correo electrónico"
                    autocomplete="username"
                    required
                    autofocus
                >

                <label class="petpay-wp-login__sr-only" for="password">
                    Contraseña
                </label>
                <div class="petpay-wp-login__password-field">
                    <input
                        id="password"
                        class="petpay-wp-login__input"
                        type="password"
                        name="password"
                        placeholder="Ingresa tu contraseña"
                        autocomplete="current-password"
                        required
                    >

                    <button
                        type="button"
                        class="petpay-wp-login__show-password"
                        data-password-toggle
                        aria-label="Mostrar contraseña"
                    >
                        Ver
                    </button>
                </div>

                <label class="petpay-wp-login__remember">
                    <input
                        type="checkbox"
                        name="remember"
                        value="1"
                        @checked(old('remember'))
                    >
                    <span>Mantener sesión iniciada</span>
                </label>

                <button type="submit" class="petpay-wp-login__submit">
                    Iniciar sesión
                </button>

                <hr class="petpay-wp-login__divider">

                <a
                    href="{{ route('comercio.google.redirect') }}"
                    class="petpay-wp-login__google"
                >
                    <svg
                        class="petpay-wp-login__google-icon"
                        viewBox="0 0 48 48"
                        aria-hidden="true"
                    >
                        <path fill="#FFC107" d="M43.611 20.083H42V20H24v8h11.303C33.654 32.657 29.202 36 24 36c-6.627 0-12-5.373-12-12s5.373-12 12-12c3.059 0 5.842 1.154 7.961 3.039l5.657-5.657C34.046 6.053 29.27 4 24 4C12.955 4 4 12.955 4 24s8.955 20 20 20s20-8.955 20-20c0-1.341-.138-2.65-.389-3.917z"/>
                        <path fill="#FF3D00" d="M6.306 14.691l6.571 4.819C14.655 16.108 19.001 12 24 12c3.059 0 5.842 1.154 7.961 3.039l5.657-5.657C34.046 6.053 29.27 4 24 4c-7.682 0-14.347 4.337-17.694 10.691z"/>
                        <path fill="#4CAF50" d="M24 44c5.17 0 9.86-1.977 13.409-5.193l-6.19-5.238C29.143 35.091 26.715 36 24 36c-5.181 0-9.624-3.329-11.287-7.946l-6.522 5.025C9.5 39.556 16.227 44 24 44z"/>
                        <path fill="#1976D2" d="M43.611 20.083H42V20H24v8h11.303c-.793 2.24-2.231 4.166-4.084 5.569c.001-.001 6.19 5.238 6.19 5.238C36.971 39.205 44 34 44 24c0-1.341-.138-2.65-.389-3.917z"/>
                    </svg>

                    <span>Continuar con Google</span>
                </a>

                <div class="petpay-wp-login__bottom-links">
                    <a href="{{ route('comercio.password.request') }}">
                        ¿Olvidaste tu contraseña?
                    </a>

                    <span class="petpay-wp-login__dot">•</span>

                    <a href="{{ route('comercio.register') }}">
                        Registrar comercio
                    </a>
                </div>
            </form>
        </div>
    </section>

    <footer class="petpay-wp-login__footer">
        <p>ALL CONTENT COPYRIGHT© PETPAY SAPI DE CV. 2026</p>
    </footer>

</main>
@endsection

@push('scripts')
    <script
        src="{{ asset('js/commerce-login.js') }}?v={{ filemtime(public_path('js/commerce-login.js')) }}"
        defer
    ></script>
@endpush