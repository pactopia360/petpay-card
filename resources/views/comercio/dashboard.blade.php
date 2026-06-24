@extends('layouts.app')

@php
    $portal = 'comercio';

    $contactsCollection = collect($contacts ?? []);
    $contactsCount = $contactsCollection->count();

    $verifiedPhonesCount = $contactsCollection->filter(function ($contact) {
        return ! empty($contact->phone_verified_at);
    })->count();

    $verifiedEmailsCount = $contactsCollection->filter(function ($contact) {
        return ! empty($contact->email_verified_at);
    })->count();

    $primaryContact = $contactsCollection->firstWhere('is_primary', true);
    $latestContactUpdate = $contactsCollection->sortByDesc('updated_at')->first();

    $latestContactUpdateLabel = ($latestContactUpdate && ! empty($latestContactUpdate->updated_at))
        ? $latestContactUpdate->updated_at->format('d M Y')
        : 'Sin registros';
@endphp

@section('title', 'PETPAY-CARD | Panel administrador de comercio')

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/petpay-card/css/portals/comercio.css') }}?v=20260624">
@endpush

@section('content')
    <section class="commerce-admin" data-active-tab="{{ $activeTab ?? 'usuarios' }}">
        <figure class="commerce-admin__hero" aria-label="Imagen principal del panel de administración de comercio">
            <img
                src="{{ asset('assets/petpay-card/img/comercio/comercio-hero.png') }}?v=20260622"
                alt="Pasillo de productos para mascotas dentro de un comercio Petpay"
                loading="eager"
            >
        </figure>

        <header class="commerce-admin__title">
            <h1>Panel de administración.</h1>
            <p>Registro, administración y operación de tiendas.</p>
        </header>

        <nav class="commerce-admin__tabs" aria-label="Secciones del panel administrador de comercio">
            <button
                type="button"
                class="commerce-admin__tab {{ ($activeTab ?? 'usuarios') === 'usuarios' ? 'is-active' : '' }}"
                data-commerce-tab-button="usuarios"
            >
                Usuarios
            </button>

            <button
                type="button"
                class="commerce-admin__tab {{ ($activeTab ?? 'usuarios') === 'sucursales' ? 'is-active' : '' }}"
                data-commerce-tab-button="sucursales"
            >
                Sucursales
            </button>

            <button type="button" class="commerce-admin__tab" disabled>Catálogos</button>
            <button type="button" class="commerce-admin__tab" disabled>Finanzas</button>
            <button type="button" class="commerce-admin__tab" disabled>Contratos</button>
        </nav>

        @if (session('status'))
            <div class="commerce-alert commerce-alert--success">
                {{ session('status') }}
            </div>
        @endif

        @if (session('warning'))
            <div class="commerce-alert commerce-alert--warning">
                {{ session('warning') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="commerce-alert commerce-alert--danger">
                {{ $errors->first() }}
            </div>
        @endif

        <div
            class="commerce-tab-panel {{ ($activeTab ?? 'usuarios') === 'usuarios' ? 'is-active' : '' }}"
            data-commerce-tab-panel="usuarios"
        >
            <div class="commerce-admin__section-title">
                <h2>Gestión de usuarios</h2>
                <p>Alta, baja o modificación de usuarios.</p>
            </div>

            @php
                $contactsKpiCollection = collect($contacts ?? []);

                $contactsKpiCount = $contactsKpiCollection->count();

                $contactsKpiVerifiedPhones = $contactsKpiCollection->filter(function ($contact) {
                    return ! empty($contact->phone_verified_at);
                })->count();

                $contactsKpiVerifiedEmails = $contactsKpiCollection->filter(function ($contact) {
                    return ! empty($contact->email_verified_at);
                })->count();

                $contactsKpiLatestUpdate = $contactsKpiCollection->sortByDesc('updated_at')->first();

                $contactsKpiLatestUpdateLabel = ($contactsKpiLatestUpdate && ! empty($contactsKpiLatestUpdate->updated_at))
                    ? $contactsKpiLatestUpdate->updated_at->format('d M Y')
                    : 'Sin registros';
            @endphp

            <section class="commerce-kpis" aria-label="Indicadores del módulo de usuarios">
                <article class="commerce-kpi-card">
                    <span class="commerce-kpi-card__label">Contactos registrados</span>
                    <strong class="commerce-kpi-card__value">{{ $contactsKpiCount }}</strong>
                </article>

                <article class="commerce-kpi-card">
                    <span class="commerce-kpi-card__label">Teléfonos verificados</span>
                    <strong class="commerce-kpi-card__value">{{ $contactsKpiVerifiedPhones }}</strong>
                </article>

                <article class="commerce-kpi-card">
                    <span class="commerce-kpi-card__label">Correos verificados</span>
                    <strong class="commerce-kpi-card__value">{{ $contactsKpiVerifiedEmails }}</strong>
                </article>

                <article class="commerce-kpi-card">
                    <span class="commerce-kpi-card__label">Última actualización</span>
                    <strong class="commerce-kpi-card__value">{{ $contactsKpiLatestUpdateLabel }}</strong>
                </article>
            </section>

            <section class="commerce-info-block">
                <div class="commerce-info-block__header">
                    <div class="commerce-info-block__content">
                        <h3 class="commerce-info-block__title">Registro de contacto administrativo</h3>
                        <p class="commerce-info-block__subtitle">
                            Captura la información del responsable o actualiza sus datos.
                        </p>
                    </div>

                    <div class="commerce-info-block__meta">
                        <strong class="commerce-info-block__meta-main">
                            {{ isset($primaryContact) && $primaryContact ? 'Principal asignado' : 'Pendiente de principal' }}
                        </strong>
                        <span class="commerce-info-block__meta-sub">
                            {{ isset($primaryContact) && $primaryContact ? $primaryContact->full_name : 'Aún no hay contacto principal' }}
                        </span>
                    </div>
                </div>

                <form
                    id="commerceContactForm"
                    class="commerce-admin__form"
                    method="POST"
                    action="{{ route('comercio.contacts.store') }}"
                    data-store-action="{{ route('comercio.contacts.store') }}"
                >
                    @csrf

                    <input type="hidden" name="_method" id="commerceContactMethod" value="POST">
                    <input type="hidden" name="phone_verified" id="phone_verified" value="0">
                    <input type="hidden" name="email_verified" id="email_verified" value="0">

                    <div class="commerce-admin__grid commerce-admin__grid--three">
                        <label class="commerce-field">
                            <span>Nombre</span>
                            <input
                                id="first_name"
                                type="text"
                                name="first_name"
                                value="{{ old('first_name', $comercio->first_name ?? '') }}"
                                autocomplete="given-name"
                                required
                            >
                        </label>

                        <label class="commerce-field">
                            <span>Apellido paterno</span>
                            <input
                                id="last_name_paternal"
                                type="text"
                                name="last_name_paternal"
                                value="{{ old('last_name_paternal', $comercio->last_name ?? '') }}"
                                autocomplete="family-name"
                            >
                        </label>

                        <label class="commerce-field">
                            <span>Apellido materno</span>
                            <input
                                id="last_name_maternal"
                                type="text"
                                name="last_name_maternal"
                                value="{{ old('last_name_maternal') }}"
                                autocomplete="additional-name"
                            >
                        </label>
                    </div>

                    <fieldset class="commerce-fieldset">
                        <legend class="commerce-fieldset__legend">Dirección de contacto</legend>

                        <div class="commerce-admin__grid commerce-admin__grid--address">
                            <label class="commerce-field">
                                <input
                                    id="street"
                                    type="text"
                                    name="street"
                                    value="{{ old('street', $comercio->business_address ?? '') }}"
                                    placeholder="Calle y número"
                                    autocomplete="street-address"
                                >
                            </label>

                            <label class="commerce-field">
                                <input
                                    id="neighborhood"
                                    type="text"
                                    name="neighborhood"
                                    value="{{ old('neighborhood') }}"
                                    placeholder="Colonia"
                                >
                            </label>

                            <label class="commerce-field">
                                <input
                                    id="postal_code"
                                    type="text"
                                    name="postal_code"
                                    value="{{ old('postal_code') }}"
                                    placeholder="CP"
                                    inputmode="numeric"
                                    maxlength="12"
                                    autocomplete="postal-code"
                                >
                            </label>

                            <label class="commerce-field">
                                <select id="state" name="state" aria-label="Estado">
                                    <option value="">Estado</option>
                                    @foreach ($states as $stateKey => $stateName)
                                        <option value="{{ $stateKey }}" @selected(old('state') === $stateKey)>
                                            {{ $stateName }}
                                        </option>
                                    @endforeach
                                </select>
                            </label>
                        </div>
                    </fieldset>

                    <div class="commerce-admin__grid commerce-admin__grid--bottom">
                        <label class="commerce-field">
                            <span>Teléfono</span>
                            <span class="commerce-verify">
                                <input
                                    id="phone"
                                    type="tel"
                                    name="phone"
                                    value="{{ old('phone', $comercio->phone ?? $comercio->business_phone ?? '') }}"
                                    autocomplete="tel"
                                    inputmode="numeric"
                                    maxlength="10"
                                >
                                <button type="button" class="commerce-verify__button" id="verifyPhoneButton">
                                    Verifica teléfono
                                </button>
                            </span>
                            <small class="commerce-field__hint" id="phoneHint"></small>
                        </label>

                        <label class="commerce-field">
                            <span>Correo electrónico</span>
                            <span class="commerce-verify">
                                <input
                                    id="email"
                                    type="email"
                                    name="email"
                                    value="{{ old('email', $comercio->email ?? $comercio->business_email ?? '') }}"
                                    autocomplete="email"
                                >
                                <button type="button" class="commerce-verify__button" id="verifyEmailButton">
                                    Verifica correo
                                </button>
                            </span>
                            <small class="commerce-field__hint" id="emailHint"></small>
                        </label>

                        <button type="submit" class="commerce-admin__save" id="commerceSaveButton">
                            Guardar
                        </button>
                    </div>

                    <div class="commerce-form-actions">
                        <button type="button" class="commerce-form-actions__reset" id="commerceResetButton" hidden>
                            Cancelar edición
                        </button>
                    </div>
                </form>
            </section>

            <section class="commerce-info-block commerce-info-block--contacts">
                <div class="commerce-info-block__header">
                    <div class="commerce-info-block__content">
                        <h3 class="commerce-info-block__title">Contactos registrados</h3>
                        <p class="commerce-info-block__subtitle">
                            Consulta, filtra, edita o elimina los usuarios administrativos guardados.
                        </p>
                    </div>

                    <div class="commerce-info-block__meta">
                        <strong class="commerce-info-block__meta-main">{{ $contactsKpiCount }} registrados</strong>
                        <span class="commerce-info-block__meta-sub">
                            {{ isset($primaryContact) && $primaryContact ? 'Principal: ' . $primaryContact->full_name : 'Sin contacto principal' }}
                        </span>
                    </div>
                </div>

                <div class="commerce-info-block__content-area">

            <section class="commerce-filter-block" aria-label="Filtro de usuarios administrativos">
                <div class="commerce-filter-grid">
                    <label class="commerce-filter-field commerce-filter-field--search">
                        <span>Buscar usuario o UUID</span>

                        <span class="commerce-filter-search">
                            <input
                                type="search"
                                id="commerceUserSearch"
                                placeholder="Buscar por nombre, correo, teléfono o UUID"
                                autocomplete="off"
                            >

                            <svg viewBox="0 0 24 24" fill="none" stroke-width="2" aria-hidden="true">
                                <circle cx="11" cy="11" r="7"></circle>
                                <path d="m20 20-3.5-3.5"></path>
                            </svg>
                        </span>
                    </label>

                    <label class="commerce-filter-field">
                        <span>Año</span>

                        <select id="commerceUserYear">
                            <option value="">Todos</option>
                            <option value="{{ now()->year }}">{{ now()->year }}</option>
                            <option value="{{ now()->subYear()->year }}">{{ now()->subYear()->year }}</option>
                        </select>
                    </label>

                    <label class="commerce-filter-field">
                        <span>Estatus</span>

                        <select id="commerceUserStatus">
                            <option value="">Todos</option>
                            <option value="principal">Principal</option>
                            <option value="completo">Completo</option>
                            <option value="pendiente">Pendiente</option>
                        </select>
                    </label>

                    <label class="commerce-filter-field">
                        <span>Tipo</span>

                        <select id="commerceUserType">
                            <option value="">Todos</option>
                            <option value="contacto">Contacto</option>
                            <option value="administrativo">Administrativo</option>
                        </select>
                    </label>

                    <label class="commerce-filter-field">
                        <span>Versión</span>

                        <select id="commerceUserVersion">
                            <option value="">Todas</option>
                            <option value="2026">2026</option>
                        </select>
                    </label>

                    <button type="button" class="commerce-filter-download" id="commerceUserDownloadButton">
                        <svg viewBox="0 0 24 24" fill="none" stroke-width="2" aria-hidden="true">
                            <path d="M12 3v12"></path>
                            <path d="m7 10 5 5 5-5"></path>
                            <path d="M5 21h14"></path>
                        </svg>
                        Descargar Excel
                    </button>
                </div>

                <p class="commerce-filter-empty" id="commerceUserFilterEmpty" hidden>
                    No hay usuarios que coincidan con los filtros seleccionados.
                </p>
            </section>

                    <div class="commerce-contacts">
                        @forelse ($contacts as $contact)
                            @php
                                $contactFilterText = trim(implode(' ', [
                                    $contact->id,
                                    $contact->full_name,
                                    $contact->phone,
                                    $contact->email,
                                    $contact->full_address,
                                ]));

                                $contactFilterSearch = mb_strtolower($contactFilterText, 'UTF-8');

                                $contactFilterYear = $contact->updated_at
                                    ? $contact->updated_at->format('Y')
                                    : now()->year;

                                $contactFilterIsComplete =
                                    filled($contact->first_name)
                                    && filled($contact->street)
                                    && filled($contact->neighborhood)
                                    && filled($contact->postal_code)
                                    && filled($contact->state)
                                    && filled($contact->phone)
                                    && filled($contact->email);

                                $contactFilterStatus = $contact->is_primary
                                    ? 'principal'
                                    : ($contactFilterIsComplete ? 'completo' : 'pendiente');
                            @endphp

                            <article
                                class="commerce-contact-card"
                                data-commerce-user-card
                                data-search="{{ e($contactFilterSearch) }}"
                                data-year="{{ e($contactFilterYear) }}"
                                data-status="{{ e($contactFilterStatus) }}"
                                data-type="contacto administrativo"
                                data-version="2026"
                                data-name="{{ e($contact->full_name ?: 'Sin nombre registrado') }}"
                                data-address="{{ e($contact->full_address ?: 'Sin dirección registrada') }}"
                                data-phone="{{ e($contact->phone ?: 'Sin teléfono') }}"
                                data-email="{{ e($contact->email ?: 'Sin correo') }}"
                                data-primary="{{ $contact->is_primary ? 'Sí' : 'No' }}"
                                data-phone-verified-label="{{ $contact->phone_verified_at ? 'Sí' : 'No' }}"
                                data-email-verified-label="{{ $contact->email_verified_at ? 'Sí' : 'No' }}"
                                data-updated-label="{{ $contact->updated_at ? $contact->updated_at->format('d/m/Y H:i') : 'Sin actualización' }}"
                            >
                                <div class="commerce-contact-card__top">
                                    <h3 class="commerce-contact-card__title">
                                        Contacto {{ $loop->iteration }}

                                        @if ($contact->is_primary)
                                            <span class="commerce-contact-card__badge">Principal</span>
                                        @endif
                                    </h3>

                                    <div class="commerce-contact-card__actions" aria-label="Acciones del contacto">
                                        <form
                                            method="POST"
                                            action="{{ route('comercio.contacts.destroy', $contact) }}"
                                            onsubmit="return confirm('¿Seguro que deseas eliminar este contacto?');"
                                        >
                                            @csrf
                                            @method('DELETE')

                                            <button type="submit" class="commerce-icon-button" aria-label="Eliminar contacto">
                                                <svg viewBox="0 0 24 24" fill="none" stroke-width="2">
                                                    <path d="M4 7h16"></path>
                                                    <path d="M10 11v6"></path>
                                                    <path d="M14 11v6"></path>
                                                    <path d="M6 7l1 14h10l1-14"></path>
                                                    <path d="M9 7V4h6v3"></path>
                                                </svg>
                                            </button>
                                        </form>

                                        <button
                                            type="button"
                                            class="commerce-icon-button commerce-edit-contact"
                                            aria-label="Editar contacto"
                                            data-update-action="{{ route('comercio.contacts.update', $contact) }}"
                                            data-first-name="{{ $contact->first_name }}"
                                            data-last-name-paternal="{{ $contact->last_name_paternal }}"
                                            data-last-name-maternal="{{ $contact->last_name_maternal }}"
                                            data-street="{{ $contact->street }}"
                                            data-neighborhood="{{ $contact->neighborhood }}"
                                            data-postal-code="{{ $contact->postal_code }}"
                                            data-state="{{ $contact->state }}"
                                            data-phone="{{ $contact->phone }}"
                                            data-email="{{ $contact->email }}"
                                            data-phone-verified="{{ $contact->phone_verified_at ? '1' : '0' }}"
                                            data-email-verified="{{ $contact->email_verified_at ? '1' : '0' }}"
                                        >
                                            <svg viewBox="0 0 24 24" fill="none" stroke-width="2">
                                                <path d="M12 20h9"></path>
                                                <path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L8 18l-4 1 1-4 11.5-11.5Z"></path>
                                            </svg>
                                        </button>
                                    </div>
                                </div>

                                <div class="commerce-contact-card__body">
                                    <p>
                                        <strong>Nombre:</strong>
                                        {{ $contact->full_name ?: 'Sin nombre registrado' }}
                                    </p>

                                    <p>
                                        <strong>Dirección:</strong>
                                        {{ $contact->full_address ?: 'Sin dirección registrada' }}
                                    </p>

                                    <p>
                                        <strong>Teléfono:</strong>
                                        {{ $contact->phone ?: 'Sin teléfono' }}

                                        @if ($contact->phone_verified_at)
                                            <span class="commerce-verified">Verificado</span>
                                        @endif
                                    </p>

                                    <p>
                                        <strong>Correo:</strong>
                                        {{ $contact->email ?: 'Sin correo' }}

                                        @if ($contact->email_verified_at)
                                            <span class="commerce-verified">Verificado</span>
                                        @endif
                                    </p>
                                </div>

                                <form
                                    method="POST"
                                    action="{{ route('comercio.contacts.primary', $contact) }}"
                                    class="commerce-contact-card__main"
                                >
                                    @csrf
                                    @method('PATCH')

                                    <label>
                                        <input
                                            type="checkbox"
                                            onchange="this.form.submit()"
                                            @checked($contact->is_primary)
                                        >
                                        <span>Guardar como contacto principal</span>
                                    </label>
                                </form>
                            </article>
                        @empty
                            <article class="commerce-empty">
                                <h3>Aún no tienes contactos registrados</h3>
                                <p>Agrega el primer contacto administrativo de tu comercio.</p>
                            </article>
                        @endforelse
                    </div>
                </div>
            </section>
        </div>
        <div
            class="commerce-tab-panel {{ ($activeTab ?? 'usuarios') === 'sucursales' ? 'is-active' : '' }}"
            data-commerce-tab-panel="sucursales"
        >
            <div class="commerce-admin__section-title commerce-admin__section-title--branches">
                <h2>Sucursales</h2>
                <p>Registra, valida y administra tus puntos de servicio.</p>
            </div>

           <form
                id="commerceBranchForm"
                class="commerce-branch-form"
                method="POST"
                action="{{ route('comercio.branches.store') }}"
                data-store-action="{{ route('comercio.branches.store') }}"
                data-business-name="{{ e($comercio->business_name ?? $comercio->name ?? 'Comercio Petpay') }}"
                data-business-phone="{{ e($comercio->business_phone ?? $comercio->phone ?? '') }}"
                data-business-email="{{ e($comercio->business_email ?? $comercio->email ?? '') }}"
                data-business-address="{{ e($comercio->business_address ?? '') }}"
            >
                @csrf

                <input type="hidden" name="_method" id="commerceBranchMethod" value="POST">
                <input type="hidden" name="phone_verified" id="branch_phone_verified" value="0">
                <input type="hidden" name="email_verified" id="branch_email_verified" value="0">
                <input type="hidden" name="is_open" id="branch_is_open" value="1">

                <div class="commerce-branch-layout">
                    <div class="commerce-branch-layout__form">
                        <div class="commerce-branch-toolbar">
                            <button type="button" class="commerce-ai-button" id="branchAiButton">
                                Completar con IA
                            </button>

                            <div class="commerce-branch-status" id="branchFormStatus">
                                <span class="commerce-branch-status__flag is-warning"></span>
                                <span>Revisando información</span>
                            </div>
                        </div>

                        <div class="commerce-branch-grid commerce-branch-grid--three">
                            <label class="commerce-field">
                                <span>Nombre cadena</span>
                                <input
                                    id="branch_chain_name"
                                    type="text"
                                    name="chain_name"
                                    value="{{ old('chain_name', $comercio->business_name ?? '') }}"
                                    placeholder="Pet Shop México"
                                >
                            </label>

                            <label class="commerce-field">
                                <span>Nombre de la sucursal</span>
                                <input
                                    id="branch_branch_name"
                                    type="text"
                                    name="branch_name"
                                    value="{{ old('branch_name') }}"
                                    placeholder="Sucursal Roma Norte"
                                >
                            </label>

                            <label class="commerce-field">
                                <span>Código sucursal</span>
                                <input
                                    id="branch_branch_code"
                                    type="text"
                                    name="branch_code"
                                    value="{{ old('branch_code') }}"
                                    placeholder="ROMA-001"
                                >
                            </label>
                        </div>

                        <div class="commerce-branch-grid commerce-branch-grid--coordinates">
                            <label class="commerce-field">
                                <span>Coordenadas Google</span>
                                <input
                                    id="branch_google_coordinates"
                                    type="text"
                                    name="google_coordinates"
                                    value="{{ old('google_coordinates') }}"
                                    placeholder="19.432608, -99.133209"
                                >
                            </label>

                            <button type="button" class="commerce-branch-map-button" id="branchMapPreviewButton">
                                Ver mapa
                            </button>
                        </div>

                        <fieldset class="commerce-fieldset">
                            <legend class="commerce-fieldset__legend">Dirección sucursal</legend>

                            <div class="commerce-branch-grid commerce-branch-grid--address">
                                <label class="commerce-field">
                                    <input
                                        id="branch_street"
                                        type="text"
                                        name="street"
                                        value="{{ old('street') }}"
                                        placeholder="Calle y número"
                                    >
                                </label>

                                <label class="commerce-field">
                                    <input
                                        id="branch_neighborhood"
                                        type="text"
                                        name="neighborhood"
                                        value="{{ old('neighborhood') }}"
                                        placeholder="Colonia"
                                    >
                                </label>

                                <label class="commerce-field">
                                    <input
                                        id="branch_postal_code"
                                        type="text"
                                        name="postal_code"
                                        value="{{ old('postal_code') }}"
                                        placeholder="CP"
                                        inputmode="numeric"
                                        maxlength="12"
                                    >
                                </label>

                                <label class="commerce-field">
                                    <select id="branch_state" name="state" aria-label="Estado de la sucursal">
                                        <option value="">Estado</option>
                                        @foreach ($states as $stateKey => $stateName)
                                            <option value="{{ $stateKey }}" @selected(old('state') === $stateKey)>
                                                {{ $stateName }}
                                            </option>
                                        @endforeach
                                    </select>
                                </label>
                            </div>
                        </fieldset>

                        <div class="commerce-branch-grid commerce-branch-grid--two">
                            <label class="commerce-field">
                                <span>Teléfono</span>
                                <span class="commerce-verify">
                                    <input
                                        id="branch_phone"
                                        type="tel"
                                        name="phone"
                                        value="{{ old('phone', $comercio->business_phone ?? $comercio->phone ?? '') }}"
                                        inputmode="numeric"
                                        maxlength="10"
                                    >
                                    <button type="button" class="commerce-verify__button" id="branchVerifyPhoneButton">
                                        Verifica teléfono
                                    </button>
                                </span>
                                <small class="commerce-field__hint" id="branchPhoneHint"></small>
                            </label>

                            <label class="commerce-field">
                                <span>Correo electrónico</span>
                                <span class="commerce-verify">
                                    <input
                                        id="branch_email"
                                        type="email"
                                        name="email"
                                        value="{{ old('email', $comercio->business_email ?? $comercio->email ?? '') }}"
                                    >
                                    <button type="button" class="commerce-verify__button" id="branchVerifyEmailButton">
                                        Verifica correo
                                    </button>
                                </span>
                                <small class="commerce-field__hint" id="branchEmailHint"></small>
                            </label>
                        </div>

                        <div class="commerce-branch-grid commerce-branch-grid--two">
                            <label class="commerce-field">
                                <span>Página WEB</span>
                                <input
                                    id="branch_website"
                                    type="text"
                                    name="website"
                                    value="{{ old('website') }}"
                                    placeholder="www.mi-comercio.com"
                                >
                                <small class="commerce-field__hint" id="branchWebsiteHint"></small>
                            </label>

                            <label class="commerce-field">
                                <span>Teléfono WhatsApp</span>
                                <input
                                    id="branch_whatsapp_phone"
                                    type="tel"
                                    name="whatsapp_phone"
                                    value="{{ old('whatsapp_phone') }}"
                                    inputmode="numeric"
                                    maxlength="10"
                                    placeholder="5512345678"
                                >
                                <small class="commerce-field__hint" id="branchWhatsappHint"></small>
                            </label>
                        </div>

                        <fieldset class="commerce-fieldset">
                            <legend class="commerce-fieldset__legend">Días de servicio</legend>

                            <div class="commerce-service-days">
                                @foreach ($serviceDays as $dayKey => $dayName)
                                    <label class="commerce-service-day">
                                        <input
                                            type="checkbox"
                                            name="service_days[]"
                                            value="{{ $dayKey }}"
                                            @checked(collect(old('service_days', []))->contains($dayKey))
                                        >
                                        <span>{{ $dayKey }}</span>
                                        <small>{{ $dayName }}</small>
                                    </label>
                                @endforeach
                            </div>
                        </fieldset>

                        <div class="commerce-branch-grid commerce-branch-grid--three commerce-branch-grid--service">
                            <label class="commerce-field">
                                <span>Hora apertura</span>
                                <input
                                    id="branch_service_open_time"
                                    type="time"
                                    name="service_open_time"
                                    value="{{ old('service_open_time') }}"
                                >
                            </label>

                            <label class="commerce-field">
                                <span>Hora cierre</span>
                                <input
                                    id="branch_service_close_time"
                                    type="time"
                                    name="service_close_time"
                                    value="{{ old('service_close_time') }}"
                                >
                            </label>

                            <div class="commerce-service-switch">
                                <span>Servicio</span>

                                <button type="button" class="commerce-switch is-on" id="branchServiceSwitch">
                                    <span></span>
                                </button>

                                <strong id="branchServiceText">En servicio</strong>
                            </div>
                        </div>

                        <div class="commerce-branch-actions">
                            <button type="submit" class="commerce-admin__save" id="branchSaveButton">
                                Guardar sucursal
                            </button>

                            <button type="button" class="commerce-form-actions__reset" id="branchResetButton" hidden>
                                Cancelar edición
                            </button>
                        </div>
                    </div>

                    <aside class="commerce-branch-layout__preview">
                        <div class="commerce-map-card">
                            <div class="commerce-map-card__map" id="branchMapPreview">
                                <span>Mapa</span>
                            </div>

                            <div class="commerce-map-card__body">
                                <strong id="branchPreviewName">Nueva sucursal</strong>
                                <p id="branchPreviewAddress">Ingresa dirección y coordenadas para previsualizar.</p>
                                <small id="branchPreviewCoordinates">Sin coordenadas</small>
                            </div>
                        </div>

                        <div class="commerce-branch-ai-card">
                            <strong>Validación inteligente</strong>
                            <p id="branchMissingPreview">
                                Completa los datos para activar bandera verde.
                            </p>
                        </div>
                    </aside>
                </div>
            </form>

                        <div class="commerce-branches-list">
                @php
                    $branchItems = collect($branches ?? [])->values();
                @endphp

                @if ($branchItems->isEmpty())
                    <article class="commerce-empty">
                        <h3>Aún no tienes sucursales registradas</h3>
                        <p>Agrega tu primera sucursal para comenzar a operar en Petpay.</p>
                    </article>
                @else
                    @foreach ($branchItems as $branch)
                        @php
                            $missingFields = $branch->missing_fields ?? [];
                            $serviceDaysValue = $branch->service_days ?? [];

                            if (is_string($serviceDaysValue)) {
                                $decodedServiceDays = json_decode($serviceDaysValue, true);
                                $serviceDaysValue = is_array($decodedServiceDays) ? $decodedServiceDays : [];
                            }

                            if (! is_array($serviceDaysValue)) {
                                $serviceDaysValue = [];
                            }
                        @endphp

                        <article class="commerce-branch-card {{ $branch->is_complete ? 'is-complete' : 'is-incomplete' }}">
                            <div class="commerce-branch-card__flag">
                                <span class="{{ $branch->is_complete ? 'is-green' : 'is-red' }}"></span>
                            </div>

                            <div class="commerce-branch-card__body">
                                <h3>
                                    {{ $branch->branch_name }}
                                    <small>{{ $branch->chain_name }}</small>
                                </h3>

                                <p>
                                    <strong>Código:</strong>
                                    {{ $branch->branch_code ?: 'Pendiente' }}
                                </p>

                                <p>
                                    <strong>Dirección:</strong>
                                    {{ $branch->full_address ?: 'Pendiente' }}
                                </p>

                                <p>
                                    <strong>Contacto:</strong>
                                    {{ $branch->phone ?: 'Sin teléfono' }}
                                    ·
                                    {{ $branch->email ?: 'Sin correo' }}
                                </p>

                                <p>
                                    <strong>Web:</strong>
                                    {{ $branch->website ?: 'Pendiente' }}
                                </p>

                                <p>
                                    <strong>Horario:</strong>
                                    {{ $branch->service_schedule ?: 'Pendiente' }}
                                </p>

                                <p>
                                    <strong>Servicio:</strong>
                                    <span class="{{ $branch->is_open ? 'commerce-open' : 'commerce-closed' }}">
                                        {{ $branch->is_open ? 'En servicio' : 'Apagado' }}
                                    </span>
                                </p>

                                @if (count($missingFields) > 0)
                                    <div class="commerce-branch-card__missing">
                                        <strong>Faltan datos:</strong>
                                        {{ implode(', ', $missingFields) }}
                                    </div>
                                @else
                                    <div class="commerce-branch-card__complete">
                                        Información completa. Bandera verde.
                                    </div>
                                @endif
                            </div>

                            <div class="commerce-branch-card__actions">
                                <button
                                    type="button"
                                    class="commerce-icon-button commerce-edit-branch"
                                    aria-label="Editar sucursal"
                                    data-update-action="{{ route('comercio.branches.update', $branch) }}"
                                    data-chain-name="{{ $branch->chain_name }}"
                                    data-branch-name="{{ $branch->branch_name }}"
                                    data-branch-code="{{ $branch->branch_code }}"
                                    data-google-coordinates="{{ $branch->google_coordinates }}"
                                    data-street="{{ $branch->street }}"
                                    data-neighborhood="{{ $branch->neighborhood }}"
                                    data-postal-code="{{ $branch->postal_code }}"
                                    data-state="{{ $branch->state }}"
                                    data-phone="{{ $branch->phone }}"
                                    data-email="{{ $branch->email }}"
                                    data-website="{{ $branch->website }}"
                                    data-whatsapp-phone="{{ $branch->whatsapp_phone }}"
                                    data-service-days="{{ e(json_encode($serviceDaysValue)) }}"
                                    data-service-open-time="{{ $branch->service_open_time ? $branch->service_open_time->format('H:i') : '' }}"
                                    data-service-close-time="{{ $branch->service_close_time ? $branch->service_close_time->format('H:i') : '' }}"
                                    data-phone-verified="{{ $branch->phone_verified ? '1' : '0' }}"
                                    data-email-verified="{{ $branch->email_verified ? '1' : '0' }}"
                                    data-is-open="{{ $branch->is_open ? '1' : '0' }}"
                                >
                                    <svg viewBox="0 0 24 24" fill="none" stroke-width="2">
                                        <path d="M12 20h9"></path>
                                        <path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L8 18l-4 1 1-4 11.5-11.5Z"></path>
                                    </svg>
                                </button>

                                <form method="POST" action="{{ route('comercio.branches.service', $branch) }}">
                                    @csrf
                                    @method('PATCH')

                                    <button
                                        type="submit"
                                        class="commerce-branch-service-button {{ $branch->is_open ? 'is-on' : 'is-off' }}"
                                        aria-label="{{ $branch->is_open ? 'Apagar servicio' : 'Encender servicio' }}"
                                    >
                                        {{ $branch->is_open ? 'Apagar' : 'Encender' }}
                                    </button>
                                </form>

                                <form
                                    method="POST"
                                    action="{{ route('comercio.branches.destroy', $branch) }}"
                                    onsubmit="return confirm('¿Seguro que deseas eliminar esta sucursal?');"
                                >
                                    @csrf
                                    @method('DELETE')

                                    <button type="submit" class="commerce-icon-button" aria-label="Eliminar sucursal">
                                        <svg viewBox="0 0 24 24" fill="none" stroke-width="2">
                                            <path d="M4 7h16"></path>
                                            <path d="M10 11v6"></path>
                                            <path d="M14 11v6"></path>
                                            <path d="M6 7l1 14h10l1-14"></path>
                                            <path d="M9 7V4h6v3"></path>
                                        </svg>
                                    </button>
                                </form>
                            </div>
                        </article>
                    @endforeach
                @endif
            </div>

@push('scripts')
    <script>

        (() => {
            const tabButtons = document.querySelectorAll('[data-commerce-tab-button]');
            const tabPanels = document.querySelectorAll('[data-commerce-tab-panel]');

            tabButtons.forEach((button) => {
                button.addEventListener('click', () => {
                    const tab = button.dataset.commerceTabButton;

                    tabButtons.forEach((item) => item.classList.remove('is-active'));
                    tabPanels.forEach((panel) => panel.classList.remove('is-active'));

                    button.classList.add('is-active');
                    document.querySelector(`[data-commerce-tab-panel="${tab}"]`)?.classList.add('is-active');

                    const url = new URL(window.location.href);
                    url.searchParams.set('tab', tab);
                    window.history.replaceState({}, '', url.toString());
                });
            });
        })();

                (() => {
            const form = document.getElementById('commerceContactForm');

            if (!form) {
                return;
            }

            const methodInput = document.getElementById('commerceContactMethod');
            const saveButton = document.getElementById('commerceSaveButton');
            const resetButton = document.getElementById('commerceResetButton');

            const phoneInput = document.getElementById('phone');
            const emailInput = document.getElementById('email');
            const phoneVerifiedInput = document.getElementById('phone_verified');
            const emailVerifiedInput = document.getElementById('email_verified');
            const phoneHint = document.getElementById('phoneHint');
            const emailHint = document.getElementById('emailHint');
            const locateButton = document.getElementById('locateContactButton');
            const locationHint = document.getElementById('contactLocationHint');

            const fields = {
                first_name: document.getElementById('first_name'),
                last_name_paternal: document.getElementById('last_name_paternal'),
                last_name_maternal: document.getElementById('last_name_maternal'),
                street: document.getElementById('street'),
                neighborhood: document.getElementById('neighborhood'),
                postal_code: document.getElementById('postal_code'),
                state: document.getElementById('state'),
                phone: phoneInput,
                email: emailInput,
            };

            const showHint = (element, message, valid) => {
                element.textContent = message;
                element.classList.toggle('is-valid', valid);
                element.classList.toggle('is-invalid', !valid);
            };

            const showLocationHint = (message, valid = null) => {
                if (!locationHint) {
                    return;
                }

                locationHint.textContent = message;
                locationHint.classList.toggle('is-valid', valid === true);
                locationHint.classList.toggle('is-invalid', valid === false);
            };

            const normalizePhone = () => {
                phoneInput.value = phoneInput.value.replace(/\D+/g, '').slice(0, 10);
                phoneVerifiedInput.value = '0';
                phoneHint.textContent = '';
                phoneHint.className = 'commerce-field__hint';
            };

            const normalizeEmail = () => {
                emailVerifiedInput.value = '0';
                emailHint.textContent = '';
                emailHint.className = 'commerce-field__hint';
            };

            const clearContactForm = () => {
                form.action = form.dataset.storeAction;
                methodInput.value = 'POST';
                saveButton.textContent = 'Guardar';
                resetButton.hidden = true;

                Object.values(fields).forEach((field) => {
                    field.value = '';
                });

                phoneVerifiedInput.value = '0';
                emailVerifiedInput.value = '0';
                phoneHint.textContent = '';
                emailHint.textContent = '';
                phoneHint.className = 'commerce-field__hint';
                emailHint.className = 'commerce-field__hint';
                showLocationHint('');
            };

            const normalizeText = (value) => {
                return String(value || '')
                    .normalize('NFD')
                    .replace(/[\u0300-\u036f]/g, '')
                    .toLowerCase()
                    .replace(/[^a-z0-9]+/g, '_')
                    .replace(/^_+|_+$/g, '');
            };

            const stateAliases = {
                aguascalientes: 'aguascalientes',
                baja_california: 'baja_california',
                baja_california_sur: 'baja_california_sur',
                campeche: 'campeche',
                chiapas: 'chiapas',
                chihuahua: 'chihuahua',
                ciudad_de_mexico: 'ciudad_de_mexico',
                cdmx: 'ciudad_de_mexico',
                mexico_city: 'ciudad_de_mexico',
                coahuila: 'coahuila',
                coahuila_de_zaragoza: 'coahuila',
                colima: 'colima',
                durango: 'durango',
                estado_de_mexico: 'estado_de_mexico',
                mexico_state: 'estado_de_mexico',
                guanajuato: 'guanajuato',
                guerrero: 'guerrero',
                hidalgo: 'hidalgo',
                jalisco: 'jalisco',
                michoacan: 'michoacan',
                michoacan_de_ocampo: 'michoacan',
                morelos: 'morelos',
                nayarit: 'nayarit',
                nuevo_leon: 'nuevo_leon',
                oaxaca: 'oaxaca',
                puebla: 'puebla',
                queretaro: 'queretaro',
                quintana_roo: 'quintana_roo',
                san_luis_potosi: 'san_luis_potosi',
                sinaloa: 'sinaloa',
                sonora: 'sonora',
                tabasco: 'tabasco',
                tamaulipas: 'tamaulipas',
                tlaxcala: 'tlaxcala',
                veracruz: 'veracruz',
                veracruz_de_ignacio_de_la_llave: 'veracruz',
                yucatan: 'yucatan',
                zacatecas: 'zacatecas',
            };

            const selectStateFromText = (stateText) => {
                const normalized = normalizeText(stateText);
                const stateKey = stateAliases[normalized] || normalized;

                if (!stateKey) {
                    return;
                }

                const option = Array.from(fields.state.options).find((item) => item.value === stateKey);

                if (option) {
                    fields.state.value = option.value;
                }
            };

            const fillAddressFromGeocode = (address) => {
                const streetParts = [
                    address.road,
                    address.pedestrian,
                    address.footway,
                    address.house_number,
                ].filter(Boolean);

                const neighborhood = address.neighbourhood
                    || address.suburb
                    || address.city_district
                    || address.quarter
                    || address.village
                    || '';

                fields.street.value = streetParts.join(' ').trim();
                fields.neighborhood.value = neighborhood;
                fields.postal_code.value = address.postcode || '';

                selectStateFromText(address.state || address.region || '');
            };

            const useCurrentLocation = () => {
                if (!navigator.geolocation) {
                    showLocationHint('Tu navegador no permite geolocalización.', false);
                    return;
                }

                locateButton.disabled = true;
                locateButton.classList.add('is-loading');
                showLocationHint('Obteniendo ubicación...', null);

                navigator.geolocation.getCurrentPosition(async (position) => {
                    const { latitude, longitude } = position.coords;

                    try {
                        const url = new URL('https://nominatim.openstreetmap.org/reverse');
                        url.searchParams.set('format', 'jsonv2');
                        url.searchParams.set('lat', latitude);
                        url.searchParams.set('lon', longitude);
                        url.searchParams.set('addressdetails', '1');

                        const response = await fetch(url.toString(), {
                            headers: {
                                Accept: 'application/json',
                            },
                        });

                        if (!response.ok) {
                            throw new Error('No se pudo consultar la dirección.');
                        }

                        const data = await response.json();
                        fillAddressFromGeocode(data.address || {});
                        showLocationHint('Dirección detectada. Revisa y ajusta los datos antes de guardar.', true);
                    } catch (error) {
                        fields.street.value = `${latitude.toFixed(6)}, ${longitude.toFixed(6)}`;
                        showLocationHint('No se pudo convertir la ubicación a dirección. Guardamos las coordenadas en calle para que las ajustes.', false);
                    } finally {
                        locateButton.disabled = false;
                        locateButton.classList.remove('is-loading');
                    }
                }, () => {
                    locateButton.disabled = false;
                    locateButton.classList.remove('is-loading');
                    showLocationHint('Permiso de ubicación rechazado o no disponible.', false);
                }, {
                    enableHighAccuracy: true,
                    timeout: 12000,
                    maximumAge: 0,
                });
            };

            clearContactForm();

            phoneInput.addEventListener('input', normalizePhone);
            emailInput.addEventListener('input', normalizeEmail);

            if (locateButton) {
                locateButton.addEventListener('click', useCurrentLocation);
            }

            document.getElementById('verifyPhoneButton').addEventListener('click', () => {
                normalizePhone();

                if (!phoneInput.value) {
                    showHint(phoneHint, 'Ingresa un teléfono.', false);
                    return;
                }

                if (!/^[0-9]{10}$/.test(phoneInput.value)) {
                    showHint(phoneHint, 'El teléfono debe tener 10 dígitos.', false);
                    return;
                }

                phoneVerifiedInput.value = '1';
                showHint(phoneHint, 'Teléfono validado.', true);
            });

            document.getElementById('verifyEmailButton').addEventListener('click', () => {
                const email = emailInput.value.trim();
                emailInput.value = email;

                if (!email) {
                    showHint(emailHint, 'Ingresa un correo.', false);
                    return;
                }

                if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
                    showHint(emailHint, 'Correo no válido.', false);
                    return;
                }

                emailVerifiedInput.value = '1';
                showHint(emailHint, 'Correo validado.', true);
            });

            document.querySelectorAll('.commerce-edit-contact').forEach((button) => {
                button.addEventListener('click', () => {
                    form.action = button.dataset.updateAction;
                    methodInput.value = 'PUT';

                    fields.first_name.value = button.dataset.firstName || '';
                    fields.last_name_paternal.value = button.dataset.lastNamePaternal || '';
                    fields.last_name_maternal.value = button.dataset.lastNameMaternal || '';
                    fields.street.value = button.dataset.street || '';
                    fields.neighborhood.value = button.dataset.neighborhood || '';
                    fields.postal_code.value = button.dataset.postalCode || '';
                    fields.state.value = button.dataset.state || '';
                    fields.phone.value = button.dataset.phone || '';
                    fields.email.value = button.dataset.email || '';

                    phoneVerifiedInput.value = button.dataset.phoneVerified || '0';
                    emailVerifiedInput.value = button.dataset.emailVerified || '0';

                    phoneHint.textContent = phoneVerifiedInput.value === '1' ? 'Teléfono validado.' : '';
                    emailHint.textContent = emailVerifiedInput.value === '1' ? 'Correo validado.' : '';

                    phoneHint.className = 'commerce-field__hint' + (phoneVerifiedInput.value === '1' ? ' is-valid' : '');
                    emailHint.className = 'commerce-field__hint' + (emailVerifiedInput.value === '1' ? ' is-valid' : '');

                    saveButton.textContent = 'Actualizar';
                    resetButton.hidden = false;
                    showLocationHint('');

                    form.scrollIntoView({
                        behavior: 'smooth',
                        block: 'center',
                    });
                });
            });

            resetButton.addEventListener('click', clearContactForm);
        })();

        (() => {
            const form = document.getElementById('commerceBranchForm');

            if (!form) {
                return;
            }

            const methodInput = document.getElementById('commerceBranchMethod');
            const saveButton = document.getElementById('branchSaveButton');
            const resetButton = document.getElementById('branchResetButton');

            const serviceSwitch = document.getElementById('branchServiceSwitch');
            const serviceInput = document.getElementById('branch_is_open');
            const serviceText = document.getElementById('branchServiceText');

            const phoneInput = document.getElementById('branch_phone');
            const emailInput = document.getElementById('branch_email');
            const websiteInput = document.getElementById('branch_website');
            const whatsappInput = document.getElementById('branch_whatsapp_phone');

            const phoneVerifiedInput = document.getElementById('branch_phone_verified');
            const emailVerifiedInput = document.getElementById('branch_email_verified');

            const phoneHint = document.getElementById('branchPhoneHint');
            const emailHint = document.getElementById('branchEmailHint');
            const websiteHint = document.getElementById('branchWebsiteHint');
            const whatsappHint = document.getElementById('branchWhatsappHint');

            const statusFlag = document.querySelector('#branchFormStatus .commerce-branch-status__flag');
            const statusText = document.querySelector('#branchFormStatus span:last-child');
            const missingPreview = document.getElementById('branchMissingPreview');

            const previewName = document.getElementById('branchPreviewName');
            const previewAddress = document.getElementById('branchPreviewAddress');
            const previewCoordinates = document.getElementById('branchPreviewCoordinates');
            const mapPreview = document.getElementById('branchMapPreview');

            const fields = {
                chain_name: document.getElementById('branch_chain_name'),
                branch_name: document.getElementById('branch_branch_name'),
                branch_code: document.getElementById('branch_branch_code'),
                google_coordinates: document.getElementById('branch_google_coordinates'),
                street: document.getElementById('branch_street'),
                neighborhood: document.getElementById('branch_neighborhood'),
                postal_code: document.getElementById('branch_postal_code'),
                state: document.getElementById('branch_state'),
                phone: phoneInput,
                email: emailInput,
                website: websiteInput,
                whatsapp_phone: whatsappInput,
                service_open_time: document.getElementById('branch_service_open_time'),
                service_close_time: document.getElementById('branch_service_close_time'),
            };

            const requiredLabels = {
                chain_name: 'Nombre de la cadena',
                branch_name: 'Nombre de la sucursal',
                branch_code: 'Código sucursal',
                google_coordinates: 'Coordenadas Google',
                street: 'Calle y número',
                neighborhood: 'Colonia',
                postal_code: 'CP',
                state: 'Estado',
                phone: 'Teléfono',
                email: 'Correo electrónico',
                website: 'Página WEB',
                whatsapp_phone: 'Teléfono WhatsApp',
                service_open_time: 'Hora de apertura',
                service_close_time: 'Hora de cierre',
            };

            const showHint = (element, message, valid) => {
                element.textContent = message;
                element.classList.toggle('is-valid', valid);
                element.classList.toggle('is-invalid', !valid);
            };

            const normalizePhone = (input) => {
                input.value = input.value.replace(/\D+/g, '').slice(0, 10);
            };

            const setServiceState = (isOpen) => {
                serviceInput.value = isOpen ? '1' : '0';
                serviceSwitch.classList.toggle('is-on', isOpen);
                serviceSwitch.classList.toggle('is-off', !isOpen);
                serviceText.textContent = isOpen ? 'En servicio' : 'Apagado';
            };

            const selectedDays = () => {
                return Array.from(form.querySelectorAll('input[name="service_days[]"]:checked')).map((input) => input.value);
            };

            const setSelectedDays = (days) => {
                form.querySelectorAll('input[name="service_days[]"]').forEach((input) => {
                    input.checked = days.includes(input.value);
                });
            };

            const getMissingFields = () => {
                const missing = [];

                Object.entries(requiredLabels).forEach(([key, label]) => {
                    if (!fields[key].value.trim()) {
                        missing.push(label);
                    }
                });

                if (selectedDays().length === 0) {
                    missing.push('Días de servicio');
                }

                return missing;
            };

            const updateStatus = () => {
                const missing = getMissingFields();

                statusFlag.classList.toggle('is-green', missing.length === 0);
                statusFlag.classList.toggle('is-red', missing.length > 0);
                statusFlag.classList.toggle('is-warning', false);

                statusText.textContent = missing.length === 0
                    ? 'Bandera verde: información completa'
                    : `Faltan ${missing.length} datos`;

                missingPreview.textContent = missing.length === 0
                    ? 'Todo está completo. La sucursal puede operar con bandera verde.'
                    : `Falta: ${missing.join(', ')}.`;

                previewName.textContent = fields.branch_name.value.trim() || 'Nueva sucursal';

                const addressParts = [
                    fields.street.value.trim(),
                    fields.neighborhood.value.trim(),
                    fields.postal_code.value.trim() ? `CP ${fields.postal_code.value.trim()}` : '',
                    fields.state.options[fields.state.selectedIndex]?.text || '',
                ].filter(Boolean);

                previewAddress.textContent = addressParts.length > 0
                    ? addressParts.join(', ')
                    : 'Ingresa dirección y coordenadas para previsualizar.';

                previewCoordinates.textContent = fields.google_coordinates.value.trim() || 'Sin coordenadas';
            };

            const resetBranchValidation = () => {
                phoneVerifiedInput.value = '0';
                emailVerifiedInput.value = '0';

                phoneHint.textContent = '';
                emailHint.textContent = '';
                websiteHint.textContent = '';
                whatsappHint.textContent = '';

                phoneHint.className = 'commerce-field__hint';
                emailHint.className = 'commerce-field__hint';
                websiteHint.className = 'commerce-field__hint';
                whatsappHint.className = 'commerce-field__hint';
            };

            Object.values(fields).forEach((field) => {
                field.addEventListener('input', updateStatus);
                field.addEventListener('change', updateStatus);
            });

            form.querySelectorAll('input[name="service_days[]"]').forEach((input) => {
                input.addEventListener('change', updateStatus);
            });

            phoneInput.addEventListener('input', () => {
                normalizePhone(phoneInput);
                phoneVerifiedInput.value = '0';
                phoneHint.textContent = '';
                phoneHint.className = 'commerce-field__hint';
                updateStatus();
            });

            whatsappInput.addEventListener('input', () => {
                normalizePhone(whatsappInput);
                whatsappHint.textContent = '';
                whatsappHint.className = 'commerce-field__hint';
                updateStatus();
            });

            emailInput.addEventListener('input', () => {
                emailVerifiedInput.value = '0';
                emailHint.textContent = '';
                emailHint.className = 'commerce-field__hint';
                updateStatus();
            });

            websiteInput.addEventListener('input', () => {
                websiteHint.textContent = '';
                websiteHint.className = 'commerce-field__hint';
                updateStatus();
            });

            document.getElementById('branchVerifyPhoneButton').addEventListener('click', () => {
                normalizePhone(phoneInput);

                if (!phoneInput.value) {
                    showHint(phoneHint, 'Ingresa un teléfono.', false);
                    return;
                }

                if (!/^[0-9]{10}$/.test(phoneInput.value)) {
                    showHint(phoneHint, 'El teléfono debe tener 10 dígitos.', false);
                    return;
                }

                phoneVerifiedInput.value = '1';
                showHint(phoneHint, 'Teléfono validado.', true);
            });

            document.getElementById('branchVerifyEmailButton').addEventListener('click', () => {
                const email = emailInput.value.trim();
                emailInput.value = email;

                if (!email) {
                    showHint(emailHint, 'Ingresa un correo.', false);
                    return;
                }

                if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
                    showHint(emailHint, 'Correo no válido.', false);
                    return;
                }

                emailVerifiedInput.value = '1';
                showHint(emailHint, 'Correo validado.', true);
            });

            websiteInput.addEventListener('blur', () => {
                const website = websiteInput.value.trim();

                if (!website) {
                    return;
                }

                if (!/^(https?:\/\/)?[a-z0-9.-]+\.[a-z]{2,}/i.test(website)) {
                    showHint(websiteHint, 'Página web no válida.', false);
                    return;
                }

                showHint(websiteHint, 'Página web válida.', true);
            });

            whatsappInput.addEventListener('blur', () => {
                normalizePhone(whatsappInput);

                if (!whatsappInput.value) {
                    return;
                }

                if (!/^[0-9]{10}$/.test(whatsappInput.value)) {
                    showHint(whatsappHint, 'El WhatsApp debe tener 10 dígitos.', false);
                    return;
                }

                showHint(whatsappHint, 'WhatsApp válido.', true);
            });

            serviceSwitch.addEventListener('click', () => {
                setServiceState(serviceInput.value !== '1');
            });

            document.getElementById('branchMapPreviewButton').addEventListener('click', () => {
                const coordinates = fields.google_coordinates.value.trim();

                if (!coordinates) {
                    mapPreview.classList.remove('has-coordinates');
                    mapPreview.innerHTML = '<span>Agrega coordenadas</span>';
                    return;
                }

                mapPreview.classList.add('has-coordinates');
                mapPreview.innerHTML = `<span>${coordinates}</span>`;
                updateStatus();
            });

            document.getElementById('branchAiButton').addEventListener('click', () => {
                const businessName = form.dataset.businessName || 'Comercio Petpay';
                const businessPhone = form.dataset.businessPhone || '';
                const businessEmail = form.dataset.businessEmail || '';
                const businessAddress = form.dataset.businessAddress || '';

                if (!fields.chain_name.value.trim()) {
                    fields.chain_name.value = businessName;
                }

                if (!fields.branch_name.value.trim()) {
                    fields.branch_name.value = 'Sucursal principal';
                }

                if (!fields.branch_code.value.trim()) {
                    fields.branch_code.value = 'SUC-001';
                }

                if (!fields.street.value.trim() && businessAddress) {
                    fields.street.value = businessAddress;
                }

                if (!fields.phone.value.trim() && businessPhone) {
                    fields.phone.value = businessPhone.replace(/\D+/g, '').slice(0, 10);
                }

                if (!fields.email.value.trim() && businessEmail) {
                    fields.email.value = businessEmail;
                }

                if (!fields.service_open_time.value) {
                    fields.service_open_time.value = '09:00';
                }

                if (!fields.service_close_time.value) {
                    fields.service_close_time.value = '18:00';
                }

                if (selectedDays().length === 0) {
                    setSelectedDays(['L', 'M', 'X', 'J', 'V']);
                }

                updateStatus();

                form.scrollIntoView({
                    behavior: 'smooth',
                    block: 'center',
                });
            });

            document.querySelectorAll('.commerce-edit-branch').forEach((button) => {
                button.addEventListener('click', () => {
                    form.action = button.dataset.updateAction;
                    methodInput.value = 'PUT';

                    fields.chain_name.value = button.dataset.chainName || '';
                    fields.branch_name.value = button.dataset.branchName || '';
                    fields.branch_code.value = button.dataset.branchCode || '';
                    fields.google_coordinates.value = button.dataset.googleCoordinates || '';
                    fields.street.value = button.dataset.street || '';
                    fields.neighborhood.value = button.dataset.neighborhood || '';
                    fields.postal_code.value = button.dataset.postalCode || '';
                    fields.state.value = button.dataset.state || '';
                    fields.phone.value = button.dataset.phone || '';
                    fields.email.value = button.dataset.email || '';
                    fields.website.value = button.dataset.website || '';
                    fields.whatsapp_phone.value = button.dataset.whatsappPhone || '';
                    fields.service_open_time.value = button.dataset.serviceOpenTime || '';
                    fields.service_close_time.value = button.dataset.serviceCloseTime || '';

                    try {
                        setSelectedDays(JSON.parse(button.dataset.serviceDays || '[]'));
                    } catch (error) {
                        setSelectedDays([]);
                    }

                    phoneVerifiedInput.value = button.dataset.phoneVerified || '0';
                    emailVerifiedInput.value = button.dataset.emailVerified || '0';

                    setServiceState(button.dataset.isOpen === '1');

                    phoneHint.textContent = phoneVerifiedInput.value === '1' ? 'Teléfono validado.' : '';
                    emailHint.textContent = emailVerifiedInput.value === '1' ? 'Correo validado.' : '';

                    phoneHint.className = 'commerce-field__hint' + (phoneVerifiedInput.value === '1' ? ' is-valid' : '');
                    emailHint.className = 'commerce-field__hint' + (emailVerifiedInput.value === '1' ? ' is-valid' : '');

                    saveButton.textContent = 'Actualizar sucursal';
                    resetButton.hidden = false;

                    updateStatus();

                    form.scrollIntoView({
                        behavior: 'smooth',
                        block: 'center',
                    });
                });
            });

            resetButton.addEventListener('click', () => {
                form.action = form.dataset.storeAction;
                methodInput.value = 'POST';
                saveButton.textContent = 'Guardar sucursal';
                resetButton.hidden = true;

                form.reset();
                setSelectedDays([]);
                setServiceState(true);
                resetBranchValidation();
                updateStatus();
            });

            updateStatus();
        })();

                (() => {
            const accordionBlocks = Array.from(document.querySelectorAll('.commerce-info-block'));

            accordionBlocks.forEach((block, index) => {
                const header = block.querySelector('.commerce-info-block__header');

                if (!header || block.dataset.accordionReady === '1') {
                    return;
                }

                block.dataset.accordionReady = '1';

                const body = document.createElement('div');
                const bodyInner = document.createElement('div');

                body.className = 'commerce-info-block__body';
                bodyInner.className = 'commerce-info-block__body-inner';

                const childrenToMove = Array.from(block.children).filter((child) => child !== header);

                childrenToMove.forEach((child) => {
                    bodyInner.appendChild(child);
                });

                body.appendChild(bodyInner);
                block.appendChild(body);

                const toggle = document.createElement('button');
                toggle.type = 'button';
                toggle.className = 'commerce-info-block__toggle';
                toggle.setAttribute('aria-label', 'Abrir o cerrar bloque');
                toggle.innerHTML = `
                    <svg viewBox="0 0 24 24" fill="none" stroke-width="2">
                        <path d="m6 9 6 6 6-6"></path>
                    </svg>
                `;

                header.appendChild(toggle);

                const setOpen = (open) => {
                    block.classList.toggle('is-open', open);
                    toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
                };

                setOpen(index === 0);

                header.addEventListener('click', (event) => {
                    if (event.target.closest('a, button, input, select, textarea, label')) {
                        if (!event.target.closest('.commerce-info-block__toggle')) {
                            return;
                        }
                    }

                    setOpen(!block.classList.contains('is-open'));
                });
            });
        })();

                (() => {
            const searchInput = document.getElementById('commerceUserSearch');
            const yearSelect = document.getElementById('commerceUserYear');
            const statusSelect = document.getElementById('commerceUserStatus');
            const typeSelect = document.getElementById('commerceUserType');
            const versionSelect = document.getElementById('commerceUserVersion');
            const emptyMessage = document.getElementById('commerceUserFilterEmpty');
            const downloadButton = document.getElementById('commerceUserDownloadButton');
            const contactsContainer = document.querySelector('.commerce-contacts');
            const cards = Array.from(document.querySelectorAll('[data-commerce-user-card]'));

            if (!contactsContainer || !cards.length) {
                if (downloadButton) {
                    downloadButton.disabled = true;
                }

                return;
            }

            const perPage = 5;
            let currentPage = 1;

            let pagination = document.querySelector('.commerce-contact-pagination');

            if (!pagination) {
                pagination = document.createElement('div');
                pagination.className = 'commerce-contact-pagination';
                pagination.innerHTML = `
                    <span class="commerce-contact-pagination__info" data-contact-pagination-info></span>

                    <div class="commerce-contact-pagination__actions">
                        <button type="button" class="commerce-contact-pagination__button" data-contact-page-prev>
                            Anterior
                        </button>

                        <span data-contact-page-numbers></span>

                        <button type="button" class="commerce-contact-pagination__button" data-contact-page-next>
                            Siguiente
                        </button>
                    </div>
                `;

                contactsContainer.insertAdjacentElement('afterend', pagination);
            }

            const info = pagination.querySelector('[data-contact-pagination-info]');
            const numbers = pagination.querySelector('[data-contact-page-numbers]');
            const prevButton = pagination.querySelector('[data-contact-page-prev]');
            const nextButton = pagination.querySelector('[data-contact-page-next]');

            const normalize = (value) => {
                return String(value || '')
                    .normalize('NFD')
                    .replace(/[\u0300-\u036f]/g, '')
                    .toLowerCase()
                    .trim();
            };

            const getSelectedFilters = () => {
                return {
                    search: normalize(searchInput ? searchInput.value : ''),
                    year: yearSelect ? yearSelect.value : '',
                    status: statusSelect ? statusSelect.value : '',
                    type: typeSelect ? typeSelect.value : '',
                    version: versionSelect ? versionSelect.value : '',
                };
            };

            const cardMatchesFilters = (card, filters) => {
                const cardSearch = normalize(card.dataset.search);
                const cardYear = card.dataset.year || '';
                const cardStatus = card.dataset.status || '';
                const cardType = normalize(card.dataset.type);
                const cardVersion = card.dataset.version || '';

                const matchesSearch = !filters.search || cardSearch.includes(filters.search);
                const matchesYear = !filters.year || cardYear === filters.year;
                const matchesStatus = !filters.status || cardStatus === filters.status;
                const matchesType = !filters.type || cardType.includes(normalize(filters.type));
                const matchesVersion = !filters.version || cardVersion === filters.version;

                return matchesSearch && matchesYear && matchesStatus && matchesType && matchesVersion;
            };

            const getFilteredCards = () => {
                const filters = getSelectedFilters();

                return cards.filter((card) => {
                    return cardMatchesFilters(card, filters);
                });
            };

            const renderPagination = () => {
                const filteredCards = getFilteredCards();
                const total = filteredCards.length;
                const totalPages = Math.max(1, Math.ceil(total / perPage));

                currentPage = Math.min(currentPage, totalPages);

                const start = (currentPage - 1) * perPage;
                const end = start + perPage;

                cards.forEach((card) => {
                    card.hidden = true;
                });

                filteredCards.slice(start, end).forEach((card) => {
                    card.hidden = false;
                });

                if (emptyMessage) {
                    emptyMessage.hidden = total > 0;
                }

                if (pagination) {
                    pagination.hidden = total === 0;
                }

                if (downloadButton) {
                    downloadButton.disabled = total === 0;
                }

                if (info) {
                    const firstVisible = total === 0 ? 0 : start + 1;
                    const lastVisible = Math.min(end, total);

                    info.textContent = total === 0
                        ? 'Sin contactos para mostrar'
                        : `Mostrando ${firstVisible}-${lastVisible} de ${total} contactos`;
                }

                if (prevButton) {
                    prevButton.disabled = currentPage <= 1;
                }

                if (nextButton) {
                    nextButton.disabled = currentPage >= totalPages;
                }

                if (numbers) {
                    numbers.innerHTML = '';

                    for (let page = 1; page <= totalPages; page++) {
                        const button = document.createElement('button');
                        button.type = 'button';
                        button.className = `commerce-contact-pagination__button ${page === currentPage ? 'is-active' : ''}`;
                        button.textContent = page;

                        button.addEventListener('click', () => {
                            currentPage = page;
                            renderPagination();
                        });

                        numbers.appendChild(button);
                    }
                }
            };

            const resetAndRender = () => {
                currentPage = 1;
                renderPagination();
            };

            const escapeExcelCell = (value) => {
                return String(value || '')
                    .replace(/&/g, '&amp;')
                    .replace(/</g, '&lt;')
                    .replace(/>/g, '&gt;')
                    .replace(/"/g, '&quot;');
            };

            const downloadExcelReport = () => {
                const filteredCards = getFilteredCards();

                if (!filteredCards.length) {
                    alert('No hay contactos para exportar con los filtros seleccionados.');
                    return;
                }

                const rows = filteredCards.map((card, index) => {
                    return `
                        <tr>
                            <td>${index + 1}</td>
                            <td>${escapeExcelCell(card.dataset.name)}</td>
                            <td>${escapeExcelCell(card.dataset.address)}</td>
                            <td>${escapeExcelCell(card.dataset.phone)}</td>
                            <td>${escapeExcelCell(card.dataset.email)}</td>
                            <td>${escapeExcelCell(card.dataset.primary)}</td>
                            <td>${escapeExcelCell(card.dataset.status)}</td>
                            <td>${escapeExcelCell(card.dataset.phoneVerifiedLabel)}</td>
                            <td>${escapeExcelCell(card.dataset.emailVerifiedLabel)}</td>
                            <td>${escapeExcelCell(card.dataset.year)}</td>
                            <td>${escapeExcelCell(card.dataset.updatedLabel)}</td>
                        </tr>
                    `;
                }).join('');

                const html = `
                    <html>
                        <head>
                            <meta charset="UTF-8">
                        </head>
                        <body>
                            <table border="1">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Nombre</th>
                                        <th>Dirección</th>
                                        <th>Teléfono</th>
                                        <th>Correo</th>
                                        <th>Contacto principal</th>
                                        <th>Estatus</th>
                                        <th>Teléfono verificado</th>
                                        <th>Correo verificado</th>
                                        <th>Año</th>
                                        <th>Última actualización</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    ${rows}
                                </tbody>
                            </table>
                        </body>
                    </html>
                `;

                const blob = new Blob([html], {
                    type: 'application/vnd.ms-excel;charset=utf-8;',
                });

                const url = URL.createObjectURL(blob);
                const link = document.createElement('a');
                const date = new Date().toISOString().slice(0, 10);

                link.href = url;
                link.download = `reporte-contactos-comercio-${date}.xls`;
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
                URL.revokeObjectURL(url);
            };

            [searchInput, yearSelect, statusSelect, typeSelect, versionSelect].forEach((element) => {
                if (!element) {
                    return;
                }

                element.addEventListener('input', resetAndRender);
                element.addEventListener('change', resetAndRender);
            });

            if (prevButton) {
                prevButton.addEventListener('click', () => {
                    currentPage = Math.max(1, currentPage - 1);
                    renderPagination();
                });
            }

            if (nextButton) {
                nextButton.addEventListener('click', () => {
                    currentPage += 1;
                    renderPagination();
                });
            }

            if (downloadButton) {
                downloadButton.addEventListener('click', downloadExcelReport);
            }

            renderPagination();
        })();
    </script>
@endpush