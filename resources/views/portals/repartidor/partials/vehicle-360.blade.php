@php
    $exteriorPositions = [
        'front',
        'front_left',
        'left',
        'rear',
        'right',
        'front_right',
    ];

    $viewerPhotos = $vehicle?->photos
        ?->whereIn('position', $exteriorPositions)
        ->sortBy('sequence')
        ->values() ?? collect();

    $viewerFrames = $viewerPhotos
        ->map(fn ($photo) => route(
            'repartidor.vehicles.photos.show',
            [
                'vehicle' => $vehicle,
                'photo' => $photo,
            ]
        ))
        ->values();

    $modelJob = $vehicle?->latestReconstructionJob;

    $modelStatusLabel = match ($modelJob?->status) {
        'awaiting_capture' => 'Captura pendiente',
        'capture_ready' => 'Captura completa',
        'queued' => 'En cola',
        'processing' => 'Construyendo modelo',
        'optimizing' => 'Optimizando',
        'ready' => 'Modelo 3D disponible',
        'requires_recapture' => 'Requiere nuevas tomas',
        'failed' => 'Error de procesamiento',
        'rejected' => 'Rechazado',
        default => 'Vista 360° básica',
    };

    $captureCanContinue = $modelJob
        && in_array($modelJob->status, [
            'awaiting_capture',
            'capture_ready',
            'requires_recapture',
            'failed',
        ], true);
@endphp

<section class="driver-vehicle-visual">
    @if ($vehicle && $viewerFrames->count() === 6)
        <section
            class="driver-vehicle-360-viewer"
            data-driver-360-viewer
            data-frames='@json($viewerFrames)'
        >
            <header>
                <div>
                    <strong>Fotografías y vista 360° del vehículo</strong>

                    <small>
                        Arrastra la imagen o utiliza las flechas.
                    </small>
                </div>

                <span>6/6 tomas básicas</span>
            </header>

            <div
                class="driver-vehicle-360-stage"
                data-driver-360-stage
            >
                <img
                    src="{{ $viewerFrames->first() }}"
                    alt="Vista 360 de {{ $vehicle->vehicle_code }}"
                    data-driver-360-image
                    draggable="false"
                >

                <div class="driver-vehicle-360-badge">
                    360°
                </div>
            </div>

            <footer>
                <button
                    type="button"
                    data-driver-360-previous
                    aria-label="Vista anterior"
                    title="Vista anterior"
                >
                    ←
                </button>

                <span data-driver-360-counter>
                    1 de 6
                </span>

                <button
                    type="button"
                    data-driver-360-next
                    aria-label="Vista siguiente"
                    title="Vista siguiente"
                >
                    →
                </button>
            </footer>
        </section>

        <section class="driver-vehicle-model-upgrade">
            <div class="driver-vehicle-model-upgrade__info">
                <span class="driver-vehicle-model-upgrade__icon">
                    3D
                </span>

                <div>
                    <strong>{{ $modelStatusLabel }}</strong>

                    <p>
                        Las seis fotografías ya permiten identificar el
                        vehículo. Puedes agregar tomas guiadas para construir
                        un modelo tridimensional más detallado.
                    </p>

                    @if ($modelJob)
                        <div class="driver-vehicle-model-upgrade__progress">
                            <span>
                                <i style="width: {{ $modelJob->capturePercentage() }}%"></i>
                            </span>

                            <small>
                                {{ $modelJob->captured_frames }}
                                de
                                {{ $modelJob->required_frames }}
                                tomas avanzadas
                            </small>
                        </div>
                    @endif
                </div>
            </div>

            <div class="driver-vehicle-model-upgrade__actions">
                @if (! $modelJob)
                    <button
                        type="button"
                        class="driver-vehicle-btn driver-vehicle-btn--dark"
                        data-driver-start-3d
                        data-start-url="{{ route(
                            'repartidor.vehicles.3d.store',
                            $vehicle
                        ) }}"
                    >
                        Mejorar a modelo 3D
                    </button>
                @elseif ($captureCanContinue)
                    <a
                        href="{{ route('repartidor.dashboard', [
                            'tab' => 'vehiculo',
                            'capture3d' => $vehicle->id,
                        ]) }}"
                        class="driver-vehicle-btn driver-vehicle-btn--dark"
                    >
                        Continuar captura
                    </a>
                @elseif ($modelJob->status === 'ready')
                    <button
                        type="button"
                        class="driver-vehicle-btn driver-vehicle-btn--outline"
                        disabled
                    >
                        Modelo 3D disponible
                    </button>
                @else
                    <span class="driver-vehicle-model-upgrade__processing">
                        {{ $modelStatusLabel }}
                    </span>
                @endif
            </div>
        </section>
    @else
        <div class="driver-vehicle-360-note">
            <span>360°</span>

            <div>
                <strong>Fotografías del vehículo pendientes</strong>

                <p>
                    @if (! $vehicle)
                        Primero guarda el vehículo y sus fotografías.
                    @else
                        Se necesitan las seis tomas exteriores para generar
                        la vista básica. Actualmente hay
                        {{ $viewerFrames->count() }} de 6.
                    @endif
                </p>
            </div>
        </div>
    @endif
</section>
