@php($portal = 'admin')

@extends('layouts.app')

@section('title', 'Solicitudes de actualización | PETPAY-CARD')

@push('styles')
    <link
        rel="stylesheet"
        href="{{ asset('assets/petpay-card/css/admin/driver-identities.css') }}?v=20260713-4"
    >
@endpush

@section('content')
<section class="petpay-dashboard driver-identities-shell">
    @include('partials.sidebars.admin')

    <div class="petpay-content-panel driver-identities-content">
        <section class="driver-review">
            <header class="driver-review__hero">
                <div>
                    <span class="driver-review__kicker">
                        📝 Admin / Repartidores
                    </span>

                    <h1>Solicitudes de actualización</h1>

                    <p>
                        Aprueba o rechaza cambios solicitados sobre datos protegidos.
                    </p>
                </div>

                <div class="driver-review__hero-actions">
                    <a
                        href="{{ route('admin.driver-identities.index') }}"
                        class="driver-review-btn driver-review-btn--light"
                    >
                        Expedientes
                    </a>

                    <a
                        href="{{ route('admin.dashboard') }}"
                        class="driver-review-btn driver-review-btn--dark"
                    >
                        Dashboard
                    </a>
                </div>
            </header>

            @if (session('status'))
                <div class="driver-review-alert driver-review-alert--success">
                    {{ session('status') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="driver-review-alert driver-review-alert--error">
                    @foreach ($errors->all() as $error)
                        <div>{{ $error }}</div>
                    @endforeach
                </div>
            @endif

            <form
                method="GET"
                class="driver-review__filters driver-review__filters--requests"
            >
                <label>
                    <span>Estado</span>

                    <select name="status">
                        <option value="">Todos</option>
                        <option value="pending" @selected($statusFilter === 'pending')>
                            Pendientes
                        </option>
                        <option value="under_review" @selected($statusFilter === 'under_review')>
                            En revisión
                        </option>
                        <option value="approved" @selected($statusFilter === 'approved')>
                            Aprobadas
                        </option>
                        <option value="rejected" @selected($statusFilter === 'rejected')>
                            Rechazadas
                        </option>
                    </select>
                </label>

                <button class="driver-review-btn driver-review-btn--primary">
                    Filtrar
                </button>

                <a
                    href="{{ route('admin.driver-update-requests.index') }}"
                    class="driver-review-btn driver-review-btn--light"
                >
                    Limpiar
                </a>
            </form>

            <div class="driver-update-admin-list">
                @forelse ($requests as $updateRequest)
                    <article class="driver-update-admin-card">
                        <header>
                            <div>
                                <strong>
                                    {{ $updateRequest->driver?->name ?: 'Repartidor' }}
                                </strong>

                                <span>
                                    {{ $updateRequest->driver?->email }}
                                    ·
                                    {{ $updateRequest->created_at?->format('d/m/Y H:i') }}
                                </span>
                            </div>

                            <b class="driver-review-status is-{{ $updateRequest->status }}">
                                {{ str_replace('_', ' ', $updateRequest->status) }}
                            </b>
                        </header>

                        <div class="driver-update-admin-values">
                            <div>
                                <span>Dato</span>
                                <strong>
                                    {{ str_replace('_', ' ', $updateRequest->field_name) }}
                                </strong>
                            </div>

                            <div>
                                <span>Valor actual</span>
                                <strong>
                                    {{ $updateRequest->current_value ?: 'Sin valor' }}
                                </strong>
                            </div>

                            <div>
                                <span>Valor solicitado</span>
                                <strong>{{ $updateRequest->requested_value }}</strong>
                            </div>
                        </div>

                        <p>
                            <strong>Motivo:</strong>
                            {{ $updateRequest->reason }}
                        </p>

                        @if ($updateRequest->evidence_path)
                            <a
                                href="{{ route(
                                    'admin.driver-update-requests.evidence',
                                    $updateRequest
                                ) }}"
                                target="_blank"
                                class="driver-review-btn driver-review-btn--light"
                            >
                                Ver evidencia
                            </a>
                        @endif

                        @if (in_array($updateRequest->status, ['pending', 'under_review'], true))
                            <div class="driver-update-admin-actions">
                                <form
                                    method="POST"
                                    action="{{ route(
                                        'admin.driver-update-requests.approve',
                                        $updateRequest
                                    ) }}"
                                >
                                    @csrf

                                    <input
                                        type="text"
                                        name="admin_notes"
                                        placeholder="Nota administrativa opcional"
                                    >

                                    <button class="driver-review-btn driver-review-btn--approve">
                                        Aprobar y aplicar
                                    </button>
                                </form>

                                <form
                                    method="POST"
                                    action="{{ route(
                                        'admin.driver-update-requests.reject',
                                        $updateRequest
                                    ) }}"
                                >
                                    @csrf

                                    <input
                                        type="text"
                                        name="admin_notes"
                                        placeholder="Motivo del rechazo"
                                        required
                                    >

                                    <button class="driver-review-btn driver-review-btn--reject">
                                        Rechazar
                                    </button>
                                </form>
                            </div>
                        @elseif ($updateRequest->admin_notes)
                            <p>
                                <strong>Resolución:</strong>
                                {{ $updateRequest->admin_notes }}
                            </p>
                        @endif
                    </article>
                @empty
                    <div class="driver-review-empty">
                        <span>📝</span>
                        <strong>No hay solicitudes</strong>
                        <p>
                            Las solicitudes enviadas por los repartidores aparecerán aquí.
                        </p>
                    </div>
                @endforelse
            </div>

            @if ($requests->hasPages())
                <div class="driver-review__pagination">
                    {{ $requests->links() }}
                </div>
            @endif
        </section>
    </div>
</section>
@endsection
