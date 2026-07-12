@php($portal = 'admin')

@extends('layouts.app')

@section('title', 'PETPAY-CARD | Validación de identidad')

@section('content')
<style>
.kyc-admin{display:grid;grid-template-columns:56px minmax(0,1fr);gap:8px;padding:24px 28px 28px 8px;background:#fff;min-height:calc(100vh - 72px)}
.kyc-admin-main{padding:26px;border:1px solid rgba(15,23,42,.09);border-radius:28px;background:#fff;box-shadow:0 20px 60px rgba(15,23,42,.08)}
.kyc-admin-head{display:flex;justify-content:space-between;gap:18px;align-items:flex-start}
.kyc-admin-head h1{margin:0;font-size:34px;letter-spacing:-.05em}.kyc-admin-head p{margin:8px 0 0;color:#64748b}
.kyc-filter{display:flex;gap:8px;flex-wrap:wrap;margin:18px 0}.kyc-filter a{padding:8px 12px;border-radius:999px;background:#f1f5f9;color:#334155;text-decoration:none;font-size:11px;font-weight:800}.kyc-filter a.active{background:#0f172a;color:#fff}
.kyc-list{display:grid;gap:14px}.kyc-card{border:1px solid #e2e8f0;border-radius:18px;padding:16px;background:#fff}.kyc-card-head{display:flex;justify-content:space-between;gap:16px}.kyc-card h2{margin:0;font-size:18px}.kyc-card p{margin:5px 0 0;color:#64748b;font-size:12px}
.kyc-badges{display:flex;gap:6px;flex-wrap:wrap;margin-top:10px}.kyc-badge{padding:5px 8px;border-radius:999px;background:#f8fafc;border:1px solid #e2e8f0;font-size:10px;font-weight:800}
.kyc-docs{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:8px;margin-top:14px}.kyc-doc{border:1px solid #e2e8f0;border-radius:12px;padding:10px}.kyc-doc strong{display:block;font-size:12px}.kyc-doc small{display:block;color:#64748b;margin-top:3px}
.kyc-doc-actions,.kyc-actions{display:flex;gap:6px;flex-wrap:wrap;margin-top:9px}.kyc-btn{min-height:34px;border:0;border-radius:8px;padding:0 10px;font-size:10px;font-weight:800;cursor:pointer;text-decoration:none;display:inline-flex;align-items:center}.kyc-btn.view{background:#e2e8f0;color:#0f172a}.kyc-btn.approve{background:#16a34a;color:#fff}.kyc-btn.reject{background:#dc2626;color:#fff}.kyc-btn.review{background:#0f172a;color:#fff}
.kyc-review-form{display:grid;grid-template-columns:1fr auto auto;gap:8px;margin-top:12px}.kyc-review-form textarea{min-height:70px;border:1px solid #cbd5e1;border-radius:10px;padding:10px}
@media(max-width:800px){.kyc-admin{grid-template-columns:1fr;padding:14px}.kyc-docs{grid-template-columns:1fr}.kyc-card-head{flex-direction:column}.kyc-review-form{grid-template-columns:1fr}}
</style>

<section class="petpay-dashboard kyc-admin">
    @include('partials.sidebars.admin')

    <div class="kyc-admin-main">
        <div class="kyc-admin-head">
            <div>
                <h1>Identidad y representación legal</h1>
                <p>Revisa documentos, identidad del firmante y facultades antes de habilitar contratos.</p>
            </div>
            <a class="kyc-btn view" href="{{ route('admin.dashboard') }}">Regresar</a>
        </div>

        @if (session('status'))
            <div style="margin-top:14px;padding:12px;border-radius:10px;background:#ecfdf5;color:#166534;font-weight:800">{{ session('status') }}</div>
        @endif

        <div class="kyc-filter">
            @foreach (['submitted' => 'Pendientes', 'under_review' => 'En revisión', 'corrections_required' => 'Correcciones', 'approved' => 'Aprobados', 'rejected' => 'Rechazados', 'all' => 'Todos'] as $key => $label)
                <a class="{{ $status === $key ? 'active' : '' }}" href="{{ route('admin.identities.index', ['status' => $key]) }}">
                    {{ $label }} ({{ number_format($counts[$key] ?? ($key === 'all' ? $counts->sum() : 0)) }})
                </a>
            @endforeach
        </div>

        <div class="kyc-list">
            @forelse ($profiles as $profile)
                <article class="kyc-card">
                    <div class="kyc-card-head">
                        <div>
                            <h2>{{ $profile->business_legal_name ?: $profile->commerce?->business_name }}</h2>
                            <p>{{ $profile->representative_name }} · {{ $profile->representative_rfc }} · {{ $profile->person_type === 'company' ? 'Persona moral' : 'Persona física' }}</p>
                            <div class="kyc-badges">
                                <span class="kyc-badge">Estatus: {{ str_replace('_', ' ', $profile->status) }}</span>
                                <span class="kyc-badge">RFC negocio: {{ $profile->business_rfc ?: 'Pendiente' }}</span>
                                <span class="kyc-badge">UUID: {{ $profile->uuid }}</span>
                            </div>
                        </div>
                        <form method="POST" action="{{ route('admin.identities.start', $profile) }}">
                            @csrf
                            <button class="kyc-btn review" type="submit">Iniciar revisión</button>
                        </form>
                    </div>

                    <div class="kyc-docs">
                        @foreach ($profile->documents->whereNotIn('status', ['replaced']) as $document)
                            <div class="kyc-doc">
                                <strong>{{ str_replace('_', ' ', ucfirst($document->document_type)) }}</strong>
                                <small>{{ $document->original_name }} · {{ strtoupper($document->status) }}</small>
                                <div class="kyc-doc-actions">
                                    <a class="kyc-btn view" target="_blank" href="{{ route('admin.identities.documents.show', $document) }}">Ver</a>
                                    <form method="POST" action="{{ route('admin.identities.documents.review', [$profile, $document]) }}">
                                        @csrf
                                        <input type="hidden" name="decision" value="approved">
                                        <button class="kyc-btn approve" type="submit">Aprobar</button>
                                    </form>
                                    <form method="POST" action="{{ route('admin.identities.documents.review', [$profile, $document]) }}">
                                        @csrf
                                        <input type="hidden" name="decision" value="rejected">
                                        <input type="hidden" name="review_notes" value="Documento rechazado. Sustituye el archivo por una versión legible y vigente.">
                                        <button class="kyc-btn reject" type="submit">Rechazar</button>
                                    </form>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <form class="kyc-review-form" method="POST" action="{{ route('admin.identities.corrections', $profile) }}">
                        @csrf
                        <textarea name="review_notes" required placeholder="Motivo de corrección o rechazo"></textarea>
                        <button class="kyc-btn review" type="submit">Solicitar corrección</button>
                        <button class="kyc-btn reject" type="submit" formaction="{{ route('admin.identities.reject', $profile) }}">Rechazar expediente</button>
                    </form>

                    <form method="POST" action="{{ route('admin.identities.approve', $profile) }}" style="margin-top:10px">
                        @csrf
                        <button class="kyc-btn approve" type="submit">Aprobar identidad y habilitar firma</button>
                    </form>
                </article>
            @empty
                <div style="padding:30px;text-align:center;border:1px dashed #cbd5e1;border-radius:16px;color:#64748b">No hay expedientes para este filtro.</div>
            @endforelse
        </div>

        <div style="margin-top:16px">{{ $profiles->links() }}</div>
    </div>
</section>
@endsection
