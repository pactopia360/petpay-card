<?php

namespace App\Http\Controllers\Repartidor\Auth;

use App\Http\Controllers\Controller;
use App\Models\Repartidor\DriverUser;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class RegisterController extends Controller
{
    public function showRegistrationForm(Request $request): View
    {
        $googleRegistration = $request->session()->get('driver_google_registration');

        return view('auth.repartidor.register', compact('googleRegistration'));
    }

    public function register(Request $request): RedirectResponse
    {
        $googleRegistration = $request->session()->get('driver_google_registration');

        $isGoogleRegistration = is_array($googleRegistration)
            && ! empty($googleRegistration['google_id'])
            && ! empty($googleRegistration['email']);

        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:120'],
            'last_name' => ['nullable', 'string', 'max:120'],
            'email' => [
                'required',
                'email',
                'max:191',
                Rule::unique('mysql_repartidor.driver_users', 'email'),
            ],
            'phone' => [
                'required',
                'string',
                'max:30',
                Rule::unique('mysql_repartidor.driver_users', 'phone'),
            ],
            'vehicle_type' => ['required', Rule::in(['bicycle', 'motorcycle', 'car', 'walking'])],
            'vehicle_make' => ['nullable', 'string', 'max:100'],
            'vehicle_model' => ['nullable', 'string', 'max:100'],
            'vehicle_plate' => ['nullable', 'string', 'max:50'],
            'license_number' => ['nullable', 'string', 'max:100'],
            'operation_zone' => ['required', 'string', 'max:180'],
            'state' => ['required', 'string', 'max:120'],
            'city' => ['required', 'string', 'max:120'],
            'availability_type' => [
                'required',
                Rule::in(['full_time', 'part_time', 'weekends', 'flexible']),
            ],
            'registration_latitude' => ['required', 'numeric', 'between:-90,90'],
            'registration_longitude' => ['required', 'numeric', 'between:-180,180'],
            'registration_accuracy_meters' => ['required', 'numeric', 'min:0', 'max:5000'],
            'registration_location_source' => ['required', Rule::in(['browser'])],
            'registration_address_detected' => ['nullable', 'string', 'max:500'],
            'registration_location_captured_at' => ['required', 'date'],
            'location_consent' => ['accepted'],
            'whatsapp_enabled' => ['nullable', 'boolean'],
            'password' => $isGoogleRegistration
                ? ['nullable', 'string', 'min:8', 'confirmed']
                : ['required', 'string', 'min:8', 'confirmed'],
            'terms' => ['accepted'],
        ], [
            'first_name.required' => 'Ingresa tu nombre.',
            'email.required' => 'Ingresa tu correo electrónico.',
            'email.unique' => 'Este correo ya está registrado.',
            'phone.required' => 'Ingresa tu teléfono móvil.',
            'phone.unique' => 'Este teléfono ya está registrado.',
            'vehicle_type.required' => 'Selecciona el medio de transporte.',
            'operation_zone.required' => 'Indica tu zona de operación.',
            'state.required' => 'Indica tu estado.',
            'city.required' => 'Indica tu ciudad.',
            'availability_type.required' => 'Selecciona tu disponibilidad.',
            'registration_latitude.required' => 'Debes confirmar tu ubicación antes de enviar el registro.',
            'registration_longitude.required' => 'Debes confirmar tu ubicación antes de enviar el registro.',
            'registration_accuracy_meters.required' => 'No pudimos validar la precisión de tu ubicación.',
            'registration_location_source.required' => 'Debes confirmar tu ubicación.',
            'registration_location_captured_at.required' => 'Debes capturar nuevamente tu ubicación.',
            'location_consent.accepted' => 'Debes autorizar el uso de tu ubicación.',
            'password.required' => 'Crea una contraseña.',
            'password.confirmed' => 'La confirmación de contraseña no coincide.',
            'terms.accepted' => 'Debes aceptar los términos y el aviso de privacidad.',
        ]);

        if ($isGoogleRegistration) {
            $sessionEmail = strtolower(trim((string) $googleRegistration['email']));
            $submittedEmail = strtolower(trim((string) $validated['email']));

            if ($sessionEmail !== $submittedEmail) {
                return back()
                    ->withInput()
                    ->withErrors(['email' => 'El correo no coincide con la cuenta de Google conectada.']);
            }
        }

        $requiresMotorData = in_array($validated['vehicle_type'], ['motorcycle', 'car'], true);

        if ($requiresMotorData) {
            $request->validate([
                'vehicle_plate' => ['required', 'string', 'max:50'],
                'license_number' => ['required', 'string', 'max:100'],
            ], [
                'vehicle_plate.required' => 'La placa es obligatoria para moto o automóvil.',
                'license_number.required' => 'La licencia es obligatoria para moto o automóvil.',
            ]);
        }

        $driver = DriverUser::query()->create([
            'first_name' => trim($validated['first_name']),
            'last_name' => filled($validated['last_name'] ?? null)
                ? trim((string) $validated['last_name'])
                : null,
            'name' => trim($validated['first_name'].' '.($validated['last_name'] ?? '')),
            'email' => strtolower(trim($validated['email'])),
            'phone' => trim($validated['phone']),
            'password' => $isGoogleRegistration
                ? Hash::make(Str::random(64))
                : $validated['password'],
            'google_id' => $isGoogleRegistration
                ? $googleRegistration['google_id']
                : null,
            'google_avatar' => $isGoogleRegistration
                ? ($googleRegistration['google_avatar'] ?? null)
                : null,
            'auth_provider' => $isGoogleRegistration ? 'google' : 'email',
            'vehicle_type' => $validated['vehicle_type'],
            'vehicle_make' => filled($validated['vehicle_make'] ?? null)
                ? trim((string) $validated['vehicle_make'])
                : null,
            'vehicle_model' => filled($validated['vehicle_model'] ?? null)
                ? trim((string) $validated['vehicle_model'])
                : null,
            'vehicle_plate' => $requiresMotorData
                ? strtoupper(trim((string) $validated['vehicle_plate']))
                : null,
            'license_number' => $requiresMotorData
                ? strtoupper(trim((string) $validated['license_number']))
                : null,
            'operation_zone' => trim($validated['operation_zone']),
            'state' => trim($validated['state']),
            'city' => trim($validated['city']),
            'availability_type' => $validated['availability_type'],
            'registration_latitude' => $validated['registration_latitude'],
            'registration_longitude' => $validated['registration_longitude'],
            'registration_accuracy_meters' => $validated['registration_accuracy_meters'],
            'registration_location_source' => $validated['registration_location_source'],
            'registration_address_detected' => filled($validated['registration_address_detected'] ?? null)
                ? trim((string) $validated['registration_address_detected'])
                : null,
            'registration_location_captured_at' => $validated['registration_location_captured_at'],
            'registration_ip' => $request->ip(),
            'registration_user_agent' => (string) $request->userAgent(),
            'terms_version' => '2026-07-12',
            'privacy_version' => '2026-07-12',
            'privacy_accepted_at' => now(),
            'whatsapp_enabled' => $request->boolean('whatsapp_enabled', true),
            'terms_accepted_at' => now(),
            'email_verified_at' => $isGoogleRegistration ? now() : null,
            'status' => 'pending',
            'approval_status' => 'pending',
            'is_available' => false,
            'delivery_commission_percent' => 0,
        ]);

        $request->session()->forget('driver_google_registration');

        Auth::guard('repartidor')->login($driver);
        $request->session()->regenerate();

        return redirect()
            ->route('repartidor.registration.pending')
            ->with(
                'status',
                $isGoogleRegistration
                    ? 'Tu cuenta de Google quedó vinculada correctamente.'
                    : 'Tu solicitud de repartidor fue enviada correctamente.'
            );
    }
}
