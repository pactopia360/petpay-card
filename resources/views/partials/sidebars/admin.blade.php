<aside class="petpay-sidebar petpay-glass-sidebar">
    <nav class="petpay-sidebar-card" aria-label="Menú Admin">
        <div class="petpay-sidebar-menu">
            <a href="{{ route('admin.dashboard') }}" class="petpay-sidebar-link" aria-label="Dashboard">
                <span class="petpay-sidebar-icon">⌂</span>
                <span class="petpay-sidebar-text">Dashboard</span>
            </a>

            <a href="{{ route('admin.providers.pending') }}" class="petpay-sidebar-link" aria-label="Proveedores pendientes">
                <span class="petpay-sidebar-icon">🏪</span>
                <span class="petpay-sidebar-text">Proveedores</span>
            </a>

            <a href="{{ route('admin.drivers.pending') }}" class="petpay-sidebar-link" aria-label="Repartidores pendientes">
                <span class="petpay-sidebar-icon">🛵</span>
                <span class="petpay-sidebar-text">Repartidores</span>
            </a>

            <a href="#" class="petpay-sidebar-link" aria-label="Clientes">
                <span class="petpay-sidebar-icon">👤</span>
                <span class="petpay-sidebar-text">Clientes</span>
            </a>

            <a href="#" class="petpay-sidebar-link" aria-label="Órdenes">
                <span class="petpay-sidebar-icon">📦</span>
                <span class="petpay-sidebar-text">Órdenes</span>
            </a>

            <a href="#" class="petpay-sidebar-link" aria-label="Pagos">
                <span class="petpay-sidebar-icon">💳</span>
                <span class="petpay-sidebar-text">Pagos</span>
            </a>

            <a href="#" class="petpay-sidebar-link" aria-label="Configuración">
                <span class="petpay-sidebar-icon">⚙</span>
                <span class="petpay-sidebar-text">Configuración</span>
            </a>
        </div>
    </nav>
</aside>