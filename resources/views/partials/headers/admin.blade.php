<header class="petpay-topbar petpay-glass-topbar">
    <a href="{{ route('admin.dashboard') }}" class="petpay-brand">
        <span class="petpay-brand-mark">P</span>
        <span>Petpay Admin</span>
    </a>

    <nav class="petpay-topbar-actions">
        <a href="#" class="petpay-topbar-icon" aria-label="Alertas">
            🔔
            <span>Alertas</span>
        </a>

        <a href="#" class="petpay-topbar-icon" aria-label="Perfil">
            👤
            <span>Perfil</span>
        </a>

        <form method="POST" action="{{ route('admin.logout') }}" style="margin:0;">
            @csrf
            <button type="submit" class="petpay-topbar-icon petpay-topbar-icon-dark" aria-label="Salir">
                ⏻
                <span>Salir</span>
            </button>
        </form>
    </nav>
</header>