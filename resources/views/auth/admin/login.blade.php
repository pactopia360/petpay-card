@extends('layouts.guest')

@section('title', 'PETPAY-CARD | Login Admin')

@section('content')
    <main class="petpay-auth petpay-auth--admin">
        <header class="petpay-auth__topbar">
            <a href="{{ route('home') }}" class="petpay-auth__back" aria-label="Regresar">
                ←
            </a>

            <div class="petpay-auth__brand">
                <span class="petpay-auth__brand-mark">P</span>
                <span class="petpay-auth__brand-text">Petpay Admin</span>
            </div>
        </header>

        <section class="petpay-auth__screen">
            <div class="petpay-auth__panel petpay-auth__panel--admin">
                <div class="petpay-auth__intro">
                    <p class="petpay-auth__eyebrow">Panel Administrativo</p>
                    <h1>Acceso Admin</h1>
                    <p>
                        Ingresa con tu usuario administrador para controlar la operación
                        general de PETPAY-CARD.
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

                <form method="POST" action="{{ route('admin.login.store') }}" class="petpay-auth__form">
                    @csrf

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
                            placeholder="admin@petpay-card.com"
                            autocomplete="email"
                            required
                            autofocus
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
                            placeholder="Ingresa tu contraseña"
                            autocomplete="current-password"
                            required
                        >
                    </div>

                    <label class="petpay-auth__remember">
                        <input type="checkbox" name="remember" value="1">
                        <span>Recordar sesión</span>
                    </label>

                    <button type="submit" class="petpay-auth__primary">
                        Entrar al Admin
                    </button>
                </form>

                <div class="petpay-auth__links">
                    <a href="{{ route('admin.password.request') }}">
                        Recuperar contraseña
                    </a>

                    <a href="{{ route('home') }}">
                        Volver al inicio
                    </a>
                </div>
            </div>
        </section>
    </main>
@endsection