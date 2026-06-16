@extends('layouts.guest')

@section('title', 'PETPAY-CARD | Registro Comercio')

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
            <div class="petpay-auth__panel petpay-auth__panel--wide">
                <div class="petpay-auth__intro">
                    <p class="petpay-auth__eyebrow">Vende en Petpay</p>
                    <h1>Registra tu comercio</h1>
                    <p>
                        Da de alta tu negocio para vender productos o servicios para mascotas
                        dentro de la plataforma Petpay.
                    </p>
                </div>

                @if ($errors->any())
                    <div class="petpay-auth__alert petpay-auth__alert--danger">
                        {{ $errors->first() }}
                    </div>
                @endif

                <form method="POST" action="{{ route('comercio.register.store') }}" class="petpay-auth__form">
                    @csrf

                    <div class="petpay-auth__form-grid">
                        <div class="petpay-auth__group">
                            <label class="petpay-auth__label" for="first_name">
                                Nombre del responsable
                            </label>
                            <input
                                id="first_name"
                                class="petpay-auth__field"
                                type="text"
                                name="first_name"
                                value="{{ old('first_name') }}"
                                placeholder="Ej. Marco"
                                autocomplete="given-name"
                                required
                                autofocus
                            >
                        </div>

                        <div class="petpay-auth__group">
                            <label class="petpay-auth__label" for="last_name">
                                Apellidos
                            </label>
                            <input
                                id="last_name"
                                class="petpay-auth__field"
                                type="text"
                                name="last_name"
                                value="{{ old('last_name') }}"
                                placeholder="Ej. Hernández"
                                autocomplete="family-name"
                            >
                        </div>

                        <div class="petpay-auth__group">
                            <label class="petpay-auth__label" for="business_name">
                                Nombre comercial
                            </label>
                            <input
                                id="business_name"
                                class="petpay-auth__field"
                                type="text"
                                name="business_name"
                                value="{{ old('business_name') }}"
                                placeholder="Ej. Pet Shop Patitas"
                                required
                            >
                        </div>

                        <div class="petpay-auth__group">
                            <label class="petpay-auth__label" for="business_type">
                                Tipo de comercio
                            </label>
                            <select
                                id="business_type"
                                class="petpay-auth__field"
                                name="business_type"
                                required
                            >
                                <option value="">Selecciona una opción</option>
                                <option value="pet_shop" @selected(old('business_type') === 'pet_shop')>Pet shop</option>
                                <option value="veterinaria" @selected(old('business_type') === 'veterinaria')>Veterinaria</option>
                                <option value="estetica" @selected(old('business_type') === 'estetica')>Estética / Grooming</option>
                                <option value="entrenamiento" @selected(old('business_type') === 'entrenamiento')>Entrenamiento</option>
                                <option value="hotel_guarderia" @selected(old('business_type') === 'hotel_guarderia')>Hotel / Guardería</option>
                                <option value="otro" @selected(old('business_type') === 'otro')>Otro</option>
                            </select>
                        </div>

                        <div class="petpay-auth__group">
                            <label class="petpay-auth__label" for="email">
                                Correo de acceso
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
                            >
                        </div>

                        <div class="petpay-auth__group">
                            <label class="petpay-auth__label" for="phone">
                                Teléfono / WhatsApp del responsable
                            </label>
                            <input
                                id="phone"
                                class="petpay-auth__field"
                                type="tel"
                                name="phone"
                                value="{{ old('phone') }}"
                                placeholder="Ej. 5512345678"
                                autocomplete="tel"
                                required
                            >
                        </div>

                        <div class="petpay-auth__group">
                            <label class="petpay-auth__label" for="business_phone">
                                Teléfono del comercio
                            </label>
                            <input
                                id="business_phone"
                                class="petpay-auth__field"
                                type="tel"
                                name="business_phone"
                                value="{{ old('business_phone') }}"
                                placeholder="Opcional"
                            >
                        </div>

                        <div class="petpay-auth__group">
                            <label class="petpay-auth__label" for="business_email">
                                Correo del comercio
                            </label>
                            <input
                                id="business_email"
                                class="petpay-auth__field"
                                type="email"
                                name="business_email"
                                value="{{ old('business_email') }}"
                                placeholder="Opcional"
                            >
                        </div>

                        <div class="petpay-auth__group petpay-auth__group--full">
                            <label class="petpay-auth__label" for="business_address">
                                Dirección del comercio
                            </label>
                            <textarea
                                id="business_address"
                                class="petpay-auth__field petpay-auth__textarea"
                                name="business_address"
                                rows="3"
                                placeholder="Calle, número, colonia, municipio, estado y código postal"
                                required
                            >{{ old('business_address') }}</textarea>
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

                    <div class="petpay-auth__options">
                        <label class="petpay-auth__check-card">
                            <input
                                type="checkbox"
                                name="sells_products"
                                value="1"
                                @checked(old('sells_products', true))
                            >
                            <span>
                                <strong>Vendo productos</strong>
                                <small>Alimento, accesorios, juguetes, higiene y más.</small>
                            </span>
                        </label>

                        <label class="petpay-auth__check-card">
                            <input
                                type="checkbox"
                                name="offers_services"
                                value="1"
                                @checked(old('offers_services'))
                            >
                            <span>
                                <strong>Ofrezco servicios</strong>
                                <small>Veterinaria, estética, entrenamiento, hotel o guardería.</small>
                            </span>
                        </label>

                        <label class="petpay-auth__check-card">
                            <input
                                type="checkbox"
                                name="has_own_delivery"
                                value="1"
                                @checked(old('has_own_delivery'))
                            >
                            <span>
                                <strong>Tengo entrega propia</strong>
                                <small>Mi comercio puede realizar entregas directamente.</small>
                            </span>
                        </label>

                        <label class="petpay-auth__check-card">
                            <input
                                type="checkbox"
                                name="uses_petpay_delivery"
                                value="1"
                                @checked(old('uses_petpay_delivery', true))
                            >
                            <span>
                                <strong>Quiero usar repartidores Petpay</strong>
                                <small>Petpay podrá conectar pedidos con repartidores.</small>
                            </span>
                        </label>
                    </div>

                    <button type="submit" class="petpay-auth__primary">
                        Registrar comercio
                    </button>
                </form>

                <p class="petpay-auth__legal">
                    Al registrarte, tu comercio quedará pendiente de revisión. Admin validará
                    la información antes de activar tu acceso para vender en Petpay.
                </p>

                <div class="petpay-auth__links">
                    <a href="{{ route('comercio.login') }}">
                        Ya tengo cuenta de comercio
                    </a>
                </div>
            </div>
        </section>
    </main>
@endsection