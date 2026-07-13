@php
    $isNewVehicle = $vehicle === null;
    $vehicleLocked = ! $isNewVehicle && $vehicle->isLocked();
@endphp

<form
    method="POST"
    action="{{ $isNewVehicle
        ? route('repartidor.vehicles.store')
        : route('repartidor.vehicles.update', $vehicle) }}"
    enctype="multipart/form-data"
    class="driver-vehicle-form"
>
    @csrf

    @unless ($isNewVehicle)
        @method('PUT')
    @endunless

    <details class="driver-vehicle-section">
        <summary>
            <span>◉</span>
            <strong>Vehículo</strong>
            <b>⌄</b>
        </summary>

        <div class="driver-vehicle-section__body">
            <div class="driver-vehicle-grid driver-vehicle-grid--2">
                <label>
                    <span>Tipo de vehículo <b>*</b></span>

                    <select
                        name="vehicle_type"
                        @disabled($vehicleLocked)
                        required
                    >
                        <option value="">Seleccionar</option>
                        <option value="motorcycle" @selected($vehicle?->vehicle_type === 'motorcycle')>
                            Motocicleta
                        </option>
                        <option value="car" @selected($vehicle?->vehicle_type === 'car')>
                            Automóvil
                        </option>
                        <option value="bicycle" @selected($vehicle?->vehicle_type === 'bicycle')>
                            Bicicleta
                        </option>
                        <option value="walking" @selected($vehicle?->vehicle_type === 'walking')>
                            A pie
                        </option>
                        <option value="van" @selected($vehicle?->vehicle_type === 'van')>
                            Camioneta
                        </option>
                        <option value="other" @selected($vehicle?->vehicle_type === 'other')>
                            Otro
                        </option>
                    </select>
                </label>

                <label>
                    <span>Alias</span>
                    <input
                        type="text"
                        name="alias"
                        value="{{ $vehicle?->alias }}"
                        placeholder="Ej. Moto roja"
                        @readonly($vehicleLocked)
                    >
                </label>

                                <label>
                    <span>Marca</span>

                    <select
                        data-driver-make-select
                        @disabled($vehicleLocked)
                    >
                        <option value="">Seleccionar marca</option>

                        @foreach ($vehicleCatalog as $type => $catalogMakes)
                            @foreach ($catalogMakes as $catalogMake)
                                <option
                                    value="{{ $catalogMake->name }}"
                                    data-vehicle-type="{{ $type }}"
                                    @selected(
                                        mb_strtolower((string) $vehicle?->make) ===
                                        mb_strtolower($catalogMake->name)
                                    )
                                >
                                    {{ $catalogMake->name }}
                                </option>
                            @endforeach
                        @endforeach

                        <option value="__other__">
                            Otra marca
                        </option>
                    </select>

                    <input
                        type="text"
                        name="make"
                        value="{{ old('make', $vehicle?->make) }}"
                        placeholder="Escribe la marca"
                        data-driver-make-value
                        @readonly($vehicleLocked)
                    >
                </label>

                                <label>
                    <span>Modelo</span>

                    <select
                        data-driver-model-select
                        @disabled($vehicleLocked)
                    >
                        <option value="">Seleccionar modelo</option>

                        @foreach ($vehicleCatalog as $type => $catalogMakes)
                            @foreach ($catalogMakes as $catalogMake)
                                @foreach ($catalogMake->models as $catalogModel)
                                    <option
                                        value="{{ $catalogModel->name }}"
                                        data-vehicle-type="{{ $type }}"
                                        data-vehicle-make="{{ mb_strtolower($catalogMake->name) }}"
                                        @selected(
                                            mb_strtolower((string) $vehicle?->model) ===
                                            mb_strtolower($catalogModel->name)
                                        )
                                    >
                                        {{ $catalogModel->name }}
                                    </option>
                                @endforeach
                            @endforeach
                        @endforeach

                        <option value="__other__">
                            Otro modelo
                        </option>
                    </select>

                    <input
                        type="text"
                        name="model"
                        value="{{ old('model', $vehicle?->model) }}"
                        placeholder="Escribe el modelo"
                        data-driver-model-value
                        @readonly($vehicleLocked)
                    >
                </label>

                <label>
                    <span>Año</span>
                    <input
                        type="number"
                        name="year"
                        min="1950"
                        max="{{ date('Y') + 1 }}"
                        value="{{ $vehicle?->year }}"
                        @readonly($vehicleLocked)
                    >
                </label>

                                @php
                    $currentColorHex = old(
                        'color_scale',
                        $vehicle?->color_scale ?: '#ffffff'
                    );

                    if (! preg_match(
                        '/^#[0-9a-fA-F]{6}$/',
                        (string) $currentColorHex
                    )) {
                        $currentColorHex = '#ffffff';
                    }
                @endphp

                <div
                    class="driver-vehicle-grid driver-vehicle-grid--color"
                    data-driver-color-group
                >
                    <label>
                        <span>Color</span>

                        <input
                            type="text"
                            name="color"
                            value="{{ old('color', $vehicle?->color) }}"
                            placeholder="Ej. Rojo"
                            autocomplete="off"
                            data-driver-color-name
                            @readonly($vehicleLocked)
                        >
                    </label>

                    <label class="driver-vehicle-color-picker">
                        <span>Escala cromática</span>

                        <span class="driver-vehicle-color-control">
                            <input
                                type="color"
                                name="color_scale"
                                value="{{ $currentColorHex }}"
                                data-driver-color-picker
                                @disabled($vehicleLocked)
                            >

                            <output data-driver-color-output>
                                {{ strtoupper($currentColorHex) }}
                            </output>
                        </span>
                    </label>

                    @if ($vehicleLocked)
                        <input
                            type="hidden"
                            name="color_scale"
                            value="{{ $currentColorHex }}"
                        >
                    @endif
                </div>

                <label>
                    <span>Placas</span>
                    <input
                        type="text"
                        name="plates"
                        value="{{ $vehicle?->plates }}"
                        placeholder="Ej. 123ABC"
                        @readonly($vehicleLocked)
                    >
                </label>

                <label>
                    <span>Vehículo principal</span>

                    <select
                        name="is_primary"
                        @disabled($vehicleLocked)
                    >
                        <option value="0" @selected(! $vehicle?->is_primary)>
                            No
                        </option>
                        <option value="1" @selected($vehicle?->is_primary)>
                            Sí
                        </option>
                    </select>
                </label>
            </div>
        </div>
    </details>
    <details class="driver-vehicle-section driver-vehicle-photo-section">
        <summary>
            <span>▧</span>
            <strong>Fotografías y vista 360°</strong>
            <b>⌄</b>
        </summary>

        <div class="driver-vehicle-section__body">
            <div class="driver-vehicle-photo-help">
                <strong>Captura guiada del vehículo</strong>
                <p>
                    Fotografía el vehículo completo, con buena iluminación
                    y sin personas bloqueando la vista.
                </p>
                <span>
                    Las imágenes se guardarán automáticamente como PNG.
                </span>
            </div>

            @php
                $vehiclePhotoPositions = [
                    'front' => [
                        'Frontal',
                        'Vista directa de la parte frontal.',
                    ],
                    'front_left' => [
                        'Frontal izquierda',
                        'Esquina frontal del lado izquierdo.',
                    ],
                    'left' => [
                        'Lateral izquierda',
                        'Costado izquierdo completo.',
                    ],
                    'rear' => [
                        'Trasera',
                        'Vista directa de la parte trasera.',
                    ],
                    'right' => [
                        'Lateral derecha',
                        'Costado derecho completo.',
                    ],
                    'front_right' => [
                        'Frontal derecha',
                        'Esquina frontal del lado derecho.',
                    ],
                    'plate' => [
                        'Placas',
                        'Acercamiento claro y completamente legible.',
                    ],
                    'dashboard' => [
                        'Tablero u odómetro',
                        'Tablero y kilometraje, cuando aplique.',
                    ],
                ];
            @endphp

            <div class="driver-vehicle-photo-grid">
                @foreach ($vehiclePhotoPositions as $position => [$label, $instruction])
                    @php
                        $savedPhoto = $vehicle?->photos
                            ?->firstWhere('position', $position);
                    @endphp

                    <article
                        class="driver-vehicle-photo-card {{ $savedPhoto ? 'has-photo' : '' }}"
                        data-driver-photo-card
                        data-photo-position="{{ $position }}"
                    >
                        <header>
                            <span>{{ $loop->iteration }}</span>

                            <div>
                                <strong>{{ $label }}</strong>
                                <small>{{ $instruction }}</small>
                            </div>

                            @if ($savedPhoto)
                                <b>
                                    {{ $savedPhoto->status === 'approved'
                                        ? 'Aprobada'
                                        : 'En revisión' }}
                                </b>
                            @endif
                        </header>

                        <div
                            class="driver-vehicle-photo-preview"
                            data-driver-photo-preview
                        >
                            @if ($savedPhoto)
                                <img
                                    src="{{ route(
                                        'repartidor.vehicles.photos.show',
                                        [
                                            'vehicle' => $vehicle,
                                            'photo' => $savedPhoto,
                                            'size' => 'thumb',
                                        ]
                                    ) }}"
                                    alt="{{ $label }} de {{ $vehicle->vehicle_code }}"
                                    loading="lazy"
                                >
                            @else
                                <span>Sin fotografía</span>
                                <small>
                                    Selecciona archivo o utiliza la cámara.
                                </small>
                            @endif
                        </div>

                        <div class="driver-vehicle-photo-actions">
                            <label class="driver-vehicle-photo-button">
                                <span>Seleccionar archivo</span>

                                <input
                                    type="file"
                                    name="vehicle_photos[{{ $position }}]"
                                    accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp"
                                    data-driver-photo-input
                                    @disabled($vehicleLocked)
                                >
                            </label>

                            <label class="driver-vehicle-photo-button">
                                <span>Usar cámara</span>

                                <input
                                    type="file"
                                    name="vehicle_camera[{{ $position }}]"
                                    accept="image/*"
                                    capture="environment"
                                    data-driver-photo-input
                                    @disabled($vehicleLocked)
                                >
                            </label>
                        </div>

                        <small
                            class="driver-vehicle-photo-file"
                            data-driver-photo-filename
                        >
                            {{ $savedPhoto?->original_name
                                ?: 'Ningún archivo seleccionado' }}
                        </small>
                    </article>
                @endforeach
            </div>

            @include(
                'portals.repartidor.partials.vehicle-360',
                ['vehicle' => $vehicle]
            )
        </div>
    </details>


    <details class="driver-vehicle-section">
        <summary>
            <span>♢</span>
            <strong>Seguro del vehículo</strong>
            <b>⌄</b>
        </summary>

        <div class="driver-vehicle-section__body">
            <div class="driver-vehicle-grid driver-vehicle-grid--2">
                <label>
                    <span>Aseguradora</span>
                    <input
                        type="text"
                        name="insurer"
                        value="{{ $vehicle?->insurer }}"
                        placeholder="Ej. AXA, GNP, Qualitas"
                        @readonly($vehicleLocked)
                    >
                </label>

                <label>
                    <span>Número de póliza</span>
                    <input
                        type="text"
                        name="policy_number"
                        value="{{ $vehicle?->policy_number }}"
                        placeholder="Ej. POL-123456"
                        @readonly($vehicleLocked)
                    >
                </label>

                <label>
                    <span>Tipo de cobertura</span>
                    <select name="coverage_type" @disabled($vehicleLocked)>
                        <option value="">Seleccionar</option>
                        <option value="basic" @selected($vehicle?->coverage_type === 'basic')>
                            Básica
                        </option>
                        <option value="limited" @selected($vehicle?->coverage_type === 'limited')>
                            Limitada
                        </option>
                        <option value="broad" @selected($vehicle?->coverage_type === 'broad')>
                            Amplia
                        </option>
                    </select>
                </label>

                <label>
                    <span>Estatus del seguro</span>
                    <select
                        name="insurance_status"
                        @disabled($vehicleLocked)
                        required
                    >
                        <option value="pending" @selected(($vehicle?->insurance_status ?? 'pending') === 'pending')>
                            Pendiente de revisión
                        </option>
                        <option value="not_required" @selected($vehicle?->insurance_status === 'not_required')>
                            No requerido
                        </option>
                        <option value="under_review" @selected($vehicle?->insurance_status === 'under_review')>
                            En revisión
                        </option>
                        <option value="approved" @selected($vehicle?->insurance_status === 'approved')>
                            Aprobado
                        </option>
                        <option value="rejected" @selected($vehicle?->insurance_status === 'rejected')>
                            Rechazado
                        </option>
                        <option value="expired" @selected($vehicle?->insurance_status === 'expired')>
                            Vencido
                        </option>
                    </select>
                </label>

                <label>
                    <span>Fecha de inicio</span>
                    <input
                        type="date"
                        name="insurance_starts_at"
                        value="{{ $vehicle?->insurance_starts_at?->format('Y-m-d') }}"
                        @readonly($vehicleLocked)
                    >
                </label>

                <label>
                    <span>Fecha de vencimiento</span>
                    <input
                        type="date"
                        name="insurance_expires_at"
                        value="{{ $vehicle?->insurance_expires_at?->format('Y-m-d') }}"
                        @readonly($vehicleLocked)
                    >
                </label>

                <label>
                    <span>Prima / costo del seguro</span>
                    <input
                        type="number"
                        name="insurance_cost"
                        step="0.01"
                        min="0"
                        value="{{ $vehicle?->insurance_cost }}"
                        @readonly($vehicleLocked)
                    >
                </label>

                <label>
                    <span>Teléfono de asistencia</span>
                    <input
                        type="tel"
                        name="assistance_phone"
                        value="{{ $vehicle?->assistance_phone }}"
                        @readonly($vehicleLocked)
                    >
                </label>

                <label>
                    <span>Archivo de póliza</span>
                    <input
                        type="file"
                        name="policy_file"
                        accept=".pdf,.jpg,.jpeg,.png,.webp"
                        @disabled($vehicleLocked)
                    >
                    @if ($vehicle?->policy_original_name)
                        <small>{{ $vehicle->policy_original_name }}</small>
                    @endif
                </label>

                <label>
                    <span>Recibo de pago</span>
                    <input
                        type="file"
                        name="receipt_file"
                        accept=".pdf,.jpg,.jpeg,.png,.webp"
                        @disabled($vehicleLocked)
                    >
                    @if ($vehicle?->receipt_original_name)
                        <small>{{ $vehicle->receipt_original_name }}</small>
                    @endif
                </label>

                <label>
                    <span>Control de vigencia</span>
                    <input
                        type="number"
                        name="expiration_alert_days"
                        min="1"
                        max="180"
                        value="{{ $vehicle?->expiration_alert_days ?? 15 }}"
                        @readonly($vehicleLocked)
                        required
                    >
                    <small>Días antes para alertar el vencimiento.</small>
                </label>

                <label>
                    <span>Notas internas</span>
                    <textarea
                        name="internal_notes"
                        rows="4"
                        @readonly($vehicleLocked)
                    >{{ $vehicle?->internal_notes }}</textarea>
                </label>
            </div>
        </div>
    </details>

    <details class="driver-vehicle-section">
        <summary>
            <span>◇</span>
            <strong>Datos asignados</strong>
            <b>⌄</b>
        </summary>

        <div class="driver-vehicle-section__body">
            <div class="driver-vehicle-grid driver-vehicle-grid--2">
                <label>
                    <span>ID vehículo</span>
                    <input
                        type="text"
                        value="{{ $vehicle?->vehicle_code ?: 'Se asignará al guardar' }}"
                        readonly
                    >
                </label>

                <label>
                    <span>ID repartidor</span>
                    <input
                        type="text"
                        value="{{ $driver->id }}"
                        readonly
                    >
                </label>

                <label>
                    <span>Estatus operativo</span>
                    <input
                        type="text"
                        value="{{ str_replace('_', ' ', $vehicle?->status ?: 'borrador') }}"
                        readonly
                    >
                </label>
            </div>
        </div>
    </details>

    <div class="driver-vehicle-form__actions">
        @if ($vehicleLocked)
            <span>🔒 Vehículo protegido</span>

            <button
                type="button"
                class="driver-vehicle-btn driver-vehicle-btn--outline"
                data-driver-update-request-open
            >
                Solicitar actualización
            </button>
        @else
            <button
                type="submit"
                class="driver-vehicle-btn driver-vehicle-btn--dark"
            >
                ▣ Guardar vehículo
            </button>
        @endif
    </div>
</form>




