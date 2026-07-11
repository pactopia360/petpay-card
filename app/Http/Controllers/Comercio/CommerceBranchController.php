<?php

namespace App\Http\Controllers\Comercio;

use App\Http\Controllers\Controller;
use App\Models\Comercio\CommerceBranch;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class CommerceBranchController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $commerce = Auth::guard('comercio')->user();

        abort_unless($commerce, 401);

        $validated = $this->validateBranch($request, (int) $commerce->id);
        $coordinates = $this->parseCoordinates($validated['google_coordinates'] ?? null);
        $this->ensureCoordinatesAreValid($validated['google_coordinates'] ?? null, $coordinates);

        $payload = $this->branchPayload(
            request: $request,
            validated: $validated,
            coordinates: $coordinates,
            commerceUserId: (int) $commerce->id
        );

        $branch = CommerceBranch::create($payload);

        return redirect()
            ->route('comercio.dashboard', ['tab' => 'sucursales'])
            ->with(
                $branch->is_complete ? 'status' : 'warning',
                $branch->is_complete
                    ? 'Sucursal guardada correctamente. La bandera está en verde.'
                    : 'Sucursal guardada, pero faltan datos: '.implode(', ', $branch->missing_fields ?? []).'.'
            );
    }

    public function update(Request $request, CommerceBranch $branch): RedirectResponse
    {
        $commerce = Auth::guard('comercio')->user();

        abort_unless($commerce, 401);
        abort_unless((int) $branch->commerce_user_id === (int) $commerce->id, 403);

        $validated = $this->validateBranch(
            request: $request,
            commerceUserId: (int) $commerce->id,
            branchId: (int) $branch->id
        );

        $coordinates = $this->parseCoordinates($validated['google_coordinates'] ?? null);
        $this->ensureCoordinatesAreValid($validated['google_coordinates'] ?? null, $coordinates);

        $branch->update(
            $this->branchPayload(
                request: $request,
                validated: $validated,
                coordinates: $coordinates,
                commerceUserId: (int) $commerce->id
            )
        );

        $branch->refresh();

        return redirect()
            ->route('comercio.dashboard', ['tab' => 'sucursales'])
            ->with(
                $branch->is_complete ? 'status' : 'warning',
                $branch->is_complete
                    ? 'Sucursal actualizada correctamente. La bandera está en verde.'
                    : 'Sucursal actualizada, pero faltan datos: '.implode(', ', $branch->missing_fields ?? []).'.'
            );
    }

    public function destroy(CommerceBranch $branch): RedirectResponse
    {
        $commerce = Auth::guard('comercio')->user();

        abort_unless($commerce, 401);
        abort_unless((int) $branch->commerce_user_id === (int) $commerce->id, 403);

        $branch->delete();

        return redirect()
            ->route('comercio.dashboard', ['tab' => 'sucursales'])
            ->with('status', 'Sucursal eliminada correctamente.');
    }

    public function toggleService(CommerceBranch $branch): RedirectResponse
    {
        $commerce = Auth::guard('comercio')->user();

        abort_unless($commerce, 401);
        abort_unless((int) $branch->commerce_user_id === (int) $commerce->id, 403);

        $branch->forceFill([
            'is_open' => ! $branch->is_open,
        ])->save();

        return redirect()
            ->route('comercio.dashboard', ['tab' => 'sucursales'])
            ->with(
                'status',
                $branch->is_open
                    ? 'Sucursal visible para usuarios.'
                    : 'Sucursal oculta para usuarios.'
            );
    }

    private function validateBranch(
        Request $request,
        int $commerceUserId,
        ?int $branchId = null
    ): array {
        $branchCodeRule = Rule::unique(
            'mysql_comercio.commerce_branches',
            'branch_code'
        )->where(
            fn ($query) => $query->where('commerce_user_id', $commerceUserId)
        );

        if ($branchId !== null) {
            $branchCodeRule->ignore($branchId);
        }

        return $request->validate([
            'chain_name' => ['nullable', 'string', 'max:160'],
            'branch_name' => ['nullable', 'string', 'max:160'],
            'branch_code' => ['nullable', 'string', 'max:50', $branchCodeRule],

            'google_coordinates' => ['nullable', 'string', 'max:2048'],

            'street' => ['nullable', 'string', 'max:180'],
            'neighborhood' => ['nullable', 'string', 'max:120'],
            'postal_code' => ['nullable', 'string', 'max:12'],
            'state' => ['nullable', 'string', Rule::in($this->stateKeys())],

            'phone' => ['nullable', 'regex:/^[0-9]{10}$/'],
            'email' => ['nullable', 'email:rfc', 'max:191'],
            'website' => ['nullable', 'string', 'max:191'],
            'whatsapp_phone' => ['nullable', 'regex:/^[0-9]{10}$/'],

            'service_days' => ['nullable', 'array'],
            'service_days.*' => ['string', Rule::in(['L', 'M', 'X', 'J', 'V', 'S', 'D'])],
            'service_open_time' => ['nullable', 'date_format:H:i'],
            'service_close_time' => [
                'nullable',
                'date_format:H:i',
                'after:service_open_time',
            ],

            'phone_verified' => ['nullable', 'boolean'],
            'email_verified' => ['nullable', 'boolean'],
            'is_open' => ['nullable', 'boolean'],
        ], [
            'branch_code.unique' => 'El código de sucursal ya está siendo usado por otra sucursal de este comercio.',
            'phone.regex' => 'El teléfono debe tener 10 dígitos.',
            'email.email' => 'Ingresa un correo electrónico válido.',
            'whatsapp_phone.regex' => 'El WhatsApp debe tener 10 dígitos.',
            'state.in' => 'Selecciona un estado válido.',
            'service_open_time.date_format' => 'La hora de apertura no es válida.',
            'service_close_time.date_format' => 'La hora de cierre no es válida.',
            'service_close_time.after' => 'La hora de cierre debe ser posterior a la hora de apertura.',
        ]);
    }

    private function branchPayload(
        Request $request,
        array $validated,
        array $coordinates,
        int $commerceUserId
    ): array {
        $normalizedCoordinates = null;

        if ($coordinates['latitude'] !== null && $coordinates['longitude'] !== null) {
            $normalizedCoordinates = number_format($coordinates['latitude'], 8, '.', '')
                .', '
                .number_format($coordinates['longitude'], 8, '.', '');
        }

        $validated['google_coordinates'] = $normalizedCoordinates
            ?? ($validated['google_coordinates'] ?? null);

        $missingFields = $this->missingFields($validated);

        return [
            'commerce_user_id' => $commerceUserId,

            'chain_name' => $validated['chain_name'] ?: 'Cadena sin nombre',
            'branch_name' => $validated['branch_name'] ?: 'Sucursal sin nombre',
            'branch_code' => $validated['branch_code'] ?: null,

            'google_coordinates' => $validated['google_coordinates'] ?: null,
            'latitude' => $coordinates['latitude'],
            'longitude' => $coordinates['longitude'],

            'street' => $validated['street'] ?: 'Dirección pendiente',
            'neighborhood' => $validated['neighborhood'] ?: null,
            'postal_code' => $validated['postal_code'] ?: null,
            'state' => $validated['state'] ?: null,

            'phone' => $validated['phone'] ?: null,
            'email' => $validated['email'] ?: null,
            'website' => $this->normalizeWebsite($validated['website'] ?? null),
            'whatsapp_phone' => $validated['whatsapp_phone'] ?: null,

            'service_days' => array_values($validated['service_days'] ?? []),
            'service_open_time' => $validated['service_open_time'] ?: null,
            'service_close_time' => $validated['service_close_time'] ?: null,

            'phone_verified' => $request->boolean('phone_verified'),
            'email_verified' => $request->boolean('email_verified'),
            'is_open' => $request->boolean('is_open', true),

            'missing_fields' => $missingFields,
            'status_flag' => $missingFields === [] ? 'complete' : 'incomplete',
        ];
    }

    private function missingFields(array $data): array
    {
        $labels = [
            'chain_name' => 'Nombre de la cadena',
            'branch_name' => 'Nombre de la sucursal',
            'branch_code' => 'Código sucursal',
            'google_coordinates' => 'Coordenadas Google',
            'street' => 'Calle y número',
            'neighborhood' => 'Colonia',
            'postal_code' => 'CP',
            'state' => 'Estado',
            'phone' => 'Teléfono',
            'email' => 'Correo electrónico',
            'website' => 'Página WEB',
            'whatsapp_phone' => 'Teléfono WhatsApp',
            'service_days' => 'Días de servicio',
            'service_open_time' => 'Hora de apertura',
            'service_close_time' => 'Hora de cierre',
        ];

        $missing = [];

        foreach ($labels as $field => $label) {
            $value = $data[$field] ?? null;

            if ($value === null || $value === '' || $value === []) {
                $missing[] = $label;
            }
        }

        return $missing;
    }

    private function parseCoordinates(?string $value): array
    {
        if (! filled($value)) {
            return [
                'latitude' => null,
                'longitude' => null,
            ];
        }

        $decoded = urldecode(trim($value));

        $patterns = [
            '/@(-?\d{1,2}(?:\.\d+)?),\s*(-?\d{1,3}(?:\.\d+)?)/',
            '/[?&](?:query|q|ll|center)=(-?\d{1,2}(?:\.\d+)?)(?:%2C|,|\s)+(-?\d{1,3}(?:\.\d+)?)/i',
            '/(-?\d{1,2}(?:\.\d+)?)\s*[,;]\s*(-?\d{1,3}(?:\.\d+)?)/',
        ];

        foreach ($patterns as $pattern) {
            if (! preg_match($pattern, $decoded, $matches)) {
                continue;
            }

            $latitude = (float) $matches[1];
            $longitude = (float) $matches[2];

            if (
                $latitude >= -90
                && $latitude <= 90
                && $longitude >= -180
                && $longitude <= 180
            ) {
                return [
                    'latitude' => $latitude,
                    'longitude' => $longitude,
                ];
            }
        }

        return [
            'latitude' => null,
            'longitude' => null,
        ];
    }

    private function ensureCoordinatesAreValid(
        ?string $originalValue,
        array $coordinates
    ): void {
        if (! filled($originalValue)) {
            return;
        }

        if ($coordinates['latitude'] !== null && $coordinates['longitude'] !== null) {
            return;
        }

        throw ValidationException::withMessages([
            'google_coordinates' => 'Ingresa coordenadas válidas en formato latitud, longitud o un enlace de Google Maps que incluya coordenadas.',
        ]);
    }

    private function normalizeWebsite(?string $website): ?string
    {
        if (! filled($website)) {
            return null;
        }

        $website = trim($website);

        if (! preg_match('/^https?:\/\//i', $website)) {
            $website = 'https://'.$website;
        }

        return filter_var($website, FILTER_VALIDATE_URL)
            ? $website
            : null;
    }

    private function stateKeys(): array
    {
        return [
            'aguascalientes',
            'baja_california',
            'baja_california_sur',
            'campeche',
            'chiapas',
            'chihuahua',
            'ciudad_de_mexico',
            'coahuila',
            'colima',
            'durango',
            'estado_de_mexico',
            'guanajuato',
            'guerrero',
            'hidalgo',
            'jalisco',
            'michoacan',
            'morelos',
            'nayarit',
            'nuevo_leon',
            'oaxaca',
            'puebla',
            'queretaro',
            'quintana_roo',
            'san_luis_potosi',
            'sinaloa',
            'sonora',
            'tabasco',
            'tamaulipas',
            'tlaxcala',
            'veracruz',
            'yucatan',
            'zacatecas',
        ];
    }
}
