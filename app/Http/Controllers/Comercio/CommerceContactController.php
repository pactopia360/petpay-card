<?php

namespace App\Http\Controllers\Comercio;

use App\Http\Controllers\Controller;
use App\Models\Comercio\CommerceContact;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class CommerceContactController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $commerce = Auth::guard('comercio')->user();

        $validated = $this->validateContact($request);

        $isPrimary = $request->boolean('is_primary');

        if ($isPrimary) {
            $this->clearPrimaryContact((int) $commerce->id);
        }

        CommerceContact::create([
            'commerce_user_id' => $commerce->id,
            'first_name' => $validated['first_name'],
            'last_name_paternal' => $validated['last_name_paternal'] ?? null,
            'last_name_maternal' => $validated['last_name_maternal'] ?? null,
            'street' => $validated['street'] ?? null,
            'neighborhood' => $validated['neighborhood'] ?? null,
            'postal_code' => $validated['postal_code'] ?? null,
            'state' => $validated['state'] ?? null,
            'phone' => $validated['phone'] ?? null,
            'email' => $validated['email'] ?? null,
            'phone_verified_at' => $request->boolean('phone_verified') ? now() : null,
            'email_verified_at' => $request->boolean('email_verified') ? now() : null,
            'is_primary' => $isPrimary,
        ]);

        return redirect()
            ->route('comercio.dashboard')
            ->with('status', 'Contacto guardado correctamente.');
    }

    public function update(Request $request, CommerceContact $contact): RedirectResponse
    {
        $commerce = Auth::guard('comercio')->user();

        abort_unless((int) $contact->commerce_user_id === (int) $commerce->id, 403);

        $validated = $this->validateContact($request);

        $isPrimary = $request->boolean('is_primary');

        if ($isPrimary) {
            $this->clearPrimaryContact((int) $commerce->id, (int) $contact->id);
        }

        $contact->update([
            'first_name' => $validated['first_name'],
            'last_name_paternal' => $validated['last_name_paternal'] ?? null,
            'last_name_maternal' => $validated['last_name_maternal'] ?? null,
            'street' => $validated['street'] ?? null,
            'neighborhood' => $validated['neighborhood'] ?? null,
            'postal_code' => $validated['postal_code'] ?? null,
            'state' => $validated['state'] ?? null,
            'phone' => $validated['phone'] ?? null,
            'email' => $validated['email'] ?? null,
            'phone_verified_at' => $request->boolean('phone_verified') ? ($contact->phone_verified_at ?: now()) : null,
            'email_verified_at' => $request->boolean('email_verified') ? ($contact->email_verified_at ?: now()) : null,
            'is_primary' => $isPrimary,
        ]);

        return redirect()
            ->route('comercio.dashboard')
            ->with('status', 'Contacto actualizado correctamente.');
    }

    public function destroy(CommerceContact $contact): RedirectResponse
    {
        $commerce = Auth::guard('comercio')->user();

        abort_unless((int) $contact->commerce_user_id === (int) $commerce->id, 403);

        $contact->delete();

        return redirect()
            ->route('comercio.dashboard')
            ->with('status', 'Contacto eliminado correctamente.');
    }

    public function setPrimary(CommerceContact $contact): RedirectResponse
    {
        $commerce = Auth::guard('comercio')->user();

        abort_unless((int) $contact->commerce_user_id === (int) $commerce->id, 403);

        $this->clearPrimaryContact((int) $commerce->id, (int) $contact->id);

        $contact->forceFill([
            'is_primary' => true,
        ])->save();

        return redirect()
            ->route('comercio.dashboard')
            ->with('status', 'Contacto principal actualizado correctamente.');
    }

    private function validateContact(Request $request): array
    {
        return $request->validate([
            'first_name' => ['required', 'string', 'max:120'],
            'last_name_paternal' => ['nullable', 'string', 'max:120'],
            'last_name_maternal' => ['nullable', 'string', 'max:120'],

            'street' => ['nullable', 'string', 'max:180'],
            'neighborhood' => ['nullable', 'string', 'max:120'],
            'postal_code' => ['nullable', 'string', 'max:12'],
            'state' => ['nullable', 'string', Rule::in($this->stateKeys())],

            'phone' => ['nullable', 'regex:/^[0-9]{10}$/'],
            'email' => ['nullable', 'email:rfc,dns', 'max:191'],
        ], [
            'first_name.required' => 'Ingresa el nombre del contacto.',
            'phone.regex' => 'El teléfono debe tener 10 dígitos.',
            'email.email' => 'Ingresa un correo electrónico válido.',
            'state.in' => 'Selecciona un estado válido.',
        ]);
    }

    private function clearPrimaryContact(int $commerceId, ?int $exceptContactId = null): void
    {
        CommerceContact::query()
            ->where('commerce_user_id', $commerceId)
            ->when($exceptContactId, fn ($query) => $query->where('id', '!=', $exceptContactId))
            ->update([
                'is_primary' => false,
            ]);
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