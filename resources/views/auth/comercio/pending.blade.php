@extends('layouts.guest')

@section('title', 'Registro en revisión | Petpay')

@push('styles')
    <link
        rel="stylesheet"
        href="{{ asset('css/commerce-pending.css') }}?v={{ filemtime(public_path('css/commerce-pending.css')) }}"
    >
@endpush

@section('content')
    <main class="commerce-pending">
        <div class="commerce-pending__overlay"></div>

        <section class="commerce-pending__card">
            <a
                href="{{ url('/') }}"
                class="commerce-pending__logo"
                aria-label="Petpay"
            >
                <span class="commerce-pending__logo-symbol">P</span>
                <span>Petpay</span>
            </a>

            <div class="commerce-pending__status-icon">
                <span>✓</span>
            </div>

            <p class="commerce-pending__eyebrow">
                Registro recibido
            </p>

            <h1>Tu comercio está en revisión</h1>

            <p class="commerce-pending__description">
                Ya recibimos la información de
                <strong>
                    {{ $commerce->business_name ?: $commerce->name }}
                </strong>.
                El equipo de Petpay revisará los datos antes de activar
                el acceso completo a la plataforma.
            </p>

            @if (session('status'))
                <div class="commerce-pending__success">
                    {{ session('status') }}
                </div>
            @endif

            <dl class="commerce-pending__summary">
                <div>
                    <dt>Comercio</dt>
                    <dd>
                        {{ $commerce->business_name ?: 'Sin nombre' }}
                    </dd>
                </div>

                <div>
                    <dt>Correo</dt>
                    <dd>{{ $commerce->email }}</dd>
                </div>

                <div>
                    <dt>Estado</dt>
                    <dd>
                        @if ($commerce->isRejected())
                            Rechazado
                        @elseif ($commerce->isSuspended())
                            Suspendido
                        @else
                            Pendiente de aprobación
                        @endif
                    </dd>
                </div>

                <div>
                    <dt>Tipo de acceso</dt>
                    <dd>
                        {{ $commerce->auth_provider === 'google'
                            ? 'Google'
                            : 'Correo y contraseña' }}
                    </dd>
                </div>
            </dl>

            @if ($commerce->isRejected())
                <div class="commerce-pending__warning">
                    <strong>La solicitud fue rechazada.</strong>

                    @if ($commerce->rejection_reason)
                        <span>
                            {{ $commerce->rejection_reason }}
                        </span>
                    @else
                        <span>
                            Contacta a soporte para conocer los detalles.
                        </span>
                    @endif
                </div>
            @elseif ($commerce->isSuspended())
                <div class="commerce-pending__warning">
                    <strong>La cuenta está suspendida.</strong>
                    <span>
                        Contacta a soporte para revisar el estado.
                    </span>
                </div>
            @else
                <div class="commerce-pending__steps">
                    <div>
                        <span>1</span>
                        <p>
                            <strong>Registro completado</strong>
                            Tus datos quedaron guardados.
                        </p>
                    </div>

                    <div>
                        <span>2</span>
                        <p>
                            <strong>Revisión de Petpay</strong>
                            Validaremos la información del comercio.
                        </p>
                    </div>

                    <div>
                        <span>3</span>
                        <p>
                            <strong>Activación</strong>
                            Cuando sea aprobado podrás entrar al dashboard.
                        </p>
                    </div>
                </div>
            @endif

            <p class="commerce-pending__note">
                Puedes cerrar esta ventana. Tu registro permanecerá guardado.
            </p>

            <form
                method="POST"
                action="{{ route('comercio.logout') }}"
            >
                @csrf

                <button
                    type="submit"
                    class="commerce-pending__logout"
                >
                    Cerrar sesión
                </button>
            </form>
        </section>
    </main>
@endsection

