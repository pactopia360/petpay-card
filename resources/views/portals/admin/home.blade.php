@php($portal = 'admin')

@extends('layouts.app')

@section('title', 'PETPAY-CARD | Admin')

@section('content')
    <section class="petpay-dashboard">
        @include('partials.sidebars.admin')

        <div class="petpay-content-panel">
            <div class="petpay-section-head">
                <div>
                    <span class="petpay-kicker">Panel central</span>
                    <h1>Portal Admin</h1>
                    <p>
                        Administra clientes, proveedores, repartidores, pedidos,
                        pagos, PawPoints, Petpay Plus, zonas y operación general.
                    </p>
                </div>
            </div>

            <div class="petpay-stat-grid">
                <a href="{{ route('admin.providers.pending') }}" class="petpay-stat petpay-stat-link">
                    <strong>{{ number_format($metrics['providers_pending'] ?? 0) }}</strong>
                    <span>Proveedores pendientes</span>
                </a>

                <a href="{{ route('admin.drivers.pending') }}" class="petpay-stat petpay-stat-link">
                    <strong>{{ number_format($metrics['drivers_pending'] ?? 0) }}</strong>
                    <span>Repartidores pendientes</span>
                </a>

                <div class="petpay-stat">
                    <strong>{{ number_format($metrics['customers_total'] ?? 0) }}</strong>
                    <span>Clientes registrados</span>
                </div>

                <div class="petpay-stat">
                    <strong>{{ number_format($metrics['providers_approved'] ?? 0) }}</strong>
                    <span>Proveedores aprobados</span>
                </div>

                <div class="petpay-stat">
                    <strong>{{ number_format($metrics['drivers_approved'] ?? 0) }}</strong>
                    <span>Repartidores aprobados</span>
                </div>

                <div class="petpay-stat">
                    <strong>{{ number_format($metrics['orders_today'] ?? 0) }}</strong>
                    <span>Órdenes de hoy</span>
                </div>

                <div class="petpay-stat">
                    <strong>{{ number_format($metrics['payments_today'] ?? 0) }}</strong>
                    <span>Pagos de hoy</span>
                </div>

                <div class="petpay-stat">
                    <strong>{{ number_format($metrics['active_deliveries'] ?? 0) }}</strong>
                    <span>Entregas activas</span>
                </div>
            </div>

            <div class="petpay-panel petpay-mt-18">
                <div class="petpay-section-head">
                    <div>
                        <span class="petpay-kicker">Acciones rápidas</span>
                        <h2>Operación pendiente</h2>
                        <p>
                            Revisa aprobaciones y mantén activa la operación de proveedores y repartidores.
                        </p>
                    </div>
                </div>

                <div class="petpay-action-grid">
                    <a href="{{ route('admin.providers.pending') }}" class="petpay-action-card">
                        <strong>🏪 Proveedores</strong>
                        <span>Aprobar o rechazar negocios registrados.</span>
                    </a>

                    <a href="{{ route('admin.drivers.pending') }}" class="petpay-action-card">
                        <strong>🛵 Repartidores</strong>
                        <span>Aprobar o rechazar repartidores registrados.</span>
                    </a>

                    <a href="#" class="petpay-action-card">
                        <strong>📦 Órdenes</strong>
                        <span>Próximo módulo operativo de pedidos.</span>
                    </a>

                    <a href="#" class="petpay-action-card">
                        <strong>💳 Pagos</strong>
                        <span>Próximo módulo de pagos, comisiones y cortes.</span>
                    </a>
                </div>
            </div>
        </div>
    </section>
@endsection