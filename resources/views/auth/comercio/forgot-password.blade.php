@extends('layouts.guest')

@section('title', 'PETPAY-CARD | Recuperar contraseña Comercio')

@section('content')
    <main class="petpay-auth petpay-auth--commerce">
        <header class="petpay-auth__topbar">
            <a href="{{ route('comercio.login') }}" class="petpay-auth__back" aria-label="Regresar">
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
                    <h1>Recupera tu contraseña</h1>
                    <p>
                        Ingresa el correo de tu comercio. Más adelante conectaremos el envío real
                        de recuperación por correo, SMS o WhatsApp.
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

                <form method="POST" action="#" class="petpay-auth__form">
                    @csrf

                    <label class="petpay-auth__label" for="email">
                        Correo electrónico
                    </label>

                    <input
                        id="email"
                        class="petpay-auth__field"
                        type="email"
                        name="email"
                        value="{{ old('email') }}"
                        placeholder="correo@comercio.com"
                        autocomplete="email"
                        required
                        autofocus
                    >

                    <button type="button" class="petpay-auth__primary" disabled>
                        Enviar instrucciones
                    </button>
                </form>

                <div class="petpay-auth__links">
                    <a href="{{ route('comercio.login') }}">
                        Volver al inicio de sesión
                    </a>
                </div>
            </div>
        </section>
    </main>
@endsection