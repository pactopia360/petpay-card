@extends('layouts.guest')

@section('title', 'Registra tu comercio | Petpay')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/commerce-register.css') }}">
@endpush

@section('content')
    @php
        $commerceTypes = [
            'Acuario público',
            'Asociación protectora de animales',
            'Aviario',
            'Boutique para mascotas',
            'Cafetería de gatos',
            'Cementerio para mascotas',
            'Centro de adopción',
            'Centro de adiestramiento',
            'Centro de control animal',
            'Centro de reproducción asistida animal',
            'Clínica de especialidades veterinarias',
            'Clínica móvil veterinaria',
            'Concurso canino',
            'Crematorio para mascotas',
            'Criadero de gatos',
            'Criadero de perros',
            'Entrenamiento de agility',
            'Escuela de obediencia canina',
            'Estética felina',
            'Feria de adopción',
            'Farmacia veterinaria',
            'Fisioterapia animal',
            'Fotografía de mascotas',
            'Grooming canino',
            'Guardería para gatos',
            'Guardería para perros',
            'Hospital veterinario',
            'Hotel para mascotas',
            'Hotel pet friendly',
            'Juguetería para mascotas',
            'Nutrición para mascotas',
            'Parque para perros',
            'Paseo de perros',
            'Playa pet friendly',
            'Psicología animal',
            'Rehabilitación veterinaria',
            'Refugio animal',
            'Rescate de fauna',
            'Restaurante pet friendly',
            'Santuario de animales',
            'Seguro para mascotas',
            'Spa para mascotas',
            'Taxi para mascotas',
            'Terrario y reptiles',
            'Tienda de accesorios para mascotas',
            'Tienda de alimento premium',
            'Tienda de acuarios',
            'Tienda de mascotas',
            'Transporte para mascotas',
            'Veterinaria',
        ];

        $googleRegistration = $googleRegistration ?? null;

        $isGoogleRegistration = is_array($googleRegistration)
            && ! empty($googleRegistration['google_id'])
            && ! empty($googleRegistration['email']);
    @endphp

    <main class="commerce-register">
        <header class="commerce-register__header">
            <a
                href="{{ url('/') }}"
                class="commerce-register__logo"
                aria-label="Ir al inicio de Petpay"
            >
                <span class="commerce-register__logo-symbol">P</span>
                <span class="commerce-register__logo-text">Petpay</span>
            </a>

            <nav class="commerce-register__navigation" aria-label="Acceso de comercios">
                <a
                    href="{{ route('comercio.register') }}"
                    class="commerce-register__navigation-link commerce-register__navigation-link--active"
                >
                    Regístrate
                </a>

                <a
                    href="{{ route('comercio.login') }}"
                    class="commerce-register__navigation-link commerce-register__navigation-link--dark"
                >
                    Iniciar sesión
                </a>
            </nav>
        </header>

        <section class="commerce-register__hero">
            <div class="commerce-register__overlay"></div>

            <div class="commerce-register__content">
                <section class="commerce-register__card">
                    <div class="commerce-register__intro">

                        <h1>Registra tu comercio</h1>

                        <p>
                            ¿Ya tienes cuenta?
                            <a href="{{ route('comercio.login') }}">
                                Ingresa ahora
                            </a>
                        </p>
                    </div>

                    @if (session('status'))
                        <div class="commerce-register__google-status">
                            {{ session('status') }}
                        </div>
                    @endif

                    @if ($errors->any())
                        <div class="commerce-register__alert" role="alert">
                            <strong>Revisa la información:</strong>

                            <ul>
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form
                        method="POST"
                        action="{{ route('comercio.register.store') }}"
                        class="commerce-register__form"
                        novalidate
                    >
                        @csrf

                        <div class="commerce-register__grid">
                            <div class="commerce-register__field">
                                <label for="first_name">Nombre</label>

                                <input
                                    id="first_name"
                                    type="text"
                                    name="first_name"
                                    value="{{ old('first_name', $googleRegistration['first_name'] ?? '') }}"
                                    autocomplete="given-name"
                                    required
                                    autofocus
                                >
                            </div>

                            <div class="commerce-register__field">
                                <label for="last_name">Apellido</label>

                                <input
                                    id="last_name"
                                    type="text"
                                    name="last_name"
                                    value="{{ old('last_name', $googleRegistration['last_name'] ?? '') }}"
                                    autocomplete="family-name"
                                >
                            </div>

                            <div class="commerce-register__field commerce-register__field--full">
                                <label for="email">Correo electrónico</label>

                                <input
                                    id="email"
                                    type="email"
                                    name="email"
                                    value="{{ old('email', $googleRegistration['email'] ?? '') }}"
                                    autocomplete="email"
                                    @readonly($isGoogleRegistration)
                                    required
                                >

                                @if ($isGoogleRegistration)
                                    <small class="commerce-register__google-connected">
                                        Cuenta de Google conectada. Este correo no puede modificarse.
                                    </small>
                                @endif
                            </div>

                            <div class="commerce-register__field commerce-register__field--full">
                                <label for="phone">Número de teléfono móvil</label>

                                <input
                                    id="phone"
                                    type="tel"
                                    name="phone"
                                    value="{{ old('phone') }}"
                                    inputmode="tel"
                                    autocomplete="tel"
                                    required
                                >
                            </div>

                            <div class="commerce-register__field commerce-register__field--full">
                                <label for="business_address">
                                    Dirección del negocio
                                </label>

                                <input
                                    id="business_address"
                                    type="text"
                                    name="business_address"
                                    value="{{ old('business_address') }}"
                                    placeholder="Ingresa una ubicación"
                                    autocomplete="street-address"
                                    required
                                >
                            </div>

                            <div class="commerce-register__field commerce-register__field--full">
                                <label for="floor_office">
                                    Piso/Oficina
                                    <span>(opcional)</span>
                                </label>

                                <input
                                    id="floor_office"
                                    type="text"
                                    name="floor_office"
                                    value="{{ old('floor_office') }}"
                                >
                            </div>

                            <div class="commerce-register__field commerce-register__field--full">
                                <label for="business_name">Nombre del negocio</label>

                                <input
                                    id="business_name"
                                    type="text"
                                    name="business_name"
                                    value="{{ old('business_name') }}"
                                    required
                                >
                            </div>

                            <div class="commerce-register__field commerce-register__field--full">
                                <label for="brand_name">Nombre de la marca</label>

                                <input
                                    id="brand_name"
                                    type="text"
                                    name="brand_name"
                                    value="{{ old('brand_name') }}"
                                >
                            </div>

                            <div class="commerce-register__field commerce-register__field--full">
                                <label for="business_type">Tipo de comercio</label>

                                <select
                                    id="business_type"
                                    name="business_type"
                                    required
                                >
                                    <option value="">Selecciona una opción</option>

                                    @foreach ($commerceTypes as $commerceType)
                                        <option
                                            value="{{ $commerceType }}"
                                            @selected(old('business_type') === $commerceType)
                                        >
                                            {{ $commerceType }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="commerce-register__field commerce-register__field--full">
                                <label for="website_url">
                                    Enlace de redes sociales/sitio web
                                    <span>(opcional)</span>
                                </label>

                                <input
                                    id="website_url"
                                    type="text"
                                    name="website_url"
                                    value="{{ old('website_url') }}"
                                    placeholder="https://"
                                >
                            </div>

                            @if (! $isGoogleRegistration)
                                <div class="commerce-register__field">
                                    <label for="password">Contraseña</label>

                                    <div class="commerce-register__password">
                                        <input
                                            id="password"
                                            type="password"
                                            name="password"
                                            minlength="8"
                                            autocomplete="new-password"
                                            required
                                        >

                                        <button
                                            type="button"
                                            class="commerce-register__password-toggle"
                                            data-password-toggle="password"
                                            aria-label="Mostrar contraseña"
                                        >
                                            Ver
                                        </button>
                                    </div>
                                </div>

                                <div class="commerce-register__field">
                                    <label for="password_confirmation">
                                        Confirmar contraseña
                                    </label>

                                    <div class="commerce-register__password">
                                        <input
                                            id="password_confirmation"
                                            type="password"
                                            name="password_confirmation"
                                            minlength="8"
                                            autocomplete="new-password"
                                            required
                                        >

                                        <button
                                            type="button"
                                            class="commerce-register__password-toggle"
                                            data-password-toggle="password_confirmation"
                                            aria-label="Mostrar confirmación de contraseña"
                                        >
                                            Ver
                                        </button>
                                    </div>
                                </div>
                            @else
                                <div class="commerce-register__google-password commerce-register__field--full">
                                    <strong>Acceso con Google</strong>

                                    <span>
                                        No necesitas crear una contraseña. Iniciarás sesión usando tu cuenta de Google.
                                    </span>
                                </div>
                            @endif
                        </div>

                        <label class="commerce-register__checkbox">
                            <input
                                type="checkbox"
                                name="whatsapp_enabled"
                                value="1"
                                @checked(old('whatsapp_enabled'))
                            >

                            <span class="commerce-register__checkbox-box"></span>

                            <span class="commerce-register__checkbox-text">
                                <strong>WhatsApp</strong>
                                Activa la mensajería de WhatsApp
                            </span>
                        </label>

                        <details class="commerce-register__details">
                            <summary>Configuración inicial del comercio</summary>

                            <div class="commerce-register__details-content">
                                <label class="commerce-register__option">
                                    <input
                                        type="checkbox"
                                        name="sells_products"
                                        value="1"
                                        @checked(old('sells_products', true))
                                    >

                                    <span>
                                        <strong>Vendo productos</strong>
                                        <small>
                                            Alimento, accesorios, juguetes, higiene y más.
                                        </small>
                                    </span>
                                </label>

                                <label class="commerce-register__option">
                                    <input
                                        type="checkbox"
                                        name="offers_services"
                                        value="1"
                                        @checked(old('offers_services'))
                                    >

                                    <span>
                                        <strong>Ofrezco servicios</strong>
                                        <small>
                                            Veterinaria, estética, entrenamiento, hotel o guardería.
                                        </small>
                                    </span>
                                </label>

                                <label class="commerce-register__option">
                                    <input
                                        type="checkbox"
                                        name="has_own_delivery"
                                        value="1"
                                        @checked(old('has_own_delivery'))
                                    >

                                    <span>
                                        <strong>Tengo entrega propia</strong>
                                        <small>
                                            Mi comercio puede realizar entregas directamente.
                                        </small>
                                    </span>
                                </label>

                                <label class="commerce-register__option">
                                    <input
                                        type="checkbox"
                                        name="uses_petpay_delivery"
                                        value="1"
                                        @checked(old('uses_petpay_delivery', true))
                                    >

                                    <span>
                                        <strong>Quiero usar repartidores Petpay</strong>
                                        <small>
                                            Petpay podrá asignar pedidos a repartidores.
                                        </small>
                                    </span>
                                </label>

                                <div class="commerce-register__grid">
                                    <div class="commerce-register__field">
                                        <label for="business_phone">
                                            Teléfono del comercio
                                            <span>(opcional)</span>
                                        </label>

                                        <input
                                            id="business_phone"
                                            type="tel"
                                            name="business_phone"
                                            value="{{ old('business_phone') }}"
                                            inputmode="tel"
                                        >
                                    </div>

                                    <div class="commerce-register__field">
                                        <label for="business_email">
                                            Correo del comercio
                                            <span>(opcional)</span>
                                        </label>

                                        <input
                                            id="business_email"
                                            type="email"
                                            name="business_email"
                                            value="{{ old('business_email') }}"
                                        >
                                    </div>
                                </div>
                            </div>
                        </details>

                        <label class="commerce-register__terms">
                            <input
                                type="checkbox"
                                name="terms"
                                value="1"
                                @checked(old('terms'))
                                required
                            >

                            <span>
                                Acepto los
                                <a href="{{ url('/terminos') }}" target="_blank">
                                    Términos y condiciones
                                </a>
                                y confirmo que leí el
                                <a href="{{ url('/privacidad') }}" target="_blank">
                                    Aviso de privacidad
                                </a>.
                            </span>
                        </label>

                        <button
                                type="submit"
                                class="commerce-register__submit"
                            >
                                {{ $isGoogleRegistration
                                    ? 'Completar registro del comercio'
                                    : 'Enviar solicitud de registro' }}
                            </button>

                            <div class="commerce-register__social-divider" aria-hidden="true">
                                <span></span>
                                <small>o</small>
                                <span></span>
                            </div>

                            <a
                                href="{{ route('comercio.google.redirect', ['intent' => 'register']) }}"
                                class="commerce-register__google"
                            >
                                <svg
                                    class="commerce-register__google-icon"
                                    viewBox="0 0 48 48"
                                    aria-hidden="true"
                                >
                                    <path fill="#FFC107" d="M43.611 20.083H42V20H24v8h11.303C33.654 32.657 29.202 36 24 36c-6.627 0-12-5.373-12-12s5.373-12 12-12c3.059 0 5.842 1.154 7.961 3.039l5.657-5.657C34.046 6.053 29.27 4 24 4C12.955 4 4 12.955 4 24s8.955 20 20 20s20-8.955 20-20c0-1.341-.138-2.65-.389-3.917z"/>
                                    <path fill="#FF3D00" d="M6.306 14.691l6.571 4.819C14.655 16.108 19.001 12 24 12c3.059 0 5.842 1.154 7.961 3.039l5.657-5.657C34.046 6.053 29.27 4 24 4c-7.682 0-14.347 4.337-17.694 10.691z"/>
                                    <path fill="#4CAF50" d="M24 44c5.17 0 9.86-1.977 13.409-5.193l-6.19-5.238C29.143 35.091 26.715 36 24 36c-5.181 0-9.624-3.329-11.287-7.946l-6.522 5.025C9.5 39.556 16.227 44 24 44z"/>
                                    <path fill="#1976D2" d="M43.611 20.083H42V20H24v8h11.303c-.793 2.24-2.231 4.166-4.084 5.569c.001-.001 6.19 5.238 6.19 5.238C36.971 39.205 44 34 44 24c0-1.341-.138-2.65-.389-3.917z"/>
                                </svg>

                                <span>Registrar comercio con Google</span>
                            </a>

                            <p class="commerce-register__google-note">
                                Usa esta opción si deseas crear tu acceso con tu cuenta de Google.
                            </p>
                    </form>
                </section>
            </div>
        </section>
    </main>
@endsection

@push('scripts')
    <script
        src="{{ asset('js/commerce-register.js') }}?v={{ filemtime(public_path('js/commerce-register.js')) }}"
        defer
    ></script>
@endpush
