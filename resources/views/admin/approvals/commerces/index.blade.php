@php($portal = 'admin')

@extends('layouts.app')

@section('title', 'PETPAY-CARD | Comercios pendientes')

@section('content')
    <style>
        body:has(.commerce-admin-white) {
            background: #ffffff !important;
        }

        .commerce-admin-white.petpay-dashboard {
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

        .commerce-admin-white::before {
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

        .commerce-admin-white .petpay-sidebar {
            margin-left: 0 !important;
        }

        .commerce-admin-white .petpay-sidebar-card {
            margin-left: 0 !important;
        }

        .commerce-admin-white .petpay-content-panel {
            position: relative;
            z-index: 1;
            width: 100%;
            max-width: none;
            min-height: calc(100vh - 120px);
            padding: 30px;
            border-radius: 34px;
            background: rgba(255, 255, 255, .94) !important;
            border: 1px solid rgba(15, 23, 42, .08) !important;
            box-shadow:
                0 24px 70px rgba(15, 23, 42, .08),
                0 8px 24px rgba(255, 122, 0, .06) !important;
            backdrop-filter: blur(18px);
            -webkit-backdrop-filter: blur(18px);
        }

        .commerce-admin-compact {
            display: grid;
            gap: 20px;
        }

        .commerce-admin-top {
            display: grid;
            grid-template-columns: minmax(0, 1fr) auto;
            align-items: center;
            gap: 24px;
            padding: 6px 4px 10px;
        }

        .commerce-admin-kicker {
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

        .commerce-admin-top h1 {
            margin: 0;
            color: #0f172a;
            font-size: clamp(34px, 4vw, 54px);
            line-height: .96;
            letter-spacing: -.055em;
            font-weight: 950;
        }

        .commerce-admin-top p {
            max-width: 760px;
            margin: 14px 0 0;
            color: #64748b;
            font-size: 15px;
            line-height: 1.65;
            font-weight: 650;
        }

        .commerce-admin-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            justify-content: flex-end;
        }

        .commerce-admin-btn {
            min-height: 42px;
            padding: 0 16px;
            border-radius: 999px;
            border: 0;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            background: #0f172a;
            color: #ffffff;
            font-size: 12px;
            font-weight: 950;
            text-decoration: none;
            box-shadow: 0 16px 34px rgba(15, 23, 42, .20);
            transition: transform .18s ease, box-shadow .18s ease;
            white-space: nowrap;
        }

        .commerce-admin-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 22px 44px rgba(15, 23, 42, .24);
        }

        .commerce-admin-stats {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 16px;
        }

        .commerce-admin-stat {
            min-height: 118px;
            position: relative;
            padding: 18px;
            border-radius: 26px;
            background:
                linear-gradient(145deg, rgba(255, 255, 255, .98), rgba(255, 250, 245, .82));
            border: 1px solid rgba(15, 23, 42, .08);
            box-shadow: 0 16px 40px rgba(15, 23, 42, .06);
            overflow: hidden;
            transition: transform .18s ease, box-shadow .18s ease, border-color .18s ease;
        }

        .commerce-admin-stat::before {
            content: "";
            position: absolute;
            inset: 0;
            background: linear-gradient(90deg, rgba(255, 122, 0, .13), transparent 44%);
            opacity: 0;
            transition: opacity .18s ease;
        }

        .commerce-admin-stat:hover {
            transform: translateY(-3px);
            border-color: rgba(255, 122, 0, .26);
            box-shadow: 0 24px 58px rgba(15, 23, 42, .10);
        }

        .commerce-admin-stat:hover::before {
            opacity: 1;
        }

        .commerce-admin-stat strong {
            position: relative;
            display: block;
            color: #0f172a;
            font-size: 34px;
            line-height: 1;
            letter-spacing: -.045em;
            font-weight: 950;
        }

        .commerce-admin-stat span {
            position: relative;
            display: block;
            margin-top: 10px;
            color: #64748b;
            font-size: 12px;
            line-height: 1.35;
            font-weight: 900;
        }

        .commerce-admin-status {
            padding: 13px 15px;
            border-radius: 18px;
            background: rgba(22, 163, 74, .10);
            color: #166534;
            font-size: 13px;
            font-weight: 900;
            border: 1px solid rgba(22, 163, 74, .20);
        }

        .commerce-admin-main {
            display: grid;
            grid-template-columns: minmax(0, 1fr) 300px;
            gap: 16px;
            align-items: start;
        }

        .commerce-admin-card {
            padding: 22px;
            border-radius: 30px;
            background: #ffffff;
            border: 1px solid rgba(15, 23, 42, .08);
            box-shadow: 0 18px 50px rgba(15, 23, 42, .06);
        }

        .commerce-admin-card h2 {
            margin: 0;
            color: #0f172a;
            font-size: 26px;
            line-height: 1;
            letter-spacing: -.045em;
            font-weight: 950;
        }

        .commerce-admin-card p {
            margin: 9px 0 0;
            color: #64748b;
            font-size: 13px;
            line-height: 1.55;
            font-weight: 650;
        }

        .commerce-admin-list {
            display: grid;
            gap: 12px;
            margin-top: 16px;
        }

        .commerce-admin-row {
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

        .commerce-admin-row-title {
            display: flex;
            align-items: center;
            gap: 11px;
            min-width: 0;
        }

        .commerce-admin-row-icon {
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

        .commerce-admin-row-title strong {
            display: block;
            color: #0f172a;
            font-size: 15px;
            font-weight: 950;
            overflow-wrap: anywhere;
        }

        .commerce-admin-row-title span {
            display: block;
            margin-top: 3px;
            color: #64748b;
            font-size: 12px;
            font-weight: 650;
            overflow-wrap: anywhere;
        }

        .commerce-admin-tags {
            display: flex;
            flex-wrap: wrap;
            gap: 7px;
            margin-top: 11px;
        }

        .commerce-admin-tag {
            padding: 6px 9px;
            border-radius: 999px;
            background: rgba(255, 122, 0, .09);
            color: #c2410c;
            font-size: 10px;
            font-weight: 950;
            border: 1px solid rgba(255, 122, 0, .18);
        }

        .commerce-admin-row-actions {
            display: flex;
            gap: 8px;
        }

        .commerce-admin-row-actions form {
            margin: 0;
        }

        .commerce-admin-icon-btn {
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

        .commerce-admin-icon-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 16px 30px rgba(15, 23, 42, .18);
        }

        .commerce-admin-icon-btn--approve {
            background: #16a34a;
        }

        .commerce-admin-icon-btn--reject {
            background: #dc2626;
        }

        .commerce-admin-empty {
            min-height: 210px;
            display: grid;
            place-items: center;
            text-align: center;
            padding: 28px;
            border-radius: 26px;
            background:
                radial-gradient(circle at top, rgba(255, 122, 0, .09), transparent 50%),
                #ffffff;
            border: 1px dashed rgba(255, 122, 0, .30);
            margin-top: 16px;
        }

        .commerce-admin-empty-icon {
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

        .commerce-admin-empty strong {
            display: block;
            color: #0f172a;
            font-size: 20px;
            letter-spacing: -.035em;
            font-weight: 950;
        }

        .commerce-admin-empty p {
            margin-top: 8px;
        }

        .commerce-admin-side-list {
            display: grid;
            gap: 10px;
            margin-top: 16px;
        }

        .commerce-admin-side-link {
            padding: 14px;
            border-radius: 20px;
            background:
                linear-gradient(145deg, #ffffff, #fffaf4);
            border: 1px solid rgba(15, 23, 42, .08);
            color: #0f172a;
            text-decoration: none;
            font-size: 12px;
            font-weight: 950;
            box-shadow: 0 12px 28px rgba(15, 23, 42, .05);
            transition: transform .18s ease, box-shadow .18s ease, border-color .18s ease;
        }

        .commerce-admin-side-link:hover {
            transform: translateY(-2px);
            border-color: rgba(255, 122, 0, .28);
            box-shadow: 0 18px 38px rgba(15, 23, 42, .09);
        }

        .commerce-admin-side-link span {
            display: block;
            margin-top: 6px;
            color: #64748b;
            font-weight: 700;
            line-height: 1.4;
        }

        @media (max-width: 1100px) {
            .commerce-admin-main {
                grid-template-columns: 1fr;
            }

            .commerce-admin-stats {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media (max-width: 820px) {
            .commerce-admin-white.petpay-dashboard {
                grid-template-columns: 1fr !important;
                gap: 12px !important;
                padding: 18px 14px 92px 14px;
            }

            .commerce-admin-white .petpay-content-panel {
                min-height: auto;
                padding: 20px;
                border-radius: 26px;
            }
        }

        @media (max-width: 720px) {
            .commerce-admin-top {
                grid-template-columns: 1fr;
            }

            .commerce-admin-actions {
                justify-content: flex-start;
            }

            .commerce-admin-stats {
                grid-template-columns: 1fr;
            }

            .commerce-admin-row {
                grid-template-columns: 1fr;
            }

            .commerce-admin-row-actions {
                justify-content: flex-start;
            }
        }
    </style>

    <section class="petpay-dashboard commerce-admin-white">
        @include('partials.sidebars.admin')

        <div class="petpay-content-panel commerce-admin-compact">
            <div class="commerce-admin-top">
                <div>
                    <span class="commerce-admin-kicker">🏪 Admin / Comercios</span>

                    <h1>Solicitudes de comercios</h1>

                    <p>
                        Administra los comercios que venderán productos y servicios dentro de Petpay.
                        Aprueba únicamente cuentas validadas para que puedan entrar a su portal.
                    </p>
                </div>

                <div class="commerce-admin-actions">
                    <a href="{{ route('comercio.register') }}" class="commerce-admin-btn">
                        + Registro comercial
                    </a>
                </div>
            </div>

            <div class="commerce-admin-stats">
                <div class="commerce-admin-stat">
                    <strong>{{ number_format($pendingCount) }}</strong>
                    <span>Pendientes</span>
                </div>

                <div class="commerce-admin-stat">
                    <strong>{{ number_format($approvedCount) }}</strong>
                    <span>Aprobados</span>
                </div>

                <div class="commerce-admin-stat">
                    <strong>{{ number_format($rejectedCount) }}</strong>
                    <span>Rechazados</span>
                </div>

                <div class="commerce-admin-stat">
                    <strong>{{ number_format($totalCount) }}</strong>
                    <span>Total comercios</span>
                </div>
            </div>

            @if (session('status'))
                <div class="commerce-admin-status">
                    {{ session('status') }}
                </div>
            @endif

            <div class="commerce-admin-main">
                <div class="commerce-admin-card">
                    <h2>Revisión pendiente</h2>

                    <p>
                        Los comercios pendientes aparecerán aquí para revisión rápida.
                    </p>

                    <div class="commerce-admin-list">
                        @forelse ($commerces as $commerce)
                            <article class="commerce-admin-row">
                                <div>
                                    <div class="commerce-admin-row-title">
                                        <div class="commerce-admin-row-icon">🏪</div>

                                        <div>
                                            <strong>{{ $commerce->business_name }}</strong>

                                            <span>
                                                {{ $commerce->email }} · {{ $commerce->phone ?: 'Sin teléfono' }}
                                            </span>
                                        </div>
                                    </div>

                                    <div class="commerce-admin-tags">
                                        <span class="commerce-admin-tag">
                                            Responsable:
                                            {{ $commerce->name ?: trim(($commerce->first_name ?? '') . ' ' . ($commerce->last_name ?? '')) }}
                                        </span>

                                        <span class="commerce-admin-tag">
                                            {{ $commerce->business_type ?: 'Sin tipo' }}
                                        </span>

                                        <span class="commerce-admin-tag">
                                            {{ $commerce->sells_products ? '✓ Productos' : '× Productos' }}
                                        </span>

                                        <span class="commerce-admin-tag">
                                            {{ $commerce->offers_services ? '✓ Servicios' : '× Servicios' }}
                                        </span>

                                        <span class="commerce-admin-tag">
                                            {{ $commerce->uses_petpay_delivery ? '✓ Reparto Petpay' : '× Reparto Petpay' }}
                                        </span>
                                    </div>
                                </div>

                                <div class="commerce-admin-row-actions">
                                    <form method="POST" action="{{ route('admin.commerces.approve', $commerce) }}">
                                        @csrf

                                        <button class="commerce-admin-icon-btn commerce-admin-icon-btn--approve" type="submit" title="Aprobar comercio">
                                            ✓
                                        </button>
                                    </form>

                                    <form method="POST" action="{{ route('admin.commerces.reject', $commerce) }}">
                                        @csrf

                                        <button class="commerce-admin-icon-btn commerce-admin-icon-btn--reject" type="submit" title="Rechazar comercio">
                                            ×
                                        </button>
                                    </form>
                                </div>
                            </article>
                        @empty
                            <div class="commerce-admin-empty">
                                <div>
                                    <div class="commerce-admin-empty-icon">🏪</div>

                                    <strong>No hay comercios pendientes</strong>

                                    <p>
                                        Cuando un comercio se registre, aparecerá aquí para revisión.
                                    </p>
                                </div>
                            </div>
                        @endforelse
                    </div>

                    @if ($commerces->hasPages())
                        <div style="margin-top: 14px;">
                            {{ $commerces->links() }}
                        </div>
                    @endif
                </div>

                <aside class="commerce-admin-card">
                    <h2>Menú comercios</h2>

                    <p>
                        Acceso rápido al alta comercial de nuevos comercios.
                    </p>

                    <div class="commerce-admin-side-list">
                        <a href="{{ route('comercio.register') }}" class="commerce-admin-side-link">
                            + Registro comercial
                            <span>Abre el formulario público para dar de alta un comercio vendedor.</span>
                        </a>
                    </div>
                </aside>
            </div>
        </div>
    </section>
@endsection