@php
    $commerceHeaderUser = auth('comercio')->user();

    $commerceHeaderName = 'Mi cuenta';

    if ($commerceHeaderUser) {
        $commerceHeaderName = $commerceHeaderUser->name
            ?? $commerceHeaderUser->full_name
            ?? $commerceHeaderUser->nombre
            ?? $commerceHeaderUser->business_name
            ?? $commerceHeaderUser->commerce_name
            ?? $commerceHeaderUser->email
            ?? 'Mi cuenta';
    }

    $commerceHeaderInitial = mb_strtoupper(mb_substr($commerceHeaderName, 0, 1));
@endphp

<header class="commerce-shell-header">
    <a href="{{ route('comercio.dashboard') }}" class="commerce-shell-header__brand" aria-label="PETPAY Comercio">
        <img
            src="{{ asset('assets/petpay-card/img/public/Logo-petpay.png') }}"
            alt="Petpay"
            class="commerce-shell-header__logo"
        >
    </a>

    <div class="commerce-shell-header__actions">
        <div class="commerce-shell-account">
            <div class="commerce-shell-account__identity">
                <span class="commerce-shell-account__meta">
                    <strong>{{ $commerceHeaderName }}</strong>
                    <small>Comercio</small>
                </span>
            </div>

            <details class="commerce-shell-account__menu">
                <summary class="commerce-shell-account__avatar-trigger" aria-label="Opciones de cuenta">
                    {{ $commerceHeaderInitial }}
                </summary>

                <div class="commerce-shell-account__panel">
                    <a href="{{ route('comercio.dashboard') }}">
                        Mi panel
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
    </div>
</header>

<script>
    (() => {
        const accountMenu = document.querySelector('.commerce-shell-account__menu');

        if (!accountMenu) {
            return;
        }

        document.addEventListener('click', (event) => {
            if (!accountMenu.open) {
                return;
            }

            if (accountMenu.contains(event.target)) {
                return;
            }

            accountMenu.open = false;
        });

        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape') {
                accountMenu.open = false;
            }
        });
    })();
</script>