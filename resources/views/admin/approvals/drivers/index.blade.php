@php($portal = 'admin')

@extends('layouts.app')

@section('title', 'Repartidores pendientes | PETPAY-CARD')

@section('content')
<section class="petpay-shell">
    @include('partials.sidebars.admin')

    <section class="petpay-content">
        <div class="petpay-panel">
            <div class="petpay-section-head">
                <div>
                    <span class="petpay-kicker">Admin</span>
                    <h1>Repartidores pendientes</h1>
                    <p>Revisa repartidores registrados y aprueba o rechaza su acceso operativo.</p>
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
                            <th>Repartidor</th>
                            <th>Contacto</th>
                            <th>Vehículo</th>
                            <th>Estado</th>
                            <th>Registro</th>
                            <th class="petpay-table-actions">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($drivers as $driver)
                            <tr>
                                <td>
                                    <strong>{{ $driver->name }}</strong>
                                    <span>{{ $driver->operation_zone ?: 'Sin zona asignada' }}</span>
                                </td>
                                <td>
                                    <strong>{{ $driver->email }}</strong>
                                    <span>{{ $driver->phone ?: 'Sin teléfono' }}</span>
                                </td>
                                <td>
                                    <strong>{{ $driver->vehicle_type ?: 'Sin vehículo' }}</strong>
                                    <span>{{ $driver->vehicle_plate ?: 'Sin placa' }}</span>
                                </td>
                                <td>
                                    <span class="petpay-status petpay-status-pending">
                                        Pendiente
                                    </span>
                                </td>
                                <td>{{ $driver->created_at?->format('d/m/Y H:i') }}</td>
                                <td>
                                    <div class="petpay-row-actions">
                                        <form method="POST" action="{{ route('admin.drivers.approve', $driver) }}">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="petpay-icon-btn petpay-icon-btn-success" title="Aprobar repartidor">
                                                ✓
                                            </button>
                                        </form>

                                        <form method="POST" action="{{ route('admin.drivers.reject', $driver) }}">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="petpay-icon-btn petpay-icon-btn-danger" title="Rechazar repartidor">
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
                                        No hay repartidores pendientes por ahora.
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="petpay-pagination">
                {{ $drivers->links() }}
            </div>
        </div>
    </section>
</section>
@endsection