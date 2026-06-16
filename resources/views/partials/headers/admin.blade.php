<header class="admin-shell-header">
    <div class="admin-shell-header__brand">
        <a href="{{ route('admin.dashboard') }}" class="admin-shell-brand" title="Dashboard Admin">
            <span class="admin-shell-brand__mark">P</span>
            <span class="admin-shell-brand__text">Petpay Admin</span>
        </a>
    </div>

    <nav class="admin-shell-menu" aria-label="Menú Admin">
        <a
            href="{{ route('admin.dashboard') }}"
            class="admin-shell-menu__link {{ request()->routeIs('admin.dashboard') ? 'is-active' : '' }}"
            title="Dashboard"
        >
            <span>⌂</span>
            <strong>Dashboard</strong>
        </a>

        <a
            href="{{ route('admin.commerces.pending') }}"
            class="admin-shell-menu__link {{ request()->routeIs('admin.commerces.*') ? 'is-active' : '' }}"
            title="Comercios"
        >
            <span>🏪</span>
            <strong>Comercios</strong>
        </a>

        <a
            href="{{ route('admin.providers.pending') }}"
            class="admin-shell-menu__link {{ request()->routeIs('admin.providers.*') ? 'is-active' : '' }}"
            title="Proveedor reservado"
        >
            <span>📦</span>
            <strong>Proveedor</strong>
        </a>

        <a
            href="{{ route('admin.drivers.pending') }}"
            class="admin-shell-menu__link {{ request()->routeIs('admin.drivers.*') ? 'is-active' : '' }}"
            title="Repartidores pendientes"
        >
            <span>🛵</span>
            <strong>Repartidores</strong>
        </a>
    </nav>

    <div class="admin-shell-actions">
        <a href="#" class="admin-shell-action" title="Alertas" aria-label="Alertas">
            🔔
        </a>

        <a href="#" class="admin-shell-action" title="Perfil" aria-label="Perfil">
            👤
        </a>

        <form method="POST" action="{{ route('admin.logout') }}" class="admin-shell-logout">
            @csrf
            <button type="submit" class="admin-shell-action admin-shell-action--dark" title="Salir" aria-label="Salir">
                ⏻
            </button>
        </form>
    </div>
</header>