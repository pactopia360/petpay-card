@extends('layouts.guest')

@section('title', 'PETPAY-CARD | Registro Usuario')

@section('content')
    <main class="petpay-auth petpay-auth--usuario">
        <header class="petpay-auth__topbar">
            <a href="{{ route('cliente.login') }}" class="petpay-auth__back" aria-label="Regresar">
                ←
            </a>

            <div class="petpay-auth__brand">
                <span class="petpay-auth__brand-mark">P</span>
                <span class="petpay-auth__brand-text">Petpay</span>
            </div>
        </header>

        <section class="petpay-auth__screen">
            <div class="petpay-auth__panel petpay-auth__panel--wide">
                <div class="petpay-auth__intro">
                    <p class="petpay-auth__eyebrow">Portal Usuario</p>

                    <h1>Crea tu cuenta</h1>

                    <p>
                        Regístrate para comprar productos, servicios y beneficios para tu mascota.
                    </p>
                </div>

                @if ($errors->any())
                    <div class="petpay-auth__alert petpay-auth__alert--danger">
                        {{ $errors->first() }}
                    </div>
                @endif

                @if (session('status'))
                    <div class="petpay-auth__alert petpay-auth__alert--success">
                        {{ session('status') }}
                    </div>
                @endif

                <div class="petpay-auth__social" style="margin-bottom: 10px;">
                    <a href="{{ route('cliente.google.redirect') }}" class="petpay-auth__social-button">
                        <span>G</span>
                        Registrarme con Google
                    </a>
                </div>

                <div class="petpay-auth__divider">
                    <span></span>
                    <small>o</small>
                    <span></span>
                </div>

                <form method="POST" action="{{ route('cliente.register.store') }}" class="petpay-auth__form">
                    @csrf

                    <div class="petpay-auth__form-grid">
                        <div class="petpay-auth__group">
                            <label class="petpay-auth__label" for="first_name">
                                Nombre
                            </label>

                            <input
                                id="first_name"
                                class="petpay-auth__field"
                                type="text"
                                name="first_name"
                                value="{{ old('first_name') }}"
                                placeholder="Nombre"
                                autocomplete="given-name"
                                required
                                autofocus
                            >
                        </div>

                        <div class="petpay-auth__group">
                            <label class="petpay-auth__label" for="last_name">
                                Apellido
                            </label>

                            <input
                                id="last_name"
                                class="petpay-auth__field"
                                type="text"
                                name="last_name"
                                value="{{ old('last_name') }}"
                                placeholder="Apellido"
                                autocomplete="family-name"
                            >
                        </div>

                        <div class="petpay-auth__group">
                            <label class="petpay-auth__label" for="email">
                                Correo electrónico
                            </label>

                            <input
                                id="email"
                                class="petpay-auth__field"
                                type="email"
                                name="email"
                                value="{{ old('email') }}"
                                placeholder="tu@email.com"
                                autocomplete="email"
                                required
                            >
                        </div>

                        <div class="petpay-auth__group">
                            <label class="petpay-auth__label" for="phone">
                                Teléfono
                            </label>

                            <input
                                id="phone"
                                class="petpay-auth__field"
                                type="tel"
                                name="phone"
                                value="{{ old('phone') }}"
                                placeholder="55 0000 0000"
                                autocomplete="tel"
                                required
                            >
                        </div>

                        <div class="petpay-auth__group petpay-auth__group--full">
                            <label class="petpay-auth__label" for="main_address">
                                Dirección principal
                            </label>

                            <input
                                id="main_address"
                                class="petpay-auth__field"
                                type="text"
                                name="main_address"
                                value="{{ old('main_address') }}"
                                placeholder="Dirección de entrega"
                                autocomplete="street-address"
                            >
                        </div>

                        <div class="petpay-auth__group">
                            <label class="petpay-auth__label" for="password">
                                Contraseña
                            </label>

                            <input
                                id="password"
                                class="petpay-auth__field"
                                type="password"
                                name="password"
                                placeholder="Mínimo 8 caracteres"
                                autocomplete="new-password"
                                required
                            >
                        </div>

                        <div class="petpay-auth__group">
                            <label class="petpay-auth__label" for="password_confirmation">
                                Confirmar contraseña
                            </label>

                            <input
                                id="password_confirmation"
                                class="petpay-auth__field"
                                type="password"
                                name="password_confirmation"
                                placeholder="Repite tu contraseña"
                                autocomplete="new-password"
                                required
                            >
                        </div>
                    </div>

                    <button type="submit" class="petpay-auth__primary">
                        Crear cuenta
                    </button>
                </form>

                <p class="petpay-auth__legal">
                    Al crear tu cuenta aceptas usar Petpay para gestionar compras, servicios,
                    direcciones y beneficios relacionados con tu mascota.
                </p>

                <div class="petpay-auth__links">
                    <a href="{{ route('cliente.login') }}">
                        Ya tengo cuenta
                    </a>
                </div>
            </div>
        </section>
    </main>
@endsection