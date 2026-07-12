@php($portal = 'admin')

@extends('layouts.app')

@section('title', 'PETPAY-CARD | Admin')

@section('content')
    <style>
        body:has(.petpay-admin-dashboard-white) {
            background: #ffffff !important;
        }

        .petpay-admin-dashboard-white {
            min-height: calc(100vh - 72px);
            background:
                radial-gradient(circle at top left, rgba(255, 122, 0, .10), transparent 34%),
                radial-gradient(circle at 92% 8%, rgba(255, 168, 79, .10), transparent 28%),
                #ffffff !important;
            padding: 24px 28px 28px 8px;
            position: relative;
            overflow: visible;
        }

        .petpay-admin-dashboard-white::before {
            content: "";
            position: absolute;
            inset: 0;
            pointer-events: none;
            background-image:
                radial-gradient(circle, rgba(255, 122, 0, .10) 1.4px, transparent 1.4px);
            background-size: 28px 28px;
            opacity: .55;
            mask-image: linear-gradient(90deg, rgba(0,0,0,.28), transparent 38%, transparent 100%);
        }

        .petpay-admin-dashboard-white .petpay-content-panel {
            position: relative;
            z-index: 1;
            background: rgba(255, 255, 255, .92) !important;
            border: 1px solid rgba(15, 23, 42, .08);
            border-radius: 34px;
            box-shadow:
                0 24px 70px rgba(15, 23, 42, .08),
                0 8px 24px rgba(255, 122, 0, .08);
            backdrop-filter: blur(18px);
            min-height: calc(100vh - 120px);
            padding: 30px;
        }

        .petpay-admin-hero {
            display: grid;
            grid-template-columns: minmax(0, 1fr) auto;
            gap: 24px;
            align-items: center;
            padding: 10px 4px 24px;
        }

        .petpay-admin-eyebrow {
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
            font-weight: 900;
            letter-spacing: .12em;
            text-transform: uppercase;
        }

        .petpay-admin-hero h1 {
            margin: 0;
            color: #0f172a;
            font-size: clamp(34px, 4vw, 54px);
            line-height: .96;
            letter-spacing: -.055em;
            font-weight: 950;
        }

        .petpay-admin-hero p {
            max-width: 820px;
            margin: 14px 0 0;
            color: #64748b;
            font-size: 15px;
            line-height: 1.65;
            font-weight: 650;
        }

        .petpay-admin-hero-card {
            min-width: 280px;
            border-radius: 28px;
            padding: 20px;
            background:
                linear-gradient(135deg, rgba(15, 23, 42, .96), rgba(30, 41, 59, .92)),
                #0f172a;
            color: #ffffff;
            box-shadow: 0 22px 54px rgba(15, 23, 42, .20);
            overflow: hidden;
            position: relative;
        }

        .petpay-admin-hero-card::before {
            content: "";
            position: absolute;
            width: 150px;
            height: 150px;
            right: -58px;
            top: -58px;
            border-radius: 50%;
            background: rgba(255, 122, 0, .45);
            filter: blur(4px);
        }

        .petpay-admin-hero-card span {
            position: relative;
            display: block;
            color: rgba(255, 255, 255, .70);
            font-size: 12px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: .08em;
        }

        .petpay-admin-hero-card strong {
            position: relative;
            display: block;
            margin-top: 8px;
            font-size: 34px;
            line-height: 1;
            letter-spacing: -.04em;
        }

        .petpay-admin-hero-card small {
            position: relative;
            display: block;
            margin-top: 8px;
            color: rgba(255, 255, 255, .74);
            font-weight: 650;
        }

        .petpay-admin-metrics {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 16px;
        }

        .petpay-admin-metric {
            position: relative;
            min-height: 118px;
            padding: 18px;
            border-radius: 26px;
            background:
                linear-gradient(145deg, rgba(255, 255, 255, .98), rgba(255, 250, 245, .82));
            border: 1px solid rgba(15, 23, 42, .08);
            box-shadow: 0 16px 40px rgba(15, 23, 42, .06);
            text-decoration: none;
            color: #0f172a;
            overflow: hidden;
            transition: transform .18s ease, box-shadow .18s ease, border-color .18s ease;
        }

        .petpay-admin-metric::before {
            content: "";
            position: absolute;
            inset: 0;
            background: linear-gradient(90deg, rgba(255, 122, 0, .14), transparent 44%);
            opacity: 0;
            transition: opacity .18s ease;
        }

        .petpay-admin-metric:hover {
            transform: translateY(-3px);
            border-color: rgba(255, 122, 0, .28);
            box-shadow: 0 24px 58px rgba(15, 23, 42, .10);
        }

        .petpay-admin-metric:hover::before {
            opacity: 1;
        }

        .petpay-admin-metric-top {
            position: relative;
            display: flex;
            justify-content: space-between;
            gap: 12px;
            align-items: flex-start;
        }

        .petpay-admin-metric-icon {
            width: 40px;
            height: 40px;
            display: inline-grid;
            place-items: center;
            flex: 0 0 auto;
            border-radius: 16px;
            background: rgba(255, 122, 0, .10);
            color: #f97316;
            font-size: 18px;
            box-shadow: inset 0 0 0 1px rgba(255, 122, 0, .12);
        }

        .petpay-admin-metric strong {
            position: relative;
            display: block;
            margin-top: 14px;
            color: #0f172a;
            font-size: 32px;
            line-height: 1;
            letter-spacing: -.04em;
            font-weight: 950;
        }

        .petpay-admin-metric span {
            position: relative;
            display: block;
            margin-top: 8px;
            color: #64748b;
            font-size: 12px;
            line-height: 1.35;
            font-weight: 850;
        }

        .petpay-admin-section {
            margin-top: 20px;
            padding: 24px;
            border-radius: 30px;
            background: #ffffff;
            border: 1px solid rgba(15, 23, 42, .08);
            box-shadow: 0 18px 50px rgba(15, 23, 42, .06);
        }

        .petpay-admin-section-head {
            display: flex;
            align-items: flex-end;
            justify-content: space-between;
            gap: 20px;
            margin-bottom: 18px;
        }

        .petpay-admin-section-head h2 {
            margin: 0;
            color: #0f172a;
            font-size: 26px;
            line-height: 1;
            letter-spacing: -.04em;
            font-weight: 950;
        }

        .petpay-admin-section-head p {
            margin: 9px 0 0;
            color: #64748b;
            font-size: 14px;
            line-height: 1.55;
            font-weight: 650;
        }

        .petpay-admin-status-pill {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 9px 13px;
            border-radius: 999px;
            background: rgba(34, 197, 94, .10);
            color: #15803d;
            font-size: 12px;
            font-weight: 900;
            white-space: nowrap;
        }

        .petpay-admin-actions {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 14px;
        }

        .petpay-admin-action {
            min-height: 132px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            gap: 18px;
            padding: 18px;
            border-radius: 24px;
            background:
                linear-gradient(145deg, #ffffff, #fffaf4);
            border: 1px solid rgba(15, 23, 42, .08);
            text-decoration: none;
            box-shadow: 0 14px 34px rgba(15, 23, 42, .05);
            transition: transform .18s ease, box-shadow .18s ease, border-color .18s ease;
        }

        .petpay-admin-action:hover {
            transform: translateY(-3px);
            border-color: rgba(255, 122, 0, .30);
            box-shadow: 0 22px 48px rgba(15, 23, 42, .09);
        }

        .petpay-admin-action-icon {
            width: 44px;
            height: 44px;
            display: inline-grid;
            place-items: center;
            border-radius: 17px;
            background: #0f172a;
            color: #ffffff;
            font-size: 18px;
            box-shadow: 0 14px 26px rgba(15, 23, 42, .18);
        }

        .petpay-admin-action strong {
            display: block;
            color: #0f172a;
            font-size: 15px;
            font-weight: 950;
            letter-spacing: -.02em;
        }

        .petpay-admin-action span {
            display: block;
            margin-top: 6px;
            color: #64748b;
            font-size: 12px;
            line-height: 1.45;
            font-weight: 650;
        }

        .petpay-admin-action-arrow {
            align-self: flex-end;
            width: 34px;
            height: 34px;
            display: inline-grid;
            place-items: center;
            border-radius: 999px;
            background: rgba(255, 122, 0, .10);
            color: #f97316;
            font-weight: 950;
        }

        @media (max-width: 1200px) {
            .petpay-admin-metrics,
            .petpay-admin-actions {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .petpay-admin-hero {
                grid-template-columns: 1fr;
            }

            .petpay-admin-hero-card {
                min-width: 0;
            }
        }

        @media (max-width: 760px) {
            .petpay-admin-dashboard-white {
                padding: 18px 14px 22px 74px;
            }

            .petpay-admin-dashboard-white .petpay-content-panel {
                border-radius: 26px;
                padding: 20px;
            }

            .petpay-admin-metrics,
            .petpay-admin-actions {
                grid-template-columns: 1fr;
            }

            .petpay-admin-section-head {
                align-items: flex-start;
                flex-direction: column;
            }
        }

        .petpay-admin-dashboard-white.petpay-dashboard {
            grid-template-columns: 56px minmax(0, 1fr) !important;
            gap: 8px !important;
        }

        .petpay-admin-dashboard-white .petpay-sidebar {
            margin-left: 0 !important;
        }

        .petpay-admin-dashboard-white .petpay-sidebar-card {
            margin-left: 0 !important;
        }

        @media (max-width: 820px) {
            .petpay-admin-dashboard-white.petpay-dashboard {
                grid-template-columns: 1fr !important;
                gap: 12px !important;
            }
        }
    </style>

    <section class="petpay-dashboard petpay-admin-dashboard-white">
        @include('partials.sidebars.admin')

        <div class="petpay-content-panel">
            <div class="petpay-admin-hero">
                <div>
                    <span class="petpay-admin-eyebrow">🐾 Panel central</span>

                    <h1>Portal Admin</h1>

                    <p>
                        Controla clientes, comercios, proveedores, repartidores, pedidos, pagos,
                        PawPoints, Petpay Plus, zonas y operación general desde un panel limpio,
                        rápido y listo para escalar.
                    </p>
                </div>

                <div class="petpay-admin-hero-card">
                    <span>Operación de hoy</span>
                    <strong>{{ number_format($metrics['orders_today'] ?? 0) }}</strong>
                    <small>Órdenes registradas en el día</small>
                </div>
            </div>

            <div class="petpay-admin-metrics">
                <a href="{{ route('admin.commerces.pending') }}" class="petpay-admin-metric">
                    <div class="petpay-admin-metric-top">
                        <div>
                            <strong>{{ number_format($metrics['commerces_pending'] ?? 0) }}</strong>
                            <span>Comercios pendientes</span>
                        </div>

                        <span class="petpay-admin-metric-icon">🏪</span>
                    </div>
                </a>

                <a href="{{ route('admin.drivers.pending') }}" class="petpay-admin-metric">
                    <div class="petpay-admin-metric-top">
                        <div>
                            <strong>{{ number_format($metrics['drivers_pending'] ?? 0) }}</strong>
                            <span>Repartidores pendientes</span>
                        </div>

                        <span class="petpay-admin-metric-icon">🛵</span>
                    </div>
                </a>

                <div class="petpay-admin-metric">
                    <div class="petpay-admin-metric-top">
                        <div>
                            <strong>{{ number_format($metrics['customers_total'] ?? 0) }}</strong>
                            <span>Clientes registrados</span>
                        </div>

                        <span class="petpay-admin-metric-icon">👤</span>
                    </div>
                </div>

                <div class="petpay-admin-metric">
                    <div class="petpay-admin-metric-top">
                        <div>
                            <strong>{{ number_format($metrics['commerces_approved'] ?? 0) }}</strong>
                            <span>Comercios aprobados</span>
                        </div>

                        <span class="petpay-admin-metric-icon">✅</span>
                    </div>
                </div>

                <div class="petpay-admin-metric">
                    <div class="petpay-admin-metric-top">
                        <div>
                            <strong>{{ number_format($metrics['drivers_approved'] ?? 0) }}</strong>
                            <span>Repartidores aprobados</span>
                        </div>

                        <span class="petpay-admin-metric-icon">🚚</span>
                    </div>
                </div>

                <div class="petpay-admin-metric">
                    <div class="petpay-admin-metric-top">
                        <div>
                            <strong>{{ number_format($metrics['orders_today'] ?? 0) }}</strong>
                            <span>Órdenes de hoy</span>
                        </div>

                        <span class="petpay-admin-metric-icon">📦</span>
                    </div>
                </div>

                <div class="petpay-admin-metric">
                    <div class="petpay-admin-metric-top">
                        <div>
                            <strong>{{ number_format($metrics['payments_today'] ?? 0) }}</strong>
                            <span>Pagos de hoy</span>
                        </div>

                        <span class="petpay-admin-metric-icon">💳</span>
                    </div>
                </div>

                <div class="petpay-admin-metric">
                    <div class="petpay-admin-metric-top">
                        <div>
                            <strong>{{ number_format($metrics['active_deliveries'] ?? 0) }}</strong>
                            <span>Entregas activas</span>
                        </div>

                        <span class="petpay-admin-metric-icon">📍</span>
                    </div>
                </div>
            
                <a href="{{ route('admin.identities.index') }}" class="petpay-admin-metric">
                    <div class="petpay-admin-metric-top">
                        <div>
                            <strong>{{ number_format($metrics['identities_pending'] ?? 0) }}</strong>
                            <span>Identidades pendientes</span>
                        </div>
                        <span class="petpay-admin-metric-icon">🪪</span>
                    </div>
                </a>

                <a href="{{ route('admin.branding.pending') }}" class="petpay-admin-metric">
                    <div class="petpay-admin-metric-top">
                        <div>
                            <strong>{{ number_format($metrics['branding_pending'] ?? 0) }}</strong>
                            <span>Imágenes Branding pendientes</span>
                        </div>

                        <span class="petpay-admin-metric-icon">🖼️</span>
                    </div>
                </a>
            </div>

            <div class="petpay-admin-section">
                <div class="petpay-admin-section-head">
                    <div>
                        <span class="petpay-admin-eyebrow">Acciones rápidas</span>

                        <h2>Operación pendiente</h2>

                        <p>
                            Revisa aprobaciones, controla altas operativas y mantén activa la operación
                            de comercios y repartidores.
                        </p>
                    </div>

                    <span class="petpay-admin-status-pill">● Sistema activo</span>
                </div>

                <div class="petpay-admin-actions">
                    <a href="{{ route('admin.commerces.pending') }}" class="petpay-admin-action">
                        <div>
                            <span class="petpay-admin-action-icon">🏪</span>

                            <div style="margin-top: 14px;">
                                <strong>Comercios</strong>
                                <span>Aprobar o rechazar negocios registrados.</span>
                            </div>
                        </div>

                        <span class="petpay-admin-action-arrow">›</span>
                    </a>

                    <a href="{{ route('admin.drivers.pending') }}" class="petpay-admin-action">
                        <div>
                            <span class="petpay-admin-action-icon">🛵</span>

                            <div style="margin-top: 14px;">
                                <strong>Repartidores</strong>
                                <span>Aprobar o rechazar repartidores registrados.</span>
                            </div>
                        </div>

                        <span class="petpay-admin-action-arrow">›</span>
                    </a>
                    <a href="{{ route('admin.identities.index') }}" class="petpay-admin-action">
                        <div>
                            <span class="petpay-admin-action-icon">🪪</span>
                            <div style="margin-top: 14px;">
                                <strong>Identidad y firma</strong>
                                <span>Validar INE, domicilio, representación legal y expediente del firmante.</span>
                            </div>
                        </div>
                        <span class="petpay-admin-action-arrow">›</span>
                    </a>

                    <a href="{{ route('admin.branding.pending') }}" class="petpay-admin-action">
                        <div>
                            <span class="petpay-admin-action-icon">🖼️</span>

                            <div style="margin-top: 14px;">
                                <strong>Branding</strong>
                                <span>Revisar imágenes enviadas por los comercios.</span>
                            </div>
                        </div>

                        <span class="petpay-admin-action-arrow">›</span>
                    </a>


                    <a href="#" class="petpay-admin-action">
                        <div>
                            <span class="petpay-admin-action-icon">📦</span>

                            <div style="margin-top: 14px;">
                                <strong>Órdenes</strong>
                                <span>Próximo módulo operativo de pedidos.</span>
                            </div>
                        </div>

                        <span class="petpay-admin-action-arrow">›</span>
                    </a>

                    <a href="#" class="petpay-admin-action">
                        <div>
                            <span class="petpay-admin-action-icon">💳</span>

                            <div style="margin-top: 14px;">
                                <strong>Pagos</strong>
                                <span>Próximo módulo de pagos, comisiones y cortes.</span>
                            </div>
                        </div>

                        <span class="petpay-admin-action-arrow">›</span>
                    </a>
                </div>
            </div>
        </div>
    </section>
@endsection