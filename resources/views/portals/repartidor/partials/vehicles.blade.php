<section class="driver-vehicles" data-driver-vehicles>
    <header class="driver-vehicles__intro">
        <div>
            <h2>Control de vehículos</h2>
            <p>Alta, baja o modificación de vehículos.</p>
        </div>
    </header>

    <div class="driver-vehicles__title">
        <h2>Vehículos del repartidor</h2>

        <button
            type="button"
            class="driver-vehicle-btn driver-vehicle-btn--dark"
            data-driver-new-vehicle
        >
            <span>＋</span>
            Añadir nuevo vehículo
        </button>
    </div>

    <section
        class="driver-vehicle-module driver-vehicle-module--new"
        data-driver-new-vehicle-form
        hidden
    >
        <header class="driver-vehicle-module__header">
            <div>
                <span class="driver-vehicle-module__icon">?</span>

                <div>
                    <strong>Nuevo vehículo</strong>
                    <small>Completa la información del vehículo.</small>
                </div>
            </div>

            <button
                type="button"
                class="driver-vehicle-icon-btn"
                data-driver-cancel-new-vehicle
                title="Cerrar"
            >
                ×
            </button>
        </header>

        @include(
            'portals.repartidor.partials.vehicle-form',
            ['vehicle' => null]
        )
    </section>

    <div class="driver-vehicles__list">
        @forelse ($vehicles as $vehicle)
            @php
                $typeLabel = match ($vehicle->vehicle_type) {
                    'motorcycle' => 'Motocicleta',
                    'car' => 'Automóvil',
                    'bicycle' => 'Bicicleta',
                    'walking' => 'A pie',
                    'van' => 'Camioneta',
                    default => 'Otro',
                };
            @endphp

            <details class="driver-vehicle-module">
                <summary class="driver-vehicle-module__header">
                    <div>
                        <span class="driver-vehicle-module__icon">?</span>

                        <div>
                            <strong>
                                {{ $typeLabel }}
                                · {{ $vehicle->make ?: 'Sin marca' }}
                                · {{ $vehicle->plates ?: 'Sin placas' }}
                                · {{ $vehicle->vehicle_code }}
                            </strong>

                            <small>
                                {{ $vehicle->isLocked()
                                    ? 'Información protegida; solicita actualización para cambiarla.'
                                    : 'Completa o actualiza la información del vehículo.' }}
                            </small>
                        </div>
                    </div>

                    <div class="driver-vehicle-module__actions">
                        @if ($vehicle->is_primary)
                            <b>Principal</b>
                        @endif

                        <span class="driver-vehicle-status is-{{ $vehicle->status }}">
                            {{ str_replace('_', ' ', $vehicle->status) }}
                        </span>

                        <span class="driver-vehicle-chevron">⌄</span>
                    </div>
                </summary>

                @include(
                    'portals.repartidor.partials.vehicle-form',
                    ['vehicle' => $vehicle]
                )

            </details>
        @empty
            <div class="driver-vehicles__empty">
                No hay vehículos registrados.
                Usa “Añadir nuevo vehículo” para comenzar.
            </div>
        @endforelse
    </div>

    @php
        $captureVehicle = request()->filled('capture3d')
            ? $vehicles->firstWhere(
                'id',
                (int) request('capture3d')
            )
            : null;
    @endphp

    @if ($captureVehicle)
        <div
            class="driver-3d-capture-modal"
            data-driver-3d-capture-modal
        >
            <a
                href="{{ route('repartidor.dashboard', [
                    'tab' => 'vehiculo',
                ]) }}"
                class="driver-3d-capture-modal__backdrop"
                aria-label="Cerrar captura 3D"
            ></a>

            <section class="driver-3d-capture-modal__dialog">
                <header class="driver-3d-capture-modal__header">
                    <div>
                        <span>Captura avanzada</span>

                        <strong>
                            {{ $captureVehicle->vehicle_code }}
                            ·
                            {{ $captureVehicle->make ?: 'Sin marca' }}
                        </strong>
                    </div>

                    <a
                        href="{{ route('repartidor.dashboard', [
                            'tab' => 'vehiculo',
                        ]) }}"
                        aria-label="Cerrar"
                    >
                        ×
                    </a>
                </header>

                <div class="driver-3d-capture-modal__content">
                    @include(
                        'portals.repartidor.partials.vehicle-3d-capture',
                        ['vehicle' => $captureVehicle]
                    )
                </div>
            </section>
        </div>
    @endif
</section>



