@php($portal = 'admin')

@extends('layouts.app')

@section('title', 'Proveedores pendientes | PETPAY-CARD')

@section('content')
    <style>
        body:has(.provider-admin-white) {
            background: #ffffff !important;
        }

        .provider-admin-white.petpay-dashboard {
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

        .provider-admin-white::before {
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

        .provider-admin-white .petpay-sidebar,
        .provider-admin-white .petpay-sidebar-card {
            margin-left: 0 !important;
        }

       .provider-admin-white .petpay-content-panel {
            position: relative;
            z-index: 1;
            width: 100%;
            max-width: none;
            min-height: calc(100vh - 120px);
            padding: 28px;
            align-content: start !important;
            border-radius: 34px;
            background: rgba(255, 255, 255, .94) !important;
            border: 1px solid rgba(15, 23, 42, .08) !important;
            box-shadow:
                0 24px 70px rgba(15, 23, 42, .08),
                0 8px 24px rgba(255, 122, 0, .06) !important;
            backdrop-filter: blur(18px);
            -webkit-backdrop-filter: blur(18px);
        }

        .provider-admin-wrap {
            display: grid;
            gap: 20px;
            align-content: start;
        }

        .provider-admin-top {
            display: grid;
            grid-template-columns: minmax(0, 1fr) auto;
            align-items: center;
            gap: 24px;
            padding: 4px 4px 4px;
        }

        .provider-admin-kicker {
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

        .provider-admin-top h1 {
            margin: 0;
            color: #0f172a;
            font-size: clamp(34px, 4vw, 54px);
            line-height: .96;
            letter-spacing: -.055em;
            font-weight: 950;
        }

        .provider-admin-top p {
            max-width: 760px;
            margin: 14px 0 0;
            color: #64748b;
            font-size: 15px;
            line-height: 1.65;
            font-weight: 650;
        }

        .provider-admin-btn {
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

        .provider-admin-btn:hover {
            transform: translateY(-2px);
            border-color: rgba(255, 122, 0, .28);
            box-shadow: 0 20px 40px rgba(15, 23, 42, .12);
        }

        .provider-admin-status {
            padding: 13px 15px;
            border-radius: 18px;
            background: rgba(22, 163, 74, .10);
            color: #166534;
            font-size: 13px;
            font-weight: 900;
            border: 1px solid rgba(22, 163, 74, .20);
        }

        .provider-admin-card {
            padding: 22px;
            border-radius: 30px;
            background: #ffffff;
            border: 1px solid rgba(15, 23, 42, .08);
            box-shadow: 0 18px 50px rgba(15, 23, 42, .06);
        }

        .provider-admin-card-head {
            display: flex;
            align-items: flex-end;
            justify-content: space-between;
            gap: 18px;
            margin-bottom: 18px;
        }

        .provider-admin-card h2 {
            margin: 0;
            color: #0f172a;
            font-size: 26px;
            line-height: 1;
            letter-spacing: -.045em;
            font-weight: 950;
        }

        .provider-admin-card p {
            margin: 9px 0 0;
            color: #64748b;
            font-size: 13px;
            line-height: 1.55;
            font-weight: 650;
        }

        .provider-admin-count {
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

        .provider-admin-list {
            display: grid;
            gap: 12px;
        }

        .provider-admin-row {
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

        .provider-admin-row-title {
            display: flex;
            align-items: center;
            gap: 11px;
            min-width: 0;
        }

        .provider-admin-row-icon {
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

        .provider-admin-row-title strong {
            display: block;
            color: #0f172a;
            font-size: 15px;
            font-weight: 950;
            overflow-wrap: anywhere;
        }

        .provider-admin-row-title span {
            display: block;
            margin-top: 3px;
            color: #64748b;
            font-size: 12px;
            font-weight: 650;
            overflow-wrap: anywhere;
        }

        .provider-admin-tags {
            display: flex;
            flex-wrap: wrap;
            gap: 7px;
            margin-top: 11px;
        }

        .provider-admin-tag {
            padding: 6px 9px;
            border-radius: 999px;
            background: rgba(255, 122, 0, .09);
            color: #c2410c;
            font-size: 10px;
            font-weight: 950;
            border: 1px solid rgba(255, 122, 0, .18);
        }

        .provider-admin-row-actions {
            display: flex;
            gap: 8px;
        }

        .provider-admin-row-actions form {
            margin: 0;
        }

        .provider-admin-icon-btn {
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

        .provider-admin-icon-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 16px 30px rgba(15, 23, 42, .18);
        }

        .provider-admin-icon-btn--approve {
            background: #16a34a;
        }

        .provider-admin-icon-btn--reject {
            background: #dc2626;
        }

        .provider-admin-empty {
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

        .provider-admin-empty-icon {
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

        .provider-admin-empty strong {
            display: block;
            color: #0f172a;
            font-size: 20px;
            letter-spacing: -.035em;
            font-weight: 950;
        }

        .provider-admin-empty p {
            margin: 8px 0 0;
            color: #64748b;
            font-size: 13px;
            line-height: 1.55;
            font-weight: 650;
        }

        .provider-admin-pagination {
            margin-top: 14px;
        }

        @media (max-width: 820px) {
            .provider-admin-white.petpay-dashboard {
                grid-template-columns: 1fr !important;
                gap: 12px !important;
                padding: 18px 14px 92px 14px;
            }

            .provider-admin-white .petpay-content-panel {
                min-height: auto;
                padding: 20px;
                border-radius: 26px;
            }
        }

        @media (max-width: 720px) {
            .provider-admin-top {
                grid-template-columns: 1fr;
            }

            .provider-admin-card-head {
                align-items: flex-start;
                flex-direction: column;
            }

            .provider-admin-row {
                grid-template-columns: 1fr;
            }

            .provider-admin-row-actions {
                justify-content: flex-start;
            }
        }
    </style>

    <section class="petpay-dashboard provider-admin-white">
        @include('partials.sidebars.admin')

        <div class="petpay-content-panel provider-admin-wrap">
            <div class="provider-admin-top">
                <div>
                    <span class="provider-admin-kicker">📦 Admin / Proveedores</span>

                    <h1>Proveedores pendientes</h1>

                    <p>
                        Revisa negocios registrados y aprueba o rechaza su acceso al portal proveedor.
                        Este módulo se conserva separado de comercios para futuras operaciones.
                    </p>
                </div>

                <a href="{{ route('admin.dashboard') }}" class="provider-admin-btn">
                    ← Dashboard
                </a>
            </div>

            @if (session('status'))
                <div class="provider-admin-status">
                    {{ session('status') }}
                </div>
            @endif

            <div class="provider-admin-card">
                <div class="provider-admin-card-head">
                    <div>
                        <h2>Revisión pendiente</h2>

                        <p>
                            Los proveedores pendientes aparecerán aquí para aprobación rápida.
                        </p>
                    </div>

                    <span class="provider-admin-count">
                        {{ number_format($providers->total()) }} pendiente{{ $providers->total() === 1 ? '' : 's' }}
                    </span>
                </div>

                <div class="provider-admin-list">
                    @forelse ($providers as $provider)
                        <article class="provider-admin-row">
                            <div>
                                <div class="provider-admin-row-title">
                                    <div class="provider-admin-row-icon">📦</div>

                                    <div>
                                        <strong>{{ $provider->business_name }}</strong>

                                        <span>
                                            {{ $provider->email }} · {{ $provider->phone ?: 'Sin teléfono' }}
                                        </span>
                                    </div>
                                </div>

                                <div class="provider-admin-tags">
                                    <span class="provider-admin-tag">
                                        {{ $provider->business_type ?: 'Sin tipo' }}
                                    </span>

                                    <span class="provider-admin-tag">
                                        {{ $provider->business_address ?: 'Sin dirección' }}
                                    </span>

                                    <span class="provider-admin-tag">
                                        Pendiente
                                    </span>

                                    <span class="provider-admin-tag">
                                        {{ $provider->created_at?->format('d/m/Y H:i') ?: 'Sin fecha' }}
                                    </span>
                                </div>
                            </div>

                            <div class="provider-admin-row-actions">
                                <form method="POST" action="{{ route('admin.providers.approve', $provider) }}">
                                    @csrf
                                    @method('PATCH')

                                    <button type="submit" class="provider-admin-icon-btn provider-admin-icon-btn--approve" title="Aprobar proveedor">
                                        ✓
                                    </button>
                                </form>

                                <form method="POST" action="{{ route('admin.providers.reject', $provider) }}">
                                    @csrf
                                    @method('PATCH')

                                    <button type="submit" class="provider-admin-icon-btn provider-admin-icon-btn--reject" title="Rechazar proveedor">
                                        ×
                                    </button>
                                </form>
                            </div>
                        </article>
                    @empty
                        <div class="provider-admin-empty">
                            <div>
                                <div class="provider-admin-empty-icon">📦</div>

                                <strong>No hay proveedores pendientes</strong>

                                <p>
                                    Cuando un proveedor se registre, aparecerá aquí para revisión.
                                </p>
                            </div>
                        </div>
                    @endforelse
                </div>

                @if ($providers->hasPages())
                    <div class="provider-admin-pagination">
                        {{ $providers->links() }}
                    </div>
                @endif
            </div>
        </div>
    </section>
@endsection