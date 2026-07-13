<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>PETPAY-CARD | Marketplace para mascotas</title>

    <link rel="stylesheet" href="{{ asset('assets/petpay-card/css/public/welcome.css') }}">
    <style>
        .petpay-access { position: relative; }
        .petpay-access__trigger { appearance: none; border: 0; cursor: pointer; font: inherit; }
        .petpay-access__panel { position: absolute; z-index: 50; top: calc(100% + 10px); right: 0; width: 250px; padding: 10px; border: 1px solid rgba(17,17,17,.1); border-radius: 18px; background: #fff; box-shadow: 0 18px 50px rgba(17,17,17,.18); }
        .petpay-access__panel[hidden] { display: none; }
        .petpay-access__title { margin: 2px 8px 8px; color: #686868; font-size: 12px; font-weight: 700; letter-spacing: .08em; text-transform: uppercase; }
        .petpay-access__option { display: flex; align-items: center; gap: 10px; padding: 11px 12px; border-radius: 12px; color: #171717; text-decoration: none; font-weight: 700; }
        .petpay-access__option:hover, .petpay-access__option:focus-visible { background: #fff0e6; color: #e85d04; outline: none; }
        .petpay-access__icon { display: grid; width: 34px; height: 34px; place-items: center; border-radius: 10px; background: #fff0e6; }
        @media (max-width: 760px) { .petpay-public__nav { gap: 6px; } .petpay-access__panel { position: fixed; top: 76px; right: 14px; left: 14px; width: auto; } }
    </style>
</head>
<body>
    <main
        class="petpay-public"
        data-login-url="{{ route('cliente.login') }}"
    >
                @php
            $publicHeaderUser = null;
            $publicHeaderAccountType = null;
            $publicHeaderHomeRoute = null;
            $publicHeaderLogoutRoute = null;

            if (auth('admin')->check()) {
                $publicHeaderUser = auth('admin')->user();
                $publicHeaderAccountType = 'Admin';
                $publicHeaderHomeRoute = route('admin.dashboard');
                $publicHeaderLogoutRoute = route('admin.logout');
            } elseif (auth('comercio')->check()) {
                $publicHeaderUser = auth('comercio')->user();
                $publicHeaderAccountType = 'Comercio';
                $publicHeaderHomeRoute = route('comercio.dashboard');
                $publicHeaderLogoutRoute = route('comercio.logout');
            } elseif (auth('cliente')->check()) {
                $publicHeaderUser = auth('cliente')->user();
                $publicHeaderAccountType = 'Cliente';
                $publicHeaderHomeRoute = route('cliente.dashboard');
                $publicHeaderLogoutRoute = route('cliente.logout');
            } elseif (auth('repartidor')->check()) {
                $publicHeaderUser = auth('repartidor')->user();
                $publicHeaderAccountType = 'Repartidor';
                $publicHeaderHomeRoute = route('repartidor.dashboard');
                $publicHeaderLogoutRoute = route('repartidor.logout');
            }

            $publicHeaderDisplayName = null;

            if ($publicHeaderUser) {
                $publicHeaderDisplayName = $publicHeaderUser->name
                    ?? $publicHeaderUser->full_name
                    ?? $publicHeaderUser->nombre
                    ?? $publicHeaderUser->business_name
                    ?? $publicHeaderUser->commerce_name
                    ?? $publicHeaderUser->email
                    ?? 'Mi cuenta';
            }

            $publicHeaderInitial = $publicHeaderDisplayName
                ? mb_strtoupper(mb_substr($publicHeaderDisplayName, 0, 1))
                : 'P';
        @endphp

        <header class="petpay-public__header">
            <a href="{{ route('home') }}" class="petpay-public__brand" aria-label="PETPAY-CARD">
                <span class="petpay-public__brand-mark">🐾</span>
                <span class="petpay-public__brand-text">PETPAY</span>
            </a>

            <nav class="petpay-public__nav" aria-label="Accesos principales">
                @if ($publicHeaderUser)
                    <a href="{{ $publicHeaderHomeRoute }}" class="petpay-public-account" title="Ir a mi panel">
                        <span class="petpay-public-account__avatar">
                            {{ $publicHeaderInitial }}
                        </span>

                        <span class="petpay-public-account__meta">
                            <strong>{{ $publicHeaderDisplayName }}</strong>
                            <small>{{ $publicHeaderAccountType }}</small>
                        </span>
                    </a>

                    <details class="petpay-public-account-menu">
                        <summary class="petpay-public-account-menu__trigger" aria-label="Opciones de cuenta">
                            <span></span>
                            <span></span>
                            <span></span>
                        </summary>

                        <div class="petpay-public-account-menu__panel">
                            <a href="{{ $publicHeaderHomeRoute }}">Mi panel</a>

                            <form method="POST" action="{{ $publicHeaderLogoutRoute }}">
                                @csrf

                                <button type="submit">
                                    Cerrar sesión
                                </button>
                            </form>
                        </div>
                    </details>
                @else
                    <div class="petpay-access">
                        <button type="button" class="petpay-public__nav-link petpay-access__trigger" data-access-trigger="login" aria-expanded="false">
                            Iniciar sesión
                        </button>
                        <div class="petpay-access__panel" data-access-panel="login" hidden>
                            <p class="petpay-access__title">Ingresar como</p>
                            <a class="petpay-access__option" href="{{ route('cliente.login') }}"><span class="petpay-access__icon">🐾</span>Usuario</a>
                            <a class="petpay-access__option" href="{{ route('comercio.login') }}"><span class="petpay-access__icon">🏪</span>Comercio</a>
                            <a class="petpay-access__option" href="{{ route('repartidor.login') }}"><span class="petpay-access__icon">🛵</span>Repartidor</a>
                        </div>
                    </div>
                    <div class="petpay-access">
                        <button type="button" class="petpay-public__nav-link petpay-public__nav-link--dark petpay-access__trigger" data-access-trigger="register" aria-expanded="false">
                            Registrarse
                        </button>
                        <div class="petpay-access__panel" data-access-panel="register" hidden>
                            <p class="petpay-access__title">Crear cuenta como</p>
                            <a class="petpay-access__option" href="{{ route('cliente.register') }}"><span class="petpay-access__icon">🐾</span>Usuario</a>
                            <a class="petpay-access__option" href="{{ route('comercio.register') }}"><span class="petpay-access__icon">🏪</span>Comercio</a>
                            <a class="petpay-access__option" href="{{ route('repartidor.register') }}"><span class="petpay-access__icon">🛵</span>Repartidor</a>
                        </div>
                    </div>
                @endif
            </nav>
        </header>

        <section class="petpay-public__hero">
            <div class="petpay-public__visual" aria-hidden="true">
                <img
                    src="{{ asset('assets/petpay-card/img/public/hero-petpay.png') }}"
                    alt=""
                    class="petpay-public__hero-image"
                >
            </div>

            <div class="petpay-public__content">
                <p class="petpay-public__kicker">
                    🐾 Marketplace pet por zonas
                </p>

                <h1 class="petpay-public__title">
                    Encuentra todo lo que necesitas para tu mascota cerca de ti.
                </h1>

                <p class="petpay-public__subtitle">
                    Compra productos y servicios para mascotas, conecta con comercios,
                    veterinarias y repartidores disponibles por zona.
                </p>

                <form class="petpay-public__search" onsubmit="return false;">
                    <label class="petpay-public__field-wrap" for="petpayDeliveryAddress">
                        <span class="petpay-public__field-icon">📍</span>

                        <input
                            id="petpayDeliveryAddress"
                            class="petpay-public__field"
                            type="text"
                            placeholder="Ingresa la dirección de entrega"
                            autocomplete="street-address"
                        >

                        <span class="petpay-public__field-required">*</span>
                    </label>

                    <button
                        type="button"
                        id="petpayUseLocation"
                        class="petpay-public__gps"
                    >
                        <span class="petpay-public__gps-icon">📡</span>
                        <span class="petpay-public__gps-text">Usar mi ubicación actual</span>
                    </button>

                    <label class="petpay-public__field-wrap" for="petpayDeliveryTime">
                        <span class="petpay-public__field-icon">🕘</span>

                        <input
                            id="petpayDeliveryTime"
                            class="petpay-public__field"
                            type="text"
                            value="Entregar ahora"
                            readonly
                        >

                        <span class="petpay-public__field-required">*</span>
                    </label>

                    <div
                        id="petpayLocationStatus"
                        class="petpay-public__location-status"
                        aria-live="polite"
                    ></div>

                    <button
                        type="button"
                        id="petpayFindNow"
                        class="petpay-public__cta"
                    >
                        Buscar ahora
                    </button>
                </form>

                <button type="button" class="petpay-public__login petpay-access__trigger" data-access-trigger="login">
                    O inicia sesión
                    <span>›</span>
                </button>

                <div class="petpay-public__mobile-links" aria-label="Accesos móviles">
                    <a href="{{ route('cliente.login') }}">Comprar</a>
                    <a href="{{ route('comercio.login') }}">Vender</a>
                    <a href="{{ route('repartidor.login') }}">Repartir</a>
                </div>
            </div>
        </section>
    </main>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const page = document.querySelector('.petpay-public');
            const addressInput = document.getElementById('petpayDeliveryAddress');
            const findButton = document.getElementById('petpayFindNow');
            const gpsButton = document.getElementById('petpayUseLocation');
            const statusBox = document.getElementById('petpayLocationStatus');

            const loginUrl = page?.dataset?.loginUrl || '/cliente/login';
            const accessTriggers = document.querySelectorAll('[data-access-trigger]');
            const accessPanels = document.querySelectorAll('[data-access-panel]');

            function closeAccessPanels() {
                accessPanels.forEach(panel => panel.hidden = true);
                accessTriggers.forEach(trigger => trigger.setAttribute('aria-expanded', 'false'));
            }

            accessTriggers.forEach(function (trigger) {
                trigger.addEventListener('click', function (event) {
                    event.stopPropagation();
                    const panel = document.querySelector('[data-access-panel="' + trigger.dataset.accessTrigger + '"]');
                    const mustOpen = panel && panel.hidden;
                    closeAccessPanels();
                    if (mustOpen) {
                        panel.hidden = false;
                        document.querySelectorAll('[data-access-trigger="' + trigger.dataset.accessTrigger + '"]').forEach(item => item.setAttribute('aria-expanded', 'true'));
                    }
                });
            });

            document.addEventListener('click', closeAccessPanels);
            document.addEventListener('keydown', function (event) {
                if (event.key === 'Escape') closeAccessPanels();
            });

            function setStatus(message, type = '') {
                if (!statusBox) {
                    return;
                }

                statusBox.textContent = message || '';
                statusBox.dataset.type = type;
            }

            function goToLogin(params = {}) {
                const url = new URL(loginUrl, window.location.origin);

                if (addressInput && addressInput.value.trim() !== '') {
                    url.searchParams.set('direccion', addressInput.value.trim());
                }

                if (params.lat && params.lng) {
                    url.searchParams.set('lat', params.lat);
                    url.searchParams.set('lng', params.lng);
                    url.searchParams.set('gps', '1');
                }

                window.location.href = url.toString();
            }

            function requestLocation(redirectAfterSuccess = false) {
                if (!navigator.geolocation) {
                    setStatus('Tu navegador no permite usar GPS. Escribe tu dirección manualmente.', 'error');
                    return;
                }

                setStatus('Detectando tu ubicación...', 'loading');

                navigator.geolocation.getCurrentPosition(
                    function (position) {
                        const lat = position.coords.latitude.toFixed(7);
                        const lng = position.coords.longitude.toFixed(7);

                        if (addressInput && addressInput.value.trim() === '') {
                            addressInput.value = 'Ubicación detectada por GPS';
                        }

                        setStatus('Ubicación detectada. Ya podemos buscar comercios cercanos.', 'success');

                        if (redirectAfterSuccess) {
                            window.setTimeout(function () {
                                goToLogin({ lat, lng });
                            }, 450);
                        }
                    },
                    function () {
                        setStatus('No pudimos obtener tu ubicación. Puedes escribir tu dirección manualmente.', 'error');
                    },
                    {
                        enableHighAccuracy: true,
                        timeout: 9000,
                        maximumAge: 60000
                    }
                );
            }

            if (gpsButton) {
                gpsButton.addEventListener('click', function () {
                    requestLocation(false);
                });
            }

            if (findButton) {
                findButton.addEventListener('click', function () {
                    if (addressInput && addressInput.value.trim() !== '') {
                        goToLogin();
                        return;
                    }

                    requestLocation(true);
                });
            }
        });
    </script>
</body>
</html>