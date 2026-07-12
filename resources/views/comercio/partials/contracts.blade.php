@php
    $contractsCollection = collect($contracts ?? []);
    $contractsSigned = $contractsCollection->where('status', 'signed')->count();
    $contractsPending = $contractsCollection->whereNotIn('status', ['signed', 'cancelled'])->count();
    $lastUpdate = $contractsCollection->max('updated_at');
    $groups = [
        'corporate' => ['title' => 'Contratos Corporativos', 'subtitle' => 'Acuerdos de operación y uso de plataforma'],
        'compliance' => ['title' => 'Cumplimiento Normativo', 'subtitle' => 'Privacidad, consumidor y uso de marca'],
        'financial' => ['title' => 'Información Financiera', 'subtitle' => 'Liquidaciones, cuenta bancaria y datos fiscales'],
    ];
@endphp

<div class="commerce-tab-panel {{ ($activeTab ?? 'usuarios') === 'contratos' ? 'is-active' : '' }}" data-commerce-tab-panel="contratos">
    <section class="growth-shell">
        <div class="growth-titlebar plain">
            <div>
                <h2>Contratos</h2>
                <p>Carga, consulta, firma y descarga de contratos necesarios para operar en Petpay.</p>
            </div>
            <button type="button" class="growth-primary-button" data-contract-open-form>Nuevo contrato</button>
        </div>

        <section class="growth-card">
            <div class="contract-filter-grid">
                <label><span>Buscar contrato o UUID</span><input type="search" data-contract-search placeholder="Buscar por contrato, UUID o firmante"></label>
                <label><span>Año</span><select data-contract-year><option value="">Todos</option>@foreach ($contractsCollection->pluck('document_year')->filter()->unique()->sortDesc() as $year)<option value="{{ $year }}">{{ $year }}</option>@endforeach</select></label>
                <label><span>Estatus</span><select data-contract-status><option value="">Todos</option><option value="pending_signature">Pendiente firma</option><option value="pending_review">En revisión</option><option value="signed">Firmado</option><option value="rejected">Rechazado</option><option value="expired">Vencido</option></select></label>
                <label><span>Tipo</span><select data-contract-type><option value="">Todos</option>@foreach ($contractsCollection->pluck('contract_type')->filter()->unique()->sort() as $type)<option value="{{ $type }}">{{ ucfirst(str_replace('_', ' ', $type)) }}</option>@endforeach</select></label>
                <label><span>Versión</span><select data-contract-version><option value="">Todas</option>@foreach ($contractsCollection->pluck('version')->filter()->unique()->sortDesc() as $version)<option value="{{ $version }}">{{ $version }}</option>@endforeach</select></label>
                <a class="growth-download-button" href="{{ route('comercio.contracts.download.zip') }}">Descargar ZIP</a>
            </div>

            <div class="contract-summary-grid">
                <article><span>Contratos requeridos</span><strong>{{ $contractsCollection->where('is_required', true)->count() }}</strong></article>
                <article><span>Contratos firmados</span><strong>{{ $contractsSigned }}</strong></article>
                <article><span>Contratos pendientes</span><strong>{{ $contractsPending }}</strong></article>
                <article><span>Última actualización</span><strong>{{ $lastUpdate ? \Illuminate\Support\Carbon::parse($lastUpdate)->format('d M Y') : 'Sin cambios' }}</strong></article>
            </div>
        </section>


        @php
            $identityDocuments = collect($identityProfile?->documents ?? [])->whereNotIn('status', ['replaced']);
            $individualDocuments = [
                'ine_front' => 'INE frente',
                'ine_back' => 'INE reverso',
                'proof_address' => 'Comprobante de domicilio',
                'tax_certificate' => 'Constancia de situación fiscal',
                'selfie' => 'Selfie del titular',
                'liveness' => 'Prueba de vida',
            ];
            $companyDocuments = [
                'ine_front' => 'INE frente del representante',
                'ine_back' => 'INE reverso del representante',
                'proof_address' => 'Comprobante de domicilio fiscal',
                'tax_certificate' => 'Constancia fiscal de la empresa',
                'representative_tax_certificate' => 'Constancia fiscal del representante',
                'selfie' => 'Selfie del representante',
                'liveness' => 'Prueba de vida del representante',
                'articles_incorporation' => 'Acta constitutiva',
                'power_of_attorney' => 'Poder notarial',
            ];
            $allIdentityDocuments = $individualDocuments + $companyDocuments;
            $identityReady = $identityProfile?->isReadyForSignature() ?? false;
            $currentPersonType = old('person_type', $identityProfile?->person_type ?? 'individual');
        @endphp

        <section class="identity-security-card" data-identity-root>
            <div class="identity-security-head">
                <div>
                    <span class="identity-security-kicker">Expediente protegido</span>
                    <h3>Identidad y representación legal</h3>
                    <p data-identity-intro>
                        {{ $currentPersonType === 'company'
                            ? 'Validaremos a la empresa y las facultades de su representante legal.'
                            : 'Validaremos al titular que firma y opera por cuenta propia.' }}
                    </p>
                </div>
                <span class="identity-security-status is-{{ $identityProfile?->status ?? 'draft' }}">
                    {{ str_replace('_', ' ', ucfirst($identityProfile?->status ?? 'draft')) }}
                </span>
            </div>

            @if ($identityProfile?->review_notes)
                <div class="identity-review-note">{{ $identityProfile->review_notes }}</div>
            @endif

            <div class="identity-security-summary">
                <article>
                    <span data-identity-summary-person-label>{{ $currentPersonType === 'company' ? 'Representante legal' : 'Titular' }}</span>
                    <strong>{{ $identityProfile?->representative_name ?: 'Pendiente' }}</strong>
                </article>
                <article><span>RFC</span><strong>{{ $identityProfile?->representative_rfc ?: 'Pendiente' }}</strong></article>
                <article><span>Documentos</span><strong>{{ $identityDocuments->where('status', 'approved')->count() }}/{{ count($identityProfile?->requiredDocumentTypes() ?? []) }}</strong></article>
                <article><span>Firma</span><strong>{{ $identityReady ? 'Habilitada' : 'Bloqueada' }}</strong></article>
            </div>

            @if (! $identityReady)
                <details class="identity-wizard" {{ in_array($identityProfile?->status, ['draft', 'corrections_required', 'rejected'], true) ? 'open' : '' }}>
                    <summary>Completar expediente de seguridad</summary>

                    <form method="POST" action="{{ route('comercio.identity.profile.save') }}" class="identity-profile-form">
                        @csrf

                        <div class="identity-person-selector">
                            <label class="identity-person-option">
                                <input type="radio" name="person_type" value="individual" @checked($currentPersonType === 'individual')>
                                <span>
                                    <strong>Persona física</strong>
                                    <small>El titular firma y opera por cuenta propia.</small>
                                </span>
                            </label>

                            <label class="identity-person-option">
                                <input type="radio" name="person_type" value="company" @checked($currentPersonType === 'company')>
                                <span>
                                    <strong>Persona moral</strong>
                                    <small>Una empresa firma mediante representante legal.</small>
                                </span>
                            </label>
                        </div>

                        <div class="identity-form-section">
                            <div class="identity-form-section-head">
                                <strong data-identity-business-title>Datos del titular</strong>
                                <small data-identity-business-help>Captura los datos fiscales de la persona física.</small>
                            </div>

                            <div class="identity-grid">
                                <label>
                                    <span data-identity-legal-name-label>Nombre legal completo</span>
                                    <input name="business_legal_name" value="{{ old('business_legal_name', $identityProfile?->business_legal_name) }}" required>
                                </label>
                                <label>
                                    <span data-identity-business-rfc-label>RFC del titular</span>
                                    <input name="business_rfc" value="{{ old('business_rfc', $identityProfile?->business_rfc) }}" required>
                                </label>
                                <label class="wide">
                                    <span data-identity-address-label>Domicilio del titular</span>
                                    <input name="address_line" value="{{ old('address_line', $identityProfile?->address_line) }}" required>
                                </label>
                                <label><span>Código postal</span><input name="postal_code" value="{{ old('postal_code', $identityProfile?->postal_code) }}" required></label>
                                <label><span>Estado</span><input name="state" value="{{ old('state', $identityProfile?->state) }}" required></label>
                                <label><span>Municipio</span><input name="municipality" value="{{ old('municipality', $identityProfile?->municipality) }}" required></label>
                            </div>
                        </div>

                        <div class="identity-form-section">
                            <div class="identity-form-section-head">
                                <strong data-identity-representative-title>Datos del titular firmante</strong>
                                <small data-identity-representative-help>Estos datos deben coincidir con INE, CURP, RFC y e.firma.</small>
                            </div>

                            <div class="identity-grid">
                                <label><span data-identity-person-name-label>Nombre completo</span><input name="representative_name" value="{{ old('representative_name', $identityProfile?->representative_name) }}" required></label>
                                <label><span data-identity-person-rfc-label>RFC del titular</span><input name="representative_rfc" value="{{ old('representative_rfc', $identityProfile?->representative_rfc) }}" required></label>
                                <label><span>CURP</span><input name="representative_curp" value="{{ old('representative_curp', $identityProfile?->representative_curp) }}" required></label>
                                <label><span>Correo</span><input type="email" name="representative_email" value="{{ old('representative_email', $identityProfile?->representative_email) }}" required></label>
                                <label><span>Teléfono</span><input name="representative_phone" value="{{ old('representative_phone', $identityProfile?->representative_phone) }}" required></label>
                                <label data-company-only-field><span>Cargo dentro de la empresa</span><input name="representative_position" value="{{ old('representative_position', $identityProfile?->representative_position) }}" data-company-required></label>
                            </div>
                        </div>

                        <div class="identity-form-section identity-company-section" data-company-only-section>
                            <div class="identity-form-section-head">
                                <strong>Constitución y facultades legales</strong>
                                <small>La información debe coincidir con el acta constitutiva y el poder notarial.</small>
                            </div>

                            <div class="identity-grid">
                                <label><span>Escritura o instrumento notarial</span><input name="notarial_deed_number" value="{{ old('notarial_deed_number', $identityProfile?->notarial_deed_number) }}" data-company-required></label>
                                <label><span>Fecha de constitución</span><input type="date" name="incorporation_date" value="{{ old('incorporation_date', $identityProfile?->incorporation_date?->format('Y-m-d')) }}" data-company-required></label>
                                <label><span>Nombre del notario</span><input name="notary_name" value="{{ old('notary_name', $identityProfile?->notary_name) }}" data-company-required></label>
                                <label><span>Número de notaría</span><input name="notary_number" value="{{ old('notary_number', $identityProfile?->notary_number) }}" data-company-required></label>
                                <label class="wide"><span>Alcance de las facultades</span><textarea name="legal_powers_scope" data-company-required>{{ old('legal_powers_scope', $identityProfile?->legal_powers_scope) }}</textarea></label>
                            </div>
                        </div>

                        <div class="identity-consents">
                            <label data-company-only-field>
                                <input type="checkbox" name="powers_declared_current" value="1" @checked($identityProfile?->powers_declared_current) data-company-required>
                                Declaro que las facultades del representante legal están vigentes y no han sido revocadas.
                            </label>
                            <label data-individual-only-field>
                                <input type="checkbox" checked disabled>
                                Declaro que actúo y firmo por cuenta propia.
                            </label>
                            <label><input type="checkbox" name="data_processing_consent" value="1" @checked($identityProfile?->data_processing_consent) required> Autorizo el tratamiento de datos para validar identidad y prevenir fraude.</label>
                            <label><input type="checkbox" name="truth_declaration" value="1" @checked($identityProfile?->truth_declaration) required> Declaro bajo protesta que la información y documentos son auténticos.</label>
                        </div>

                        <button class="identity-primary" type="submit">Guardar datos del expediente</button>
                    </form>

                    <div class="identity-document-heading">
                        <div>
                            <strong data-identity-documents-title>Documentos de persona física</strong>
                            <small data-identity-documents-help>Sube archivos legibles, completos y vigentes.</small>
                        </div>
                    </div>

                    <div class="identity-document-grid">
                        @foreach ($allIdentityDocuments as $type => $label)
                            @php
                                $document = $identityDocuments->where('document_type', $type)->sortByDesc('id')->first();
                                $companyOnly = in_array($type, ['representative_tax_certificate', 'articles_incorporation', 'power_of_attorney'], true);
                            @endphp

                            <form
                                method="POST"
                                action="{{ route('comercio.identity.documents.store') }}"
                                enctype="multipart/form-data"
                                class="identity-document-card"
                                data-document-scope="{{ $companyOnly ? 'company' : 'all' }}"
                            >
                                @csrf
                                <input type="hidden" name="document_type" value="{{ $type }}">
                                <strong
                                    @if ($type === 'ine_front') data-dynamic-document-label="ine_front" @endif
                                    @if ($type === 'ine_back') data-dynamic-document-label="ine_back" @endif
                                    @if ($type === 'proof_address') data-dynamic-document-label="proof_address" @endif
                                    @if ($type === 'tax_certificate') data-dynamic-document-label="tax_certificate" @endif
                                    @if ($type === 'selfie') data-dynamic-document-label="selfie" @endif
                                    @if ($type === 'liveness') data-dynamic-document-label="liveness" @endif
                                >{{ $label }}</strong>
                                <small>{{ $document ? strtoupper($document->status) : 'PENDIENTE' }}</small>

                                @if ($document)
                                    <a target="_blank" href="{{ route('comercio.identity.documents.show', $document) }}">Ver archivo</a>
                                @endif

                                <input type="file" name="document" accept=".pdf,.jpg,.jpeg,.png,.webp,.mp4,.mov" required>
                                <button type="submit">Cargar / reemplazar</button>
                            </form>
                        @endforeach
                    </div>

                    <div class="identity-challenge">
                        <strong>Prueba de vida solicitada</strong>
                        <span>{{ $identityProfile?->liveness_challenge }}</span>
                    </div>

                    <form method="POST" action="{{ route('comercio.identity.submit') }}" class="identity-submit-form">
                        @csrf
                        <button type="submit">Enviar expediente a revisión</button>
                    </form>
                </details>
            @else
                <div class="identity-approved-box">
                    <strong>Identidad aprobada</strong>
                    <span>Expediente bloqueado con huella {{ substr((string) $identityProfile->identity_hash, 0, 18) }}…</span>
                </div>
            @endif
        </section>

        <div class="contract-groups">
            @foreach ($groups as $groupKey => $group)
                @php
                    $groupContracts = $contractsCollection->where('group_key', $groupKey);
                    $groupSigned = $groupContracts->where('status', 'signed')->count();
                    $groupRequired = $groupContracts->where('is_required', true)->count();
                    $groupOptional = $groupContracts->where('is_required', false)->count();
                @endphp

                <section class="contract-group" data-contract-group>
                    <button type="button" class="contract-group-head" data-contract-group-toggle>
                        <div>
                            <strong>{{ $group['title'] }}</strong>
                            <small>{{ $groupRequired }} requeridos · {{ $groupOptional }} opcionales</small>
                        </div>
                        <div class="contract-group-progress">
                            <strong>{{ $groupSigned }}/{{ $groupContracts->count() }} firmados</strong>
                            <small>Documentos vigentes {{ now()->year }}</small>
                            <span>⌄</span>
                        </div>
                    </button>

                    <div class="contract-group-body" hidden>
                        @forelse ($groupContracts as $contract)
                            <article class="contract-item" data-contract-card
                                data-search="{{ mb_strtolower($contract->title.' '.$contract->uuid.' '.$contract->representative_name, 'UTF-8') }}"
                                data-status="{{ $contract->status }}"
                                data-year="{{ $contract->document_year }}"
                                data-type="{{ $contract->contract_type }}"
                                data-version="{{ $contract->version }}">
                                <div class="contract-item-main">
                                    <span class="growth-status is-{{ $contract->status }}">{{ str_replace('_', ' ', ucfirst($contract->status)) }}</span>
                                    <h4>{{ $contract->title }}</h4>
                                    <p>{{ $contract->uuid }}</p>
                                    <div class="contract-badges">
                                        <span>Versión {{ $contract->version }}</span>
                                        <span>{{ $contract->is_required ? 'Requerido' : 'Opcional' }}</span>
                                        @if ($contract->effective_to)<span>Vence {{ $contract->effective_to->format('d/m/Y') }}</span>@endif
                                    </div>
                                </div>

                                <div class="contract-item-info">
                                    <span><small>Firmante</small><strong>{{ $contract->representative_name ?: 'Pendiente' }}</strong></span>
                                    <span><small>Firma</small><strong>{{ $contract->signed_at?->format('d/m/Y H:i') ?? 'Pendiente' }}</strong></span>
                                    <span><small>IP</small><strong>{{ $contract->signed_ip ?: '—' }}</strong></span>
                                    <span><small>Documentos</small><strong>{{ $contract->documents->count() }}</strong></span>
                                </div>

                                <div class="contract-item-actions">
                                    @if ($contract->status !== 'signed')
                                        <button type="button" data-contract-upload-open="{{ $contract->id }}">Adjuntar</button>
                                    @endif

                                    @if (in_array($contract->status, ['draft', 'pending_signature'], true))
                                        @if ($identityReady)
                                            <button type="button" data-contract-sign-open="{{ $contract->id }}" data-contract-title="{{ $contract->title }}">Firmar</button>
                                        @else
                                            <button type="button" disabled title="Completa y aprueba el expediente de identidad">Firma bloqueada</button>
                                        @endif
                                    @endif

                                    @if ($contract->original_path)
                                        <a href="{{ route('comercio.contracts.download', [$contract, 'original']) }}">Descargar</a>
                                    @endif
                                </div>

                                <form method="POST" action="{{ route('comercio.contracts.documents.store', $contract) }}" enctype="multipart/form-data" class="contract-inline-form" data-contract-upload-form="{{ $contract->id }}" hidden>
                                    @csrf
                                    <select name="document_type" required><option value="identification">Identificación</option><option value="power">Poder</option><option value="tax_certificate">Constancia fiscal</option><option value="address">Comprobante domicilio</option><option value="annex">Anexo</option></select>
                                    <input type="file" name="document" required>
                                    <button type="submit">Subir documento</button>
                                </form>

                                <div class="contract-inline-form contract-sign-launcher" data-contract-sign-form="{{ $contract->id }}" hidden>
                                    <button
                                        type="button"
                                        class="contract-open-signature"
                                        data-signature-modal-open
                                        data-signature-action="{{ route('comercio.contracts.sign', $contract) }}"
                                        data-signature-title="{{ $contract->title }}"
                                        data-signature-name="{{ $contract->representative_name }}"
                                        data-signature-position="{{ $contract->representative_position }}"
                                    >Abrir sistema de firma</button>
                                </div>
                            </article>
                        @empty
                            <div class="growth-empty">No hay contratos en esta sección.</div>
                        @endforelse
                    </div>
                </section>
            @endforeach
        </div>
    </section>

    <div class="growth-modal" data-contract-modal hidden>
        <div class="growth-modal-card">
            <div class="growth-modal-head">
                <div><h3>Nuevo contrato adicional</h3><p>Registra anexos o documentos propios del comercio.</p></div>
                <button type="button" data-contract-close-form>×</button>
            </div>

            <form method="POST" action="{{ route('comercio.contracts.store') }}" enctype="multipart/form-data" class="growth-form">
                @csrf
                <div class="growth-form-grid">
                    <label class="wide"><span>Título</span><input type="text" name="title" required></label>
                    <label><span>Grupo</span><select name="group_key" required><option value="corporate">Corporativos</option><option value="compliance">Cumplimiento</option><option value="financial">Financieros</option></select></label>
                    <label><span>Tipo</span><select name="contract_type" required><option value="commercial">Comercial</option><option value="terms">Términos</option><option value="privacy">Privacidad</option><option value="service">Servicios</option><option value="annex">Anexo</option></select></label>
                    <label><span>Versión</span><input type="text" name="version" value="1.0" required></label>
                    <label><span>Sucursal</span><select name="branch_id"><option value="">Todas</option>@foreach (collect($branches ?? []) as $branch)<option value="{{ $branch->id }}">{{ $branch->branch_name }}</option>@endforeach</select></label>
                    <label><span>Representante</span><input type="text" name="representative_name"></label>
                    <label><span>Correo</span><input type="email" name="representative_email"></label>
                    <label><span>Cargo</span><input type="text" name="representative_position"></label>
                    <label><span>Vigencia desde</span><input type="date" name="effective_from"></label>
                    <label><span>Vigencia hasta</span><input type="date" name="effective_to"></label>
                    <label class="wide"><span>Archivo original</span><input type="file" name="original_file" accept=".pdf,.doc,.docx"></label>
                    <label class="wide"><span>Notas</span><textarea name="notes"></textarea></label>
                </div>
                <div class="growth-modal-actions">
                    <button type="button" class="secondary" data-contract-close-form>Cancelar</button>
                    <button type="submit" class="primary">Guardar contrato</button>
                </div>
            </form>
        </div>
    </div>
    <div class="growth-modal signature-modal" data-signature-modal hidden>
        <div class="growth-modal-card signature-modal-card">
            <div class="growth-modal-head">
                <div>
                    <h3>Firmar contrato</h3>
                    <p data-signature-contract-title>Selecciona un método de firma.</p>
                </div>
                <button type="button" data-signature-modal-close>×</button>
            </div>

            <form method="POST" enctype="multipart/form-data" data-signature-form>
                @csrf
                <input type="hidden" name="acceptance" value="1">
                <input type="hidden" name="signature_method" value="drawn" data-signature-method-input>
                <input type="hidden" name="signature_data" data-signature-data>
                <input type="hidden" name="camera_data" data-camera-data>

                <div class="signature-identity-grid">
                    <label><span>Nombre completo del representante</span><input type="text" name="representative_name" data-signature-representative required></label>
                    <label><span>Cargo</span><input type="text" name="representative_position" data-signature-position></label>
                </div>

                <div class="signature-methods">
                    <button type="button" class="is-active" data-signature-method="drawn">En pantalla</button>
                    <button type="button" data-signature-method="camera">Cámara web</button>
                    <button type="button" data-signature-method="uploaded">Subir firma</button>
                    <button type="button" data-signature-method="certificate">e.firma / FIEL</button>
                </div>

                <section class="signature-panel is-active" data-signature-panel="drawn">
                    <p>Dibuja tu firma con mouse, touch o stylus.</p>
                    <canvas width="760" height="240" data-signature-canvas></canvas>
                    <button type="button" class="signature-secondary-button" data-signature-clear>Limpiar firma</button>
                </section>

                <section class="signature-panel" data-signature-panel="camera" hidden>
                    <p>Activa la cámara y captura evidencia del firmante.</p>
                    <div class="signature-camera-grid">
                        <video autoplay playsinline muted data-signature-video></video>
                        <canvas width="640" height="480" data-signature-camera-canvas hidden></canvas>
                        <img alt="Captura de cámara" data-signature-camera-preview hidden>
                    </div>
                    <div class="signature-camera-actions">
                        <button type="button" class="signature-secondary-button" data-signature-camera-start>Activar cámara</button>
                        <button type="button" class="signature-secondary-button" data-signature-camera-capture>Capturar</button>
                    </div>
                </section>

                <section class="signature-panel" data-signature-panel="uploaded" hidden>
                    <p>Sube una imagen PNG o JPG de la firma.</p>
                    <input type="file" name="signature_file" accept=".png,.jpg,.jpeg">
                </section>

                <section class="signature-panel" data-signature-panel="certificate" hidden>
                    <div class="signature-certificate-note">La llave privada y la contraseña se usan solo durante la firma y no se almacenan.</div>
                    <div class="signature-certificate-grid">
                        <label><span>Certificado .cer</span><input type="file" name="cer_file" accept=".cer"></label>
                        <label><span>Llave privada .key</span><input type="file" name="key_file" accept=".key"></label>
                        <label><span>Contraseña</span><input type="password" name="key_password" autocomplete="new-password"></label>
                    </div>
                </section>

                <label class="signature-acceptance">
                    <input type="checkbox" required>
                    <span>Confirmo que soy el firmante autorizado y acepto el contenido del contrato.</span>
                </label>

                <div class="growth-modal-actions">
                    <button type="button" class="secondary" data-signature-modal-close>Cancelar</button>
                    <button type="submit" class="primary">Firmar y generar PDF</button>
                </div>
            </form>
        </div>
    </div>

</div>