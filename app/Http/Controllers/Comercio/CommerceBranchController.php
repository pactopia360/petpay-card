<?php

namespace App\Http\Controllers\Comercio;

use App\Http\Controllers\Controller;
use App\Models\Comercio\CommerceBranch;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class CommerceBranchController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $commerce = Auth::guard('comercio')->user();

        $validated = $this->validateBranch($request);
        $coordinates = $this->parseCoordinates($validated['google_coordinates'] ?? null);
        $missingFields = $this->missingFields($validated);

        CommerceBranch::create([
            'commerce_user_id' => $commerce->id,

            'chain_name' => $validated['chain_name'] ?: 'Cadena sin nombre',
            'branch_name' => $validated['branch_name'] ?: 'Sucursal sin nombre',
            'branch_code' => $validated['branch_code'] ?? null,

            'google_coordinates' => $validated['google_coordinates'] ?? null,
            'latitude' => $coordinates['latitude'],
            'longitude' => $coordinates['longitude'],

            'street' => $validated['street'] ?: 'Dirección pendiente',
            'neighborhood' => $validated['neighborhood'] ?? null,
            'postal_code' => $validated['postal_code'] ?? null,
            'state' => $validated['state'] ?? null,

            'phone' => $validated['phone'] ?? null,
            'email' => $validated['email'] ?? null,
            'website' => $this->normalizeWebsite($validated['website'] ?? null),
            'whatsapp_phone' => $validated['whatsapp_phone'] ?? null,

            'service_days' => $validated['service_days'] ?? [],
            'service_open_time' => $validated['service_open_time'] ?? null,
            'service_close_time' => $validated['service_close_time'] ?? null,

            'phone_verified' => $request->boolean('phone_verified'),
            'email_verified' => $request->boolean('email_verified'),

            'is_open' => $request->boolean('is_open', true),
            'missing_fields' => $missingFields,
            'status_flag' => count($missingFields) === 0 ? 'complete' : 'incomplete',
        ]);

        return redirect()
            ->route('comercio.dashboard', ['tab' => 'sucursales'])
            ->with(
                count($missingFields) === 0 ? 'status' : 'warning',
                count($missingFields) === 0
                    ? 'Sucursal guardada correctamente. La bandera está en verde.'
                    : 'Sucursal guardada, pero faltan datos: ' . implode(', ', $missingFields) . '.'
            );
    }

    public function update(Request $request, CommerceBranch $branch): RedirectResponse
    {
        $commerce = Auth::guard('comercio')->user();

        abort_unless((int) $branch->commerce_user_id === (int) $commerce->id, 403);

        $validated = $this->validateBranch($request);
        $coordinates = $this->parseCoordinates($validated['google_coordinates'] ?? null);
        $missingFields = $this->missingFields($validated);

        $branch->update([
            'chain_name' => $validated['chain_name'] ?: 'Cadena sin nombre',
            'branch_name' => $validated['branch_name'] ?: 'Sucursal sin nombre',
            'branch_code' => $validated['branch_code'] ?? null,

            'google_coordinates' => $validated['google_coordinates'] ?? null,
            'latitude' => $coordinates['latitude'],
            'longitude' => $coordinates['longitude'],

            'street' => $validated['street'] ?: 'Dirección pendiente',
            'neighborhood' => $validated['neighborhood'] ?? null,
            'postal_code' => $validated['postal_code'] ?? null,
            'state' => $validated['state'] ?? null,

            'phone' => $validated['phone'] ?? null,
            'email' => $validated['email'] ?? null,
            'website' => $this->normalizeWebsite($validated['website'] ?? null),
            'whatsapp_phone' => $validated['whatsapp_phone'] ?? null,

            'service_days' => $validated['service_days'] ?? [],
            'service_open_time' => $validated['service_open_time'] ?? null,
            'service_close_time' => $validated['service_close_time'] ?? null,

            'phone_verified' => $request->boolean('phone_verified'),
            'email_verified' => $request->boolean('email_verified'),

            'is_open' => $request->boolean('is_open'),
            'missing_fields' => $missingFields,
            'status_flag' => count($missingFields) === 0 ? 'complete' : 'incomplete',
        ]);

        return redirect()
            ->route('comercio.dashboard', ['tab' => 'sucursales'])
            ->with(
                count($missingFields) === 0 ? 'status' : 'warning',
                count($missingFields) === 0
                    ? 'Sucursal actualizada correctamente. La bandera está en verde.'
                    : 'Sucursal actualizada, pero faltan datos: ' . implode(', ', $missingFields) . '.'
            );
    }

    public function destroy(CommerceBranch $branch): RedirectResponse
    {
        $commerce = Auth::guard('comercio')->user();

        abort_unless((int) $branch->commerce_user_id === (int) $commerce->id, 403);

        $branch->delete();

        return redirect()
            ->route('comercio.dashboard', ['tab' => 'sucursales'])
            ->with('status', 'Sucursal eliminada correctamente.');
    }

    public function toggleService(CommerceBranch $branch): RedirectResponse
    {
        $commerce = Auth::guard('comercio')->user();

        abort_unless((int) $branch->commerce_user_id === (int) $commerce->id, 403);

        $branch->forceFill([
            'is_open' => ! $branch->is_open,
        ])->save();

        return redirect()
            ->route('comercio.dashboard', ['tab' => 'sucursales'])
            ->with('status', $branch->is_open ? 'Sucursal en servicio.' : 'Sucursal apagada.');
    }

    private function validateBranch(Request $request): array
    {
        return $request->validate([
            'chain_name' => ['nullable', 'string', 'max:160'],
            'branch_name' => ['nullable', 'string', 'max:160'],
            'branch_code' => ['nullable', 'string', 'max:50'],

            'google_coordinates' => ['nullable', 'string', 'max:120'],

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
            'service_close_time' => ['nullable', 'date_format:H:i'],
        ], [
            'phone.regex' => 'El teléfono debe tener 10 dígitos.',
            'email.email' => 'Ingresa un correo electrónico válido.',
            'whatsapp_phone.regex' => 'El WhatsApp debe tener 10 dígitos.',
            'state.in' => 'Selecciona un estado válido.',
            'service_open_time.date_format' => 'La hora de apertura no es válida.',
            'service_close_time.date_format' => 'La hora de cierre no es válida.',
        ]);
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

    private function parseCoordinates(?string $coordinates): array
    {
        if (! $coordinates) {
            return [
                'latitude' => null,
                'longitude' => null,
            ];
        }

        preg_match_all('/-?\d+(?:\.\d+)?/', $coordinates, $matches);

        $numbers = $matches[0] ?? [];

        return [
            'latitude' => isset($numbers[0]) ? (float) $numbers[0] : null,
            'longitude' => isset($numbers[1]) ? (float) $numbers[1] : null,
        ];
    }

    private function normalizeWebsite(?string $website): ?string
    {
        if (! $website) {
            return null;
        }

        $website = trim($website);

        if (! preg_match('/^https?:\/\//i', $website)) {
            return 'https://' . $website;
        }

        return $website;
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