@php
    $driver = auth('repartidor')->user();
    $driverName = trim(($driver->first_name ?? '').' '.($driver->last_name ?? ''));
    $driverName = $driverName !== '' ? $driverName : ($driver->name ?? $driver->email ?? 'Mi cuenta');
    $driverStatus = match ($driver->approval_status ?? $driver->status ?? 'pending') {
        'approved', 'active' => 'Cuenta aprobada',
        'rejected' => 'Cuenta rechazada',
        'suspended' => 'Cuenta suspendida',
        default => 'Cuenta en revisión',
    };
@endphp

<header class="driver-black-header">
    <div class="driver-black-header__inner">
        <a href="{{ route('repartidor.dashboard') }}" class="driver-black-header__brand" title="Ir al inicio">
            <img src="{{ asset('assets/petpay-card/img/public/Logo-petpay.png') }}" alt="Petpay">
            <span><strong>Panel de reparto</strong><small>Operación y entregas</small></span>
        </a>

        <div class="driver-black-header__right">
            <div class="driver-black-header__identity">
                <strong>{{ $driverName }}</strong>
                <small>{{ $driverStatus }}</small>
            </div>

            <details class="driver-black-header__account" data-driver-account-menu>
                <summary class="driver-black-header__logo-button" title="Opciones de cuenta">
                    <img src="{{ asset('assets/petpay-card/img/public/Logo-petpay.png') }}" alt="Petpay">
                </summary>

                <div class="driver-black-header__menu">
                    <div class="driver-black-header__menu-identity">
                        <strong>{{ $driverName }}</strong>
                        <small>Portal Repartidor</small>
                    </div>
                    <a href="{{ route('repartidor.dashboard') }}">Ir al panel</a>
                    <form method="POST" action="{{ route('repartidor.logout') }}">
                        @csrf
                        <button type="submit">Cerrar sesión</button>
                    </form>
                </div>
            </details>
        </div>
    </div>
</header>
