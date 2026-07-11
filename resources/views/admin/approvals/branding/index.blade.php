@php($portal = 'admin')

@extends('layouts.app')

@section('title', 'PETPAY-CARD | Revisión de Branding')

@section('content')
    <style>
        .branding-review-page {
            width: min(1180px, calc(100vw - 32px));
            margin: 0 auto;
            padding: 24px 0 48px;
        }

        .branding-review-head {
            display: flex;
            align-items: flex-end;
            justify-content: space-between;
            gap: 20px;
            margin-bottom: 20px;
        }

        .branding-review-head h1 {
            margin: 0;
            color: #0f172a;
            font-size: clamp(32px, 4vw, 52px);
            letter-spacing: -.05em;
        }

        .branding-review-head p {
            margin: 8px 0 0;
            color: #64748b;
        }

        .branding-review-count {
            padding: 10px 14px;
            border-radius: 999px;
            background: #111827;
            color: #fff;
            font-weight: 900;
            white-space: nowrap;
        }

        .branding-review-alert {
            margin-bottom: 16px;
            padding: 12px 14px;
            border-radius: 14px;
            background: rgba(16, 185, 129, .12);
            color: #047857;
            font-weight: 800;
        }

        .branding-review-grid {
            display: grid;
            gap: 18px;
        }

        .branding-review-card {
            padding: 18px;
            border: 1px solid rgba(15, 23, 42, .10);
            border-radius: 24px;
            background: #fff;
            box-shadow: 0 18px 44px rgba(15, 23, 42, .08);
        }

        .branding-review-card__head {
            display: flex;
            justify-content: space-between;
            gap: 16px;
            align-items: center;
            margin-bottom: 14px;
        }

        .branding-review-card__head h2 {
            margin: 0;
            font-size: 18px;
            color: #111827;
        }

        .branding-review-card__head p {
            margin: 4px 0 0;
            color: #64748b;
            font-size: 12px;
        }

        .branding-review-assets {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 14px;
        }

        .branding-review-asset {
            overflow: hidden;
            border: 1px solid rgba(15, 23, 42, .10);
            border-radius: 18px;
            background: #f8fafc;
        }

        .branding-review-asset[hidden] {
            display: none;
        }

        .branding-review-asset img {
            width: 100%;
            height: 180px;
            display: block;
            object-fit: cover;
            background: #e5e7eb;
        }

        .branding-review-asset__body {
            padding: 12px;
        }

        .branding-review-asset__body strong {
            color: #111827;
        }

        .branding-review-asset__body small {
            display: block;
            margin-top: 4px;
            color: #64748b;
        }

        .branding-review-actions {
            display: grid;
            gap: 8px;
            margin-top: 12px;
        }

        .branding-review-actions form {
            display: grid;
            gap: 8px;
        }

        .branding-review-actions textarea {
            min-height: 72px;
            width: 100%;
            resize: vertical;
            border: 1px solid rgba(15, 23, 42, .14);
            border-radius: 12px;
            padding: 9px 10px;
            font: inherit;
        }

        .branding-review-buttons {
            display: flex;
            gap: 8px;
        }

        .branding-review-button {
            flex: 1;
            min-height: 38px;
            border: 0;
            border-radius: 11px;
            color: #fff;
            font-weight: 900;
            cursor: pointer;
        }

        .branding-review-button--approve {
            background: #16a34a;
        }

        .branding-review-button--reject {
            background: #dc2626;
        }

        .branding-review-empty {
            padding: 34px;
            border: 1px dashed rgba(249, 115, 22, .35);
            border-radius: 22px;
            text-align: center;
            color: #64748b;
        }

        @media (max-width: 900px) {
            .branding-review-assets {
                grid-template-columns: 1fr;
            }

            .branding-review-head {
                align-items: flex-start;
                flex-direction: column;
            }
        }
    </style>

    <section class="branding-review-page">
        <header class="branding-review-head">
            <div>
                <h1>Revisión de Branding</h1>
                <p>Aprueba o rechaza individualmente las imágenes enviadas por cada comercio.</p>
            </div>

            <span class="branding-review-count">{{ number_format($pendingCount) }} pendientes</span>
        </header>

        @if (session('status'))
            <div class="branding-review-alert">{{ session('status') }}</div>
        @endif

        @if ($errors->any())
            <div class="branding-review-alert" style="background: rgba(239,68,68,.12); color:#b91c1c;">
                {{ $errors->first() }}
            </div>
        @endif

        <div class="branding-review-grid">
            @forelse ($brandings as $branding)
                <article class="branding-review-card">
                    <header class="branding-review-card__head">
                        <div>
                            <h2>{{ $branding->commerce?->business_name ?? $branding->store_name ?? 'Comercio' }}</h2>
                            <p>{{ $branding->commerce?->email ?? 'Sin correo' }}</p>
                        </div>
                    </header>

                    <div class="branding-review-assets">
                        @foreach ([
                            'header' => 'Imagen de cabecera',
                            'icon' => 'Imagen de ícono',
                            'listing' => 'Imagen de listado',
                        ] as $type => $label)
                            @php
                                $pathField = "{$type}_image_path";
                                $statusField = "{$type}_image_status";
                                $submittedField = "{$type}_image_submitted_at";
                            @endphp

                            <section class="branding-review-asset" @hidden($branding->{$statusField} !== 'pending' || ! $branding->{$pathField})>
                                <img
                                    src="{{ asset('storage/'.$branding->{$pathField}) }}"
                                    alt="{{ $label }}"
                                >

                                <div class="branding-review-asset__body">
                                    <strong>{{ $label }}</strong>
                                    <small>
                                        Enviada:
                                        {{ $branding->{$submittedField}?->format('d/m/Y H:i') ?? 'Sin fecha' }}
                                    </small>

                                    <div class="branding-review-actions">
                                        <form method="POST" action="{{ route('admin.branding.approve', [$branding, $type]) }}">
                                            @csrf
                                            @method('PATCH')

                                            <button type="submit" class="branding-review-button branding-review-button--approve">
                                                Aprobar
                                            </button>
                                        </form>

                                        <form method="POST" action="{{ route('admin.branding.reject', [$branding, $type]) }}">
                                            @csrf
                                            @method('PATCH')

                                            <textarea
                                                name="reason"
                                                placeholder="Motivo obligatorio del rechazo"
                                                required
                                                minlength="8"
                                                maxlength="1000"
                                            ></textarea>

                                            <button type="submit" class="branding-review-button branding-review-button--reject">
                                                Rechazar
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </section>
                        @endforeach
                    </div>
                </article>
            @empty
                <div class="branding-review-empty">
                    No hay imágenes de Branding pendientes de revisión.
                </div>
            @endforelse
        </div>

        @if ($brandings->hasPages())
            <div style="margin-top: 18px;">
                {{ $brandings->links() }}
            </div>
        @endif
    </section>
@endsection
