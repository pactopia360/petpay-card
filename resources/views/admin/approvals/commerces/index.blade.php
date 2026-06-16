@php($portal = 'admin')

@extends('layouts.app')

@section('title', 'PETPAY-CARD | Comercios pendientes')

@section('content')
    <style>
        .commerce-admin-compact {
            display: grid;
            gap: 16px;
        }

        .commerce-admin-top {
            display: grid;
            grid-template-columns: minmax(0, 1fr) auto;
            align-items: center;
            gap: 18px;
        }

        .commerce-admin-top h1 {
            margin: 0;
            font-size: clamp(28px, 4vw, 42px);
            line-height: .98;
            letter-spacing: -.055em;
        }

        .commerce-admin-top p {
            margin: 8px 0 0;
            max-width: 720px;
            color: #6b7280;
            font-size: 14px;
            line-height: 1.45;
        }

        .commerce-admin-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            justify-content: flex-end;
        }

        .commerce-admin-btn {
            min-height: 38px;
            padding: 0 14px;
            border-radius: 999px;
            border: 0;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: #111827;
            color: #fff;
            font-size: 12px;
            font-weight: 900;
            text-decoration: none;
            box-shadow: 0 12px 28px rgba(17, 24, 39, .16);
        }

        .commerce-admin-btn--light {
            background: rgba(255, 255, 255, .78);
            color: #111827;
            border: 1px solid rgba(255, 255, 255, .86);
        }

        .commerce-admin-stats {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 10px;
        }

        .commerce-admin-stat {
            min-height: 82px;
            padding: 14px;
            border-radius: 20px;
            background: rgba(255, 255, 255, .74);
            border: 1px solid rgba(255, 255, 255, .82);
            box-shadow: 0 14px 36px rgba(17, 24, 39, .07);
        }

        .commerce-admin-stat strong {
            display: block;
            font-size: 26px;
            line-height: 1;
            letter-spacing: -.04em;
            color: #111827;
        }

        .commerce-admin-stat span {
            display: block;
            margin-top: 7px;
            font-size: 12px;
            font-weight: 900;
            color: #6b7280;
        }

        .commerce-admin-main {
            display: grid;
            grid-template-columns: minmax(0, 1fr) 280px;
            gap: 14px;
            align-items: start;
        }

        .commerce-admin-card {
            padding: 18px;
            border-radius: 24px;
            background: rgba(255, 255, 255, .78);
            border: 1px solid rgba(255, 255, 255, .84);
            box-shadow: 0 18px 48px rgba(17, 24, 39, .08);
        }

        .commerce-admin-card h2 {
            margin: 0;
            font-size: 24px;
            letter-spacing: -.045em;
            color: #111827;
        }

        .commerce-admin-card p {
            margin: 7px 0 0;
            color: #6b7280;
            font-size: 13px;
            line-height: 1.45;
        }

        .commerce-admin-list {
            display: grid;
            gap: 10px;
            margin-top: 14px;
        }

        .commerce-admin-row {
            display: grid;
            grid-template-columns: minmax(0, 1fr) auto;
            gap: 14px;
            align-items: center;
            padding: 13px;
            border-radius: 18px;
            background: rgba(255, 255, 255, .72);
            border: 1px solid rgba(229, 231, 235, .72);
        }

        .commerce-admin-row-title {
            display: flex;
            align-items: center;
            gap: 9px;
            min-width: 0;
        }

        .commerce-admin-row-icon {
            width: 36px;
            height: 36px;
            border-radius: 14px;
            display: grid;
            place-items: center;
            background: #111827;
            color: #fff;
            flex: 0 0 auto;
        }

        .commerce-admin-row-title strong {
            display: block;
            color: #111827;
            font-size: 15px;
            overflow-wrap: anywhere;
        }

        .commerce-admin-row-title span {
            display: block;
            margin-top: 2px;
            color: #6b7280;
            font-size: 12px;
            overflow-wrap: anywhere;
        }

        .commerce-admin-tags {
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
            margin-top: 9px;
        }

        .commerce-admin-tag {
            padding: 5px 8px;
            border-radius: 999px;
            background: #fff7ed;
            color: #9a3412;
            font-size: 10px;
            font-weight: 900;
            border: 1px solid #fed7aa;
        }

        .commerce-admin-row-actions {
            display: flex;
            gap: 7px;
        }

        .commerce-admin-icon-btn {
            width: 36px;
            height: 36px;
            border: 0;
            border-radius: 13px;
            color: #fff;
            font-size: 16px;
            font-weight: 900;
            cursor: pointer;
        }

        .commerce-admin-icon-btn--approve {
            background: #16a34a;
        }

        .commerce-admin-icon-btn--reject {
            background: #dc2626;
        }

        .commerce-admin-empty {
            min-height: 190px;
            display: grid;
            place-items: center;
            text-align: center;
            padding: 24px;
            border-radius: 22px;
            background:
                radial-gradient(circle at top, rgba(255, 138, 31, .12), transparent 48%),
                rgba(255, 255, 255, .62);
            border: 1px dashed rgba(255, 138, 31, .36);
            margin-top: 14px;
        }

        .commerce-admin-empty-icon {
            width: 50px;
            height: 50px;
            margin: 0 auto 10px;
            border-radius: 18px;
            display: grid;
            place-items: center;
            background: #111827;
            color: #fff;
            font-size: 24px;
        }

        .commerce-admin-empty strong {
            display: block;
            color: #111827;
            font-size: 20px;
            letter-spacing: -.035em;
        }

        .commerce-admin-side-list {
            display: grid;
            gap: 10px;
            margin-top: 14px;
        }

        .commerce-admin-side-link {
            padding: 12px;
            border-radius: 17px;
            background: rgba(255, 255, 255, .70);
            border: 1px solid rgba(255, 255, 255, .82);
            color: #111827;
            text-decoration: none;
            font-size: 12px;
            font-weight: 900;
        }

        .commerce-admin-side-link span {
            display: block;
            margin-top: 4px;
            color: #6b7280;
            font-weight: 700;
            line-height: 1.35;
        }

        .commerce-admin-status {
            padding: 12px 14px;
            border-radius: 18px;
            background: rgba(22, 163, 74, .13);
            color: #166534;
            font-size: 13px;
            font-weight: 900;
            border: 1px solid rgba(22, 163, 74, .22);
        }

        @media (max-width: 1050px) {
            .commerce-admin-main {
                grid-template-columns: 1fr;
            }

            .commerce-admin-stats {
                grid-template-columns: repeat(2, minmax(0, 1fr));
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

    <section class="petpay-dashboard">
        @include('partials.sidebars.admin')

        <div class="petpay-content-panel commerce-admin-compact">
            <div class="commerce-admin-top">
                <div>
                    <span class="petpay-kicker">Admin / Comercios</span>
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