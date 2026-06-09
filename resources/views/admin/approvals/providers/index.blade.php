@php($portal = 'admin')

@extends('layouts.app')

@section('title', 'Proveedores pendientes | PETPAY-CARD')

@section('content')
<section class="petpay-shell">
    @include('partials.sidebars.admin')

    <section class="petpay-content">
        <div class="petpay-panel">
            <div class="petpay-section-head">
                <div>
                    <span class="petpay-kicker">Admin</span>
                    <h1>Proveedores pendientes</h1>
                    <p>Revisa negocios registrados y aprueba o rechaza su acceso al portal proveedor.</p>
                </div>

                <a href="{{ route('admin.dashboard') }}" class="petpay-btn petpay-btn-white">
                    ← Dashboard
                </a>
            </div>

            @if (session('status'))
                <div class="petpay-alert petpay-alert-success">
                    {{ session('status') }}
                </div>
            @endif

            <div class="petpay-table-wrap">
                <table class="petpay-table">
                    <thead>
                        <tr>
                            <th>Negocio</th>
                            <th>Contacto</th>
                            <th>Tipo</th>
                            <th>Estado</th>
                            <th>Registro</th>
                            <th class="petpay-table-actions">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($providers as $provider)
                            <tr>
                                <td>
                                    <strong>{{ $provider->business_name }}</strong>
                                    <span>{{ $provider->business_address ?: 'Sin dirección' }}</span>
                                </td>
                                <td>
                                    <strong>{{ $provider->email }}</strong>
                                    <span>{{ $provider->phone ?: 'Sin teléfono' }}</span>
                                </td>
                                <td>{{ $provider->business_type ?: 'Sin tipo' }}</td>
                                <td>
                                    <span class="petpay-status petpay-status-pending">
                                        Pendiente
                                    </span>
                                </td>
                                <td>{{ $provider->created_at?->format('d/m/Y H:i') }}</td>
                                <td>
                                    <div class="petpay-row-actions">
                                        <form method="POST" action="{{ route('admin.providers.approve', $provider) }}">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="petpay-icon-btn petpay-icon-btn-success" title="Aprobar proveedor">
                                                ✓
                                            </button>
                                        </form>

                                        <form method="POST" action="{{ route('admin.providers.reject', $provider) }}">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="petpay-icon-btn petpay-icon-btn-danger" title="Rechazar proveedor">
                                                ×
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6">
                                    <div class="petpay-empty">
                                        No hay proveedores pendientes por ahora.
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="petpay-pagination">
                {{ $providers->links() }}
            </div>
        </div>
    </section>
</section>
@endsection