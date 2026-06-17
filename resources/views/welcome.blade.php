<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>PETPAY-CARD | Marketplace para mascotas</title>

    <link rel="stylesheet" href="{{ asset('assets/petpay-card/css/public/welcome.css') }}">
</head>
<body>
    <main
        class="petpay-public"
        data-login-url="{{ route('cliente.login') }}"
    >
        <header class="petpay-public__header">
            <a href="{{ route('home') }}" class="petpay-public__brand" aria-label="PETPAY-CARD">
                <span class="petpay-public__brand-mark">🐾</span>
                <span class="petpay-public__brand-text">PETPAY</span>
            </a>

            <nav class="petpay-public__nav" aria-label="Accesos principales">
                <a href="{{ route('cliente.login') }}" class="petpay-public__nav-link petpay-public__nav-link--dark">
                    Comprar
                </a>

                <a href="{{ route('comercio.login') }}" class="petpay-public__nav-link">
                    Vender
                </a>

                <a href="{{ route('repartidor.login') }}" class="petpay-public__nav-link">
                    Repartir
                </a>
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

                <a href="{{ route('cliente.login') }}" class="petpay-public__login">
                    O inicia sesión
                    <span>›</span>
                </a>

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