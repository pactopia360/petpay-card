<header class="petpay-topbar petpay-topbar-public">
    <a href="{{ route('home') }}" class="petpay-brand">
        <span class="petpay-brand-mark">🐾</span>
        <span>PETPAY-CARD</span>
    </a>

    <nav class="petpay-topbar-actions">
        <a href="{{ route('cliente.home') }}" class="petpay-btn petpay-btn-black">Comprar</a>
        <a href="{{ route('proveedor.home') }}" class="petpay-btn petpay-btn-orange">Vender</a>
        <a href="{{ route('repartidor.home') }}" class="petpay-btn petpay-btn-orange">Repartir</a>
        <a href="{{ route('admin.home') }}" class="petpay-btn petpay-btn-white">Admin</a>
    </nav>
</header>