@php
    $headerUser = null;
    $headerAccountType = null;
    $headerHomeRoute = null;
    $headerLogoutRoute = null;

    if (auth('admin')->check()) {
        $headerUser = auth('admin')->user();
        $headerAccountType = 'Admin';
        $headerHomeRoute = route('admin.home');
        $headerLogoutRoute = route('admin.logout');
    } elseif (auth('comercio')->check()) {
        $headerUser = auth('comercio')->user();
        $headerAccountType = 'Comercio';
        $headerHomeRoute = route('comercio.dashboard');
        $headerLogoutRoute = route('comercio.logout');
    } elseif (auth('cliente')->check()) {
        $headerUser = auth('cliente')->user();
        $headerAccountType = 'Cliente';
        $headerHomeRoute = route('cliente.home');
        $headerLogoutRoute = route('cliente.logout');
    } elseif (auth('repartidor')->check()) {
        $headerUser = auth('repartidor')->user();
        $headerAccountType = 'Repartidor';
        $headerHomeRoute = route('repartidor.home');
        $headerLogoutRoute = route('repartidor.logout');
    }

    $headerDisplayName = null;

    if ($headerUser) {
        $headerDisplayName = $headerUser->name
            ?? $headerUser->full_name
            ?? $headerUser->nombre
            ?? $headerUser->business_name
            ?? $headerUser->commerce_name
            ?? $headerUser->email
            ?? 'Mi cuenta';
    }
@endphp

<header class="petpay-topbar petpay-topbar-public">
    <a href="{{ route('home') }}" class="petpay-brand">
        <span class="petpay-brand-mark">🐾</span>
        <span>PETPAY-CARD</span>
    </a>

    <nav class="petpay-topbar-actions">
        @if ($headerUser)
            <a href="{{ $headerHomeRoute }}" class="petpay-header-account" title="Ir a mi panel">
                <span class="petpay-header-account__avatar">
                    {{ mb_strtoupper(mb_substr($headerDisplayName, 0, 1)) }}
                </span>

                <span class="petpay-header-account__meta">
                    <strong>{{ $headerDisplayName }}</strong>
                    <small>{{ $headerAccountType }}</small>
                </span>
            </a>

            <details class="petpay-header-menu">
                <summary class="petpay-header-menu__trigger" aria-label="Opciones de cuenta">
                    <span></span>
                    <span></span>
                    <span></span>
                </summary>

                <div class="petpay-header-menu__panel">
                    <a href="{{ $headerHomeRoute }}">Mi panel</a>

                    <form method="POST" action="{{ $headerLogoutRoute }}">
                        @csrf

                        <button type="submit">
                            Cerrar sesión
                        </button>
                    </form>
                </div>
            </details>
        @else
            <a href="{{ route('cliente.home') }}" class="petpay-btn petpay-btn-black">Comprar</a>
            <a href="{{ route('comercio.home') }}" class="petpay-btn petpay-btn-orange">Vender</a>
            <a href="{{ route('repartidor.home') }}" class="petpay-btn petpay-btn-orange">Repartir</a>
            <a href="{{ route('admin.home') }}" class="petpay-btn petpay-btn-white">Admin</a>
        @endif
    </nav>
</header>