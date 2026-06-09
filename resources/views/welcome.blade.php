@php($portal = 'public')

@extends('layouts.app')

@section('title', 'PETPAY-CARD | Marketplace para mascotas')

@section('content')
    <section class="petpay-hero">
        <div class="petpay-hero-visual">
            <div class="petpay-pet-illustration">
                🐶
            </div>
        </div>

        <div class="petpay-hero-content">
            <div class="petpay-kicker">🐾 Marketplace pet por zonas</div>

            <h1 class="petpay-title">
                Todo para tu mascota cerca de ti.
            </h1>

            <p class="petpay-subtitle">
                Compra productos y servicios para mascotas, conecta con tiendas cercanas,
                proveedores, veterinarias y repartidores disponibles por zona.
            </p>

            <div class="petpay-search-card">
                <input class="petpay-field" type="text" placeholder="📍 Dirección de entrega">
                <input class="petpay-field" type="text" placeholder="🕘 Entregar ahora">
                <a href="{{ route('cliente.home') }}" class="petpay-btn petpay-btn-black">
                    Buscar ahora
                </a>
            </div>
        </div>
    </section>

    <section class="petpay-portal-grid">
        @include('partials.portal-card', [
            'icon' => '🛒',
            'title' => 'Cliente',
            'description' => 'Compra productos y servicios para tu mascota cerca de tu ubicación.',
            'url' => route('cliente.login'),
        ])

        @include('partials.portal-card', [
            'icon' => '🏪',
            'title' => 'Proveedor',
            'description' => 'Administra tienda, productos, servicios, tickets y ventas.',
            'url' => route('proveedor.login'),
        ])

        @include('partials.portal-card', [
            'icon' => '🛵',
            'title' => 'Repartidor',
            'description' => 'Activa disponibilidad, acepta entregas y consulta tus ingresos.',
            'url' => route('repartidor.login'),
        ])

        @include('partials.portal-card', [
            'icon' => '⚙️',
            'title' => 'Admin',
            'description' => 'Controla usuarios, pedidos, comisiones, pagos y operación.',
            'url' => route('admin.login'),
        ])
    </section>

    <p class="petpay-footer-note">
        PETPAY-CARD · petpay-card.com · Plataforma marketplace pet por zonas
    </p>
@endsection