@php($portal = 'admin')

@extends('layouts.app')

@section('title', 'Repartidores pendientes | PETPAY-CARD')

@push('styles')
    <link
        rel="stylesheet"
        href="{{ asset('assets/petpay-card/css/admin/driver-identities.css') }}?v=20260713-5"
    >
@endpush

@section('content')
    <style>
        body:has(.driver-admin-white) {
            background: #ffffff !important;
        }

        .driver-admin-white.petpay-dashboard {
            width: 100% !important;
            max-width: none !important;
            margin: 0 !important;
            grid-template-columns: 56px minmax(0, 1fr) !important;
            gap: 8px !important;
            align-items: start !important;
            min-height: calc(100vh - 72px);
            padding: 24px 28px 28px 8px;
            background:
                radial-gradient(circle at top left, rgba(255, 122, 0, .08), transparent 32%),
                radial-gradient(circle at 94% 10%, rgba(255, 168, 79, .07), transparent 26%),
                #ffffff !important;
            position: relative;
            overflow: visible;
        }

        .driver-admin-white::before {
            content: "";
            position: absolute;
            inset: 0;
            pointer-events: none;
            background-image:
                radial-gradient(circle, rgba(255, 122, 0, .08) 1.3px, transparent 1.3px);
            background-size: 30px 30px;
            opacity: .45;
            mask-image: linear-gradient(90deg, rgba(0, 0, 0, .22), transparent 34%, transparent 100%);
        }

        .driver-admin-white .petpay-sidebar,
        .driver-admin-white .petpay-sidebar-card {
            margin-left: 0 !important;
        }

        .driver-admin-white .petpay-content-panel {
            position: relative;
            z-index: 1;
            width: 100%;
            max-width: none;
            min-height: calc(100vh - 120px);
            padding: 28px;
            border-radius: 34px;
            background: rgba(255, 255, 255, .94) !important;
            border: 1px solid rgba(15, 23, 42, .08) !important;
            box-shadow:
                0 24px 70px rgba(15, 23, 42, .08),
                0 8px 24px rgba(255, 122, 0, .06) !important;
            backdrop-filter: blur(18px);
            -webkit-backdrop-filter: blur(18px);
            align-content: start !important;
        }

        .driver-admin-wrap {
            display: grid;
            gap: 20px;
            align-content: start;
        }

        .driver-admin-top {
            display: grid;
            grid-template-columns: minmax(0, 1fr) auto;
            align-items: center;
            gap: 24px;
            padding: 4px 4px 4px;
        }

        .driver-admin-kicker {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            width: fit-content;
            margin-bottom: 10px;
            padding: 7px 12px;
            border-radius: 999px;
            background: rgba(255, 122, 0, .10);
            color: #f97316;
            font-size: 11px;
            font-weight: 950;
            letter-spacing: .12em;
            text-transform: uppercase;
        }

        .driver-admin-top h1 {
            margin: 0;
            color: #0f172a;
            font-size: clamp(34px, 4vw, 54px);
            line-height: .96;
            letter-spacing: -.055em;
            font-weight: 950;
        }

        .driver-admin-top p {
            max-width: 760px;
            margin: 14px 0 0;
            color: #64748b;
            font-size: 15px;
            line-height: 1.65;
            font-weight: 650;
        }

        .driver-admin-btn {
            min-height: 42px;
            padding: 0 16px;
            border-radius: 999px;
            border: 1px solid rgba(15, 23, 42, .08);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            background: #ffffff;
            color: #0f172a;
            font-size: 12px;
            font-weight: 950;
            text-decoration: none;
            box-shadow: 0 14px 28px rgba(15, 23, 42, .08);
            transition: transform .18s ease, box-shadow .18s ease, border-color .18s ease;
            white-space: nowrap;
        }

        .driver-admin-btn:hover {
            transform: translateY(-2px);
            border-color: rgba(255, 122, 0, .28);
            box-shadow: 0 20px 40px rgba(15, 23, 42, .12);
        }

        .driver-admin-status {
            padding: 13px 15px;
            border-radius: 18px;
            background: rgba(22, 163, 74, .10);
            color: #166534;
            font-size: 13px;
            font-weight: 900;
            border: 1px solid rgba(22, 163, 74, .20);
        }

        .driver-admin-card {
            padding: 22px;
            border-radius: 30px;
            background: #ffffff;
            border: 1px solid rgba(15, 23, 42, .08);
            box-shadow: 0 18px 50px rgba(15, 23, 42, .06);
        }

        .driver-admin-card-head {
            display: flex;
            align-items: flex-end;
            justify-content: space-between;
            gap: 18px;
            margin-bottom: 18px;
        }

        .driver-admin-card h2 {
            margin: 0;
            color: #0f172a;
            font-size: 26px;
            line-height: 1;
            letter-spacing: -.045em;
            font-weight: 950;
        }

        .driver-admin-card p {
            margin: 9px 0 0;
            color: #64748b;
            font-size: 13px;
            line-height: 1.55;
            font-weight: 650;
        }

        .driver-admin-count {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 34px;
            padding: 0 13px;
            border-radius: 999px;
            background: rgba(255, 122, 0, .10);
            color: #c2410c;
            font-size: 12px;
            font-weight: 950;
            white-space: nowrap;
        }

        .driver-admin-list {
            display: grid;
            gap: 12px;
        }

        .driver-admin-row {
            display: grid;
            grid-template-columns: minmax(0, 1fr) auto;
            gap: 16px;
            align-items: center;
            padding: 15px;
            border-radius: 22px;
            background:
                linear-gradient(145deg, #ffffff, #fffaf4);
            border: 1px solid rgba(15, 23, 42, .08);
            box-shadow: 0 12px 30px rgba(15, 23, 42, .045);
        }

        .driver-admin-row-title {
            display: flex;
            align-items: center;
            gap: 11px;
            min-width: 0;
        }

        .driver-admin-row-icon {
            width: 42px;
            height: 42px;
            border-radius: 17px;
            display: grid;
            place-items: center;
            background: #0f172a;
            color: #ffffff;
            flex: 0 0 auto;
            box-shadow: 0 14px 26px rgba(15, 23, 42, .18);
        }

        .driver-admin-row-title strong {
            display: block;
            color: #0f172a;
            font-size: 15px;
            font-weight: 950;
            overflow-wrap: anywhere;
        }

        .driver-admin-row-title span {
            display: block;
            margin-top: 3px;
            color: #64748b;
            font-size: 12px;
            font-weight: 650;
            overflow-wrap: anywhere;
        }

        .driver-admin-tags {
            display: flex;
            flex-wrap: wrap;
            gap: 7px;
            margin-top: 11px;
        }

        .driver-admin-tag {
            padding: 6px 9px;
            border-radius: 999px;
            background: rgba(255, 122, 0, .09);
            color: #c2410c;
            font-size: 10px;
            font-weight: 950;
            border: 1px solid rgba(255, 122, 0, .18);
        }

        .driver-admin-row-actions {
            display: flex;
            gap: 8px;
        }

        .driver-admin-row-actions form {
            margin: 0;
        }

        .driver-admin-icon-btn {
            width: 38px;
            height: 38px;
            border: 0;
            border-radius: 14px;
            color: #ffffff;
            font-size: 16px;
            font-weight: 950;
            cursor: pointer;
            box-shadow: 0 12px 24px rgba(15, 23, 42, .12);
            transition: transform .16s ease, box-shadow .16s ease;
        }

        .driver-admin-icon-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 16px 30px rgba(15, 23, 42, .18);
        }

        .driver-admin-icon-btn--approve {
            background: #16a34a;
        }

        .driver-admin-icon-btn--reject {
            background: #dc2626;
        }

        .driver-admin-empty {
            min-height: 240px;
            display: grid;
            place-items: center;
            text-align: center;
            padding: 28px;
            border-radius: 26px;
            background:
                radial-gradient(circle at top, rgba(255, 122, 0, .09), transparent 50%),
                #ffffff;
            border: 1px dashed rgba(255, 122, 0, .30);
        }

        .driver-admin-empty-icon {
            width: 54px;
            height: 54px;
            margin: 0 auto 12px;
            border-radius: 20px;
            display: grid;
            place-items: center;
            background: #0f172a;
            color: #ffffff;
            font-size: 24px;
            box-shadow: 0 14px 28px rgba(15, 23, 42, .18);
        }

        .driver-admin-empty strong {
            display: block;
            color: #0f172a;
            font-size: 20px;
            letter-spacing: -.035em;
            font-weight: 950;
        }

        .driver-admin-empty p {
            margin: 8px 0 0;
            color: #64748b;
            font-size: 13px;
            line-height: 1.55;
            font-weight: 650;
        }

        .driver-admin-pagination {
            margin-top: 14px;
        }

        @media (max-width: 820px) {
            .driver-admin-white.petpay-dashboard {
                grid-template-columns: 1fr !important;
                gap: 12px !important;
                padding: 18px 14px 92px 14px;
            }

            .driver-admin-white .petpay-content-panel {
                min-height: auto;
                padding: 20px;
                border-radius: 26px;
            }
        }

        @media (max-width: 720px) {
            .driver-admin-top {
                grid-template-columns: 1fr;
            }

            .driver-admin-card-head {
                align-items: flex-start;
                flex-direction: column;
            }

            .driver-admin-row {
                grid-template-columns: 1fr;
            }

            .driver-admin-row-actions {
                justify-content: flex-start;
            }
        }
    </style>

    <section class="petpay-dashboard driver-admin-white">
        @include('partials.sidebars.admin')

        <div class="petpay-content-panel driver-admin-wrap">
            <div class="driver-admin-top">
                <div>
                    <span class="driver-admin-kicker">🛵 Admin / Repartidores</span>

                    <h1>Repartidores pendientes</h1>

                    <p>
                        Revisa repartidores registrados y aprueba o rechaza su acceso operativo.
                        Mantén controlado el acceso de quienes harán entregas dentro de Petpay.
                    </p>
                </div>

                <a href="{{ route('admin.dashboard') }}" class="driver-admin-btn">
                    ← Dashboard
                </a>
            </div>

            <nav
                class="driver-admin-hub"
                aria-label="Procesos administrativos de repartidores"
            >
                <a
                    href="{{ route('admin.drivers.pending') }}"
                    class="driver-admin-hub__item is-active"
                >
                    <span class="driver-admin-hub__icon">🛵</span>

                    <span>
                        <strong>Accesos iniciales</strong>
                        <small>Autoriza el ingreso al portal.</small>
                    </span>

                    <b>{{ number_format($drivers->total()) }}</b>
                </a>

                <a
                    href="{{ route('admin.driver-identities.index') }}"
                    class="driver-admin-hub__item"
                >
                    <span class="driver-admin-hub__icon">🪪</span>

                    <span>
                        <strong>Expedientes y documentos</strong>
                        <small>Revisa documentos y ejecuta validación IA.</small>
                    </span>

                    <b>{{ number_format($identityCount) }}</b>
                </a>

                <a
                    href="{{ route('admin.driver-update-requests.index') }}"
                    class="driver-admin-hub__item"
                >
                    <span class="driver-admin-hub__icon">📝</span>

                    <span>
                        <strong>Solicitudes de actualización</strong>
                        <small>Aprueba o rechaza cambios de datos.</small>
                    </span>

                    <b>{{ number_format($updateRequestCount) }}</b>
                </a>
            </nav>
            @if (session('status'))
                <div class="driver-admin-status">
                    {{ session('status') }}
                </div>
            @endif

            <div class="driver-admin-card">
                <div class="driver-admin-card-head">
                    <div>
                        <h2>Revisión pendiente</h2>

                        <p>
                            Los repartidores pendientes aparecerán aquí para aprobación rápida.
                        </p>
                    </div>

                    <span class="driver-admin-count">
                        {{ number_format($drivers->total()) }} pendiente{{ $drivers->total() === 1 ? '' : 's' }}
                    </span>
                </div>

                <div class="driver-admin-list">
                    @forelse ($drivers as $driver)
                        <article class="driver-admin-row">
                            <div>
                                <div class="driver-admin-row-title">
                                    <div class="driver-admin-row-icon">🛵</div>

                                    <div>
                                        <strong>{{ $driver->name }}</strong>

                                        <span>
                                            {{ $driver->email }} · {{ $driver->phone ?: 'Sin teléfono' }}
                                        </span>
                                    </div>
                                </div>

                                <div class="driver-admin-tags">
                                    <span class="driver-admin-tag">
                                        Zona:
                                        {{ $driver->operation_zone ?: 'Sin zona asignada' }}
                                    </span>

                                    <span class="driver-admin-tag">
                                        Vehículo:
                                        {{ $driver->vehicle_type ?: 'Sin vehículo' }}
                                    </span>

                                    <span class="driver-admin-tag">
                                        Placa:
                                        {{ $driver->vehicle_plate ?: 'Sin placa' }}
                                    </span>

                                    <span class="driver-admin-tag">
                                        Pendiente
                                    </span>

                                    <span class="driver-admin-tag">
                                        {{ $driver->created_at?->format('d/m/Y H:i') ?: 'Sin fecha' }}
                                    </span>
                                </div>
                            </div>

                            <div class="driver-admin-row-actions">
                                <form method="POST" action="{{ route('admin.drivers.approve', $driver) }}">
                                    @csrf
                                    @method('PATCH')

                                    <button type="submit" class="driver-admin-icon-btn driver-admin-icon-btn--approve" title="Aprobar repartidor">
                                        ✓
                                    </button>
                                </form>

                                <form method="POST" action="{{ route('admin.drivers.reject', $driver) }}">
                                    @csrf
                                    @method('PATCH')

                                    <button type="submit" class="driver-admin-icon-btn driver-admin-icon-btn--reject" title="Rechazar repartidor">
                                        ×
                                    </button>
                                </form>
                            </div>
                        </article>
                    @empty
                        <div class="driver-admin-empty">
                            <div>
                                <div class="driver-admin-empty-icon">🛵</div>

                                <strong>No hay repartidores pendientes</strong>

                                <p>
                                    Cuando un repartidor se registre, aparecerá aquí para revisión.
                                </p>
                            </div>
                        </div>
                    @endforelse
                </div>

                @if ($drivers->hasPages())
                    <div class="driver-admin-pagination">
                        {{ $drivers->links() }}
                    </div>
                @endif
            </div>
        </div>
    </section>
@endsection
