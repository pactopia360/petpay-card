@php
    $commerceHeaderUser = auth('comercio')->user();

    $commerceHeaderName = $commerceHeaderUser->business_name
        ?? $commerceHeaderUser->commerce_name
        ?? $commerceHeaderUser->name
        ?? $commerceHeaderUser->full_name
        ?? $commerceHeaderUser->nombre
        ?? $commerceHeaderUser->email
        ?? 'Mi cuenta';
@endphp

<header class="commerce-black-header" aria-label="Encabezado del portal Comercio">
    <div class="commerce-black-header__inner">
        <span class="commerce-black-header__title">
            Panel de comercio
        </span>

        <details class="commerce-black-header__account">
            <summary
                class="commerce-black-header__logo-button"
                aria-label="Abrir opciones de cuenta"
                title="Opciones de cuenta"
            >
                <img
                    src="{{ asset('assets/petpay-card/img/public/Logo-petpay.png') }}"
                    alt="Petpay"
                    class="commerce-black-header__logo"
                >
            </summary>

            <div class="commerce-black-header__menu">
                <div class="commerce-black-header__identity">
                    <strong>{{ $commerceHeaderName }}</strong>
                    <small>Portal Comercio</small>
                </div>

                <a href="{{ route('comercio.dashboard') }}">
                    Ir al panel
                </a>

                <form method="POST" action="{{ route('comercio.logout') }}">
                    @csrf

                    <button type="submit">
                        Cerrar sesión
                    </button>
                </form>
            </div>
        </details>
    </div>
</header>

<script>
    (() => {
        const menu = document.querySelector('.commerce-black-header__account');

        if (!menu) {
            return;
        }

        document.addEventListener('click', (event) => {
            if (menu.open && !menu.contains(event.target)) {
                menu.open = false;
            }
        });

        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape') {
                menu.open = false;
            }
        });
    })();
</script>
