@php
    $captureJob = $vehicle->latestReconstructionJob;
    $captureFrames = $captureJob
        ? $captureJob->frames->keyBy('sequence')
        : collect();

    $captureLocked = $captureJob
        && in_array($captureJob->status, [
            'queued',
            'processing',
            'optimizing',
            'ready',
        ], true);

    $captureStatus = match ($captureJob?->status) {
        'awaiting_capture' => 'Captura pendiente',
        'capture_ready' => 'Captura completa',
        'queued' => 'En cola',
        'processing' => 'Construyendo modelo',
        'optimizing' => 'Optimizando modelo',
        'ready' => 'Modelo disponible',
        'requires_recapture' => 'Requiere nuevas tomas',
        'failed' => 'Error de procesamiento',
        'rejected' => 'Rechazado',
        default => 'Sin iniciar',
    };
@endphp

<details
    class="driver-vehicle-3d"
    data-driver-vehicle-3d
    @if ((int) request('capture3d') === (int) $vehicle->id) open @endif
>
    <summary class="driver-vehicle-3d__summary">
        <div class="driver-vehicle-3d__summary-main">
            <span class="driver-vehicle-3d__icon">◫</span>

            <div>
                <strong>Modelo 3D del vehículo</strong>

                <small>
                    Captura guiada para construir la vista del vehículo
                    y su representación en el mapa.
                </small>
            </div>
        </div>

        <div class="driver-vehicle-3d__summary-status">
            @if ($captureJob)
                <b>
                    {{ $captureJob->captured_frames }}
                    /
                    {{ $captureJob->required_frames }}
                </b>
            @endif

            <span class="is-{{ $captureJob?->status ?: 'new' }}">
                {{ $captureStatus }}
            </span>

            <i>⌄</i>
        </div>
    </summary>

    <div class="driver-vehicle-3d__body">
        @if (! $captureJob)
            <div class="driver-vehicle-3d__welcome">
                <div>
                    <span>360°</span>

                    <div>
                        <strong>Construye el vehículo digital</strong>

                        <p>
                            Necesitaremos 24 fotografías alrededor del
                            vehículo y 6 fotografías desde un ángulo elevado.
                        </p>
                    </div>
                </div>

                <ul>
                    <li>Usa un lugar iluminado y con espacio suficiente.</li>
                    <li>Mantén visible el vehículo completo.</li>
                    <li>Evita personas y objetos frente al vehículo.</li>
                    <li>No cambies de distancia durante la vuelta.</li>
                </ul>

                <form
                    method="POST"
                    action="{{ route(
                        'repartidor.vehicles.3d.store',
                        $vehicle
                    ) }}"
                >
                    @csrf

                    <button
                        type="submit"
                        class="driver-vehicle-btn driver-vehicle-btn--dark"
                    >
                        Iniciar captura 3D
                    </button>
                </form>
            </div>
        @else
            <section class="driver-vehicle-3d__progress">
                <div
                    class="driver-vehicle-3d__progress-ring"
                    style="--capture-progress: {{ $captureJob->capturePercentage() * 3.6 }}deg"
                >
                    <span>
                        <strong>{{ $captureJob->capturePercentage() }}%</strong>
                        <small>completado</small>
                    </span>
                </div>

                <div>
                    <strong>
                        {{ $captureJob->captured_frames }}
                        de
                        {{ $captureJob->required_frames }}
                        tomas guardadas
                    </strong>

                    <p>
                        Cada fotografía se guarda inmediatamente.
                        Puedes salir y continuar después sin perder el avance.
                    </p>

                    @if ($captureJob->status === 'queued')
                        <span class="driver-vehicle-3d__notice">
                            El modelo está en cola para procesamiento.
                        </span>
                    @elseif ($captureJob->status === 'processing')
                        <span class="driver-vehicle-3d__notice">
                            Estamos construyendo la geometría del vehículo.
                        </span>
                    @elseif ($captureJob->status === 'optimizing')
                        <span class="driver-vehicle-3d__notice">
                            Estamos preparando el modelo para web y mapa.
                        </span>
                    @elseif ($captureJob->status === 'ready')
                        <span class="driver-vehicle-3d__notice is-ready">
                            El modelo 3D está disponible.
                        </span>
                    @endif
                </div>
            </section>

            @if (! $captureLocked)
                <div class="driver-vehicle-3d__instructions">
                    <span>Consejo de captura</span>

                    <p>
                        Coloca el vehículo al centro. Muévete tú alrededor
                        de él; no muevas el vehículo entre fotografías.
                    </p>
                </div>
            @endif

            <section class="driver-vehicle-3d__capture-group">
                <header>
                    <div>
                        <span>1</span>

                        <div>
                            <strong>Vuelta horizontal</strong>
                            <small>
                                24 fotografías, una cada 15 grados.
                            </small>
                        </div>
                    </div>

                    <b>
                        {{ $captureFrames->keys()->filter(
                            fn ($sequence) => $sequence <= 24
                        )->count() }}/24
                    </b>
                </header>

                <div class="driver-vehicle-3d__grid">
                    @for ($sequence = 1; $sequence <= 24; $sequence++)
                        @php
                            $frame = $captureFrames->get($sequence);
                            $angle = ($sequence - 1) * 15;
                        @endphp

                        <article
                            class="driver-vehicle-3d__slot {{ $frame ? 'is-complete' : '' }}"
                        >
                            <header>
                                <span>Toma {{ str_pad($sequence, 2, '0', STR_PAD_LEFT) }}</span>
                                <b>{{ $angle }}°</b>
                            </header>

                            @if ($frame)
                                <a
                                    href="{{ route(
                                        'repartidor.vehicles.3d.frames.show',
                                        [$vehicle, $captureJob, $frame]
                                    ) }}"
                                    target="_blank"
                                    rel="noopener"
                                    class="driver-vehicle-3d__preview"
                                >
                                    <img
                                        src="{{ route(
                                            'repartidor.vehicles.3d.frames.show',
                                            [
                                                $vehicle,
                                                $captureJob,
                                                $frame,
                                                'thumbnail' => 1,
                                            ]
                                        ) }}"
                                        alt="Toma {{ $sequence }} del vehículo"
                                        loading="lazy"
                                    >

                                    <span>Ver fotografía</span>
                                </a>

                                @if (! $captureLocked)
                                    <form
                                        method="POST"
                                        action="{{ route(
                                            'repartidor.vehicles.3d.frames.destroy',
                                            [$vehicle, $captureJob, $frame]
                                        ) }}"
                                    >
                                        @csrf
                                        @method('DELETE')

                                        <button
                                            type="submit"
                                            class="driver-vehicle-3d__remove"
                                        >
                                            Repetir toma
                                        </button>
                                    </form>
                                @endif
                            @elseif (! $captureLocked)
                                <form
                                    method="POST"
                                    enctype="multipart/form-data"
                                    action="{{ route(
                                        'repartidor.vehicles.3d.frames.upload',
                                        [$vehicle, $captureJob]
                                    ) }}"
                                    class="driver-vehicle-3d__upload"
                                >
                                    @csrf

                                    <input
                                        type="hidden"
                                        name="sequence"
                                        value="{{ $sequence }}"
                                    >

                                    <label>
                                        <span>📷</span>
                                        <strong>Capturar</strong>
                                        <small>{{ $angle }}° horizontal</small>

                                        <input
                                            type="file"
                                            name="frame"
                                            accept="image/jpeg,image/png,image/webp"
                                            capture="environment"
                                            required
                                        >
                                    </label>

                                    <button type="submit">
                                        Guardar toma
                                    </button>
                                </form>
                            @else
                                <div class="driver-vehicle-3d__missing">
                                    Toma no disponible
                                </div>
                            @endif
                        </article>
                    @endfor
                </div>
            </section>

            <section class="driver-vehicle-3d__capture-group">
                <header>
                    <div>
                        <span>2</span>

                        <div>
                            <strong>Ángulo elevado</strong>
                            <small>
                                6 fotografías desde arriba, una cada 60 grados.
                            </small>
                        </div>
                    </div>

                    <b>
                        {{ $captureFrames->keys()->filter(
                            fn ($sequence) => $sequence >= 25
                        )->count() }}/6
                    </b>
                </header>

                <div class="driver-vehicle-3d__grid driver-vehicle-3d__grid--high">
                    @for ($sequence = 25; $sequence <= 30; $sequence++)
                        @php
                            $frame = $captureFrames->get($sequence);
                            $angle = ($sequence - 25) * 60;
                        @endphp

                        <article
                            class="driver-vehicle-3d__slot {{ $frame ? 'is-complete' : '' }}"
                        >
                            <header>
                                <span>Toma {{ $sequence }}</span>
                                <b>{{ $angle }}°</b>
                            </header>

                            @if ($frame)
                                <a
                                    href="{{ route(
                                        'repartidor.vehicles.3d.frames.show',
                                        [$vehicle, $captureJob, $frame]
                                    ) }}"
                                    target="_blank"
                                    rel="noopener"
                                    class="driver-vehicle-3d__preview"
                                >
                                    <img
                                        src="{{ route(
                                            'repartidor.vehicles.3d.frames.show',
                                            [
                                                $vehicle,
                                                $captureJob,
                                                $frame,
                                                'thumbnail' => 1,
                                            ]
                                        ) }}"
                                        alt="Toma elevada {{ $sequence }}"
                                        loading="lazy"
                                    >

                                    <span>Ver fotografía</span>
                                </a>

                                @if (! $captureLocked)
                                    <form
                                        method="POST"
                                        action="{{ route(
                                            'repartidor.vehicles.3d.frames.destroy',
                                            [$vehicle, $captureJob, $frame]
                                        ) }}"
                                    >
                                        @csrf
                                        @method('DELETE')

                                        <button
                                            type="submit"
                                            class="driver-vehicle-3d__remove"
                                        >
                                            Repetir toma
                                        </button>
                                    </form>
                                @endif
                            @elseif (! $captureLocked)
                                <form
                                    method="POST"
                                    enctype="multipart/form-data"
                                    action="{{ route(
                                        'repartidor.vehicles.3d.frames.upload',
                                        [$vehicle, $captureJob]
                                    ) }}"
                                    class="driver-vehicle-3d__upload"
                                >
                                    @csrf

                                    <input
                                        type="hidden"
                                        name="sequence"
                                        value="{{ $sequence }}"
                                    >

                                    <label>
                                        <span>📷</span>
                                        <strong>Capturar</strong>
                                        <small>{{ $angle }}° elevado</small>

                                        <input
                                            type="file"
                                            name="frame"
                                            accept="image/jpeg,image/png,image/webp"
                                            capture="environment"
                                            required
                                        >
                                    </label>

                                    <button type="submit">
                                        Guardar toma
                                    </button>
                                </form>
                            @endif
                        </article>
                    @endfor
                </div>
            </section>

            @if (
                ! $captureLocked
                && $captureJob->captured_frames >= $captureJob->required_frames
            )
                <form
                    method="POST"
                    action="{{ route(
                        'repartidor.vehicles.3d.submit',
                        [$vehicle, $captureJob]
                    ) }}"
                    class="driver-vehicle-3d__submit"
                >
                    @csrf

                    <div>
                        <strong>Captura completa</strong>
                        <span>
                            Revisa las fotografías antes de enviarlas.
                        </span>
                    </div>

                    <button
                        type="submit"
                        class="driver-vehicle-btn driver-vehicle-btn--dark"
                    >
                        Enviar a construcción 3D
                    </button>
                </form>
            @endif
        @endif
    </div>
</details>
