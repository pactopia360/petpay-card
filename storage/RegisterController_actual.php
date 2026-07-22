<?php

namespace App\Http\Controllers\Comercio\Auth;

use App\Http\Controllers\Controller;
use App\Models\Comercio\CommerceUser;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class RegisterController extends Controller
{
    public function showRegistrationForm(Request $request): View
    {
        $googleRegistration = $request->session()->get(
            'commerce_google_registration'
        );

        return view(
            'auth.comercio.register',
            compact('googleRegistration')
        );
    }

    public function register(Request $request): RedirectResponse
    {
        $googleRegistration = $request->session()->get(
            'commerce_google_registration'
        );

        $isGoogleRegistration = is_array($googleRegistration)
            && ! empty($googleRegistration['google_id'])
            && ! empty($googleRegistration['email']);

        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:120'],
            'last_name' => ['nullable', 'string', 'max:120'],

            'business_name' => ['required', 'string', 'max:180'],
            'brand_name' => ['nullable', 'string', 'max:180'],
            'business_type' => ['required', 'string', 'max:120'],

            'email' => [
                'required',
                'email',
                'max:180',
                Rule::unique(
                    'mysql_comercio.commerce_users',
                    'email'
                ),
            ],

            'phone' => [
                'required',
                'string',
                'max:30',
                Rule::unique(
                    'mysql_comercio.commerce_users',
                    'phone'
                ),
            ],

            'business_phone' => ['nullable', 'string', 'max:30'],
            'business_email' => ['nullable', 'email', 'max:180'],
            'website_url' => ['nullable', 'string', 'max:500'],

            'business_address' => ['required', 'string', 'max:500'],
            'floor_office' => ['nullable', 'string', 'max:120'],

            'whatsapp_enabled' => ['nullable', 'boolean'],

            'sells_products' => ['nullable', 'boolean'],
            'offers_services' => ['nullable', 'boolean'],
            'has_own_delivery' => ['nullable', 'boolean'],
            'uses_petpay_delivery' => ['nullable', 'boolean'],

            'password' => $isGoogleRegistration
                ? ['nullable', 'string', 'min:8', 'confirmed']
                : ['required', 'string', 'min:8', 'confirmed'],

            'terms' => ['accepted'],
        ], [
            'first_name.required' => 'Ingresa el nombre del responsable.',
            'business_name.required' => 'Ingresa el nombre comercial.',
            'business_type.required' => 'Selecciona el tipo de comercio.',

            'email.required' => 'Ingresa el correo electrónico.',
            'email.email' => 'Ingresa un correo electrónico válido.',
            'email.unique' => 'Este correo ya está registrado.',

            'phone.required' => 'Ingresa el teléfono del responsable.',
            'phone.unique' => 'Este teléfono ya está registrado.',

            'business_email.email' => 'Ingresa un correo del comercio válido.',
            'business_address.required' => 'Ingresa la dirección del comercio.',

            'password.required' => 'Crea una contraseña.',
            'password.min' => 'La contraseña debe tener al menos 8 caracteres.',
            'password.confirmed' => 'La confirmación de contraseña no coincide.',

            'terms.accepted' => 'Debes aceptar los términos y el aviso de privacidad.',
        ]);

        if ($isGoogleRegistration) {
            $sessionEmail = strtolower(
                trim((string) $googleRegistration['email'])
            );

            $submittedEmail = strtolower(
                trim((string) $validated['email'])
            );

            if ($sessionEmail !== $submittedEmail) {
                return back()
                    ->withInput()
                    ->withErrors([
                        'email' => 'El correo no coincide con la cuenta de Google conectada.',
                    ]);
            }
        }

        $sellsProducts = $request->boolean(
            'sells_products',
            true
        );

        $offersServices = $request->boolean(
            'offers_services',
            false
        );

        if (! $sellsProducts && ! $offersServices) {
            return back()
                ->withInput()
                ->withErrors([
                    'sells_products' => 'Selecciona al menos si vendes productos u ofreces servicios.',
                ]);
        }

        $password = $isGoogleRegistration
            ? Hash::make(Str::random(64))
            : Hash::make($validated['password']);

        CommerceUser::create([
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'] ?? null,
            'name' => trim(
                $validated['first_name'].' '.
                ($validated['last_name'] ?? '')
            ),

            'email' => strtolower(trim($validated['email'])),
            'phone' => $validated['phone'],
            'password' => $password,

            'google_id' => $isGoogleRegistration
                ? $googleRegistration['google_id']
                : null,

            'google_avatar' => $isGoogleRegistration
                ? ($googleRegistration['google_avatar'] ?? null)
                : null,

            'auth_provider' => $isGoogleRegistration
                ? 'google'
                : 'email',

            'email_verified_at' => $isGoogleRegistration
                ? now()
                : null,

            'business_name' => $validated['business_name'],
            'brand_name' => $validated['brand_name'] ?? null,
            'business_type' => $validated['business_type'],

            'business_phone' => $validated['business_phone']
                ?? $validated['phone'],

            'business_email' => $validated['business_email']
                ?? $validated['email'],

            'website_url' => $validated['website_url'] ?? null,

            'business_address' => $validated['business_address'],
            'floor_office' => $validated['floor_office'] ?? null,

            'whatsapp_enabled' => $request->boolean(
                'whatsapp_enabled'
            ),

            'terms_accepted_at' => now(),

            'sells_products' => $sellsProducts,
            'offers_services' => $offersServices,
            'has_own_delivery' => $request->boolean(
                'has_own_delivery'
            ),
            'uses_petpay_delivery' => $request->boolean(
                'uses_petpay_delivery',
                true
            ),

            'status' => 'pending',
            'approval_status' => 'pending',
            'is_open' => false,
            'commission_percent' => 0,
        ]);

        $request->session()->forget(
            'commerce_google_registration'
        );

        return redirect()
            ->route('comercio.login')
            ->with(
                'status',
                $isGoogleRegistration
                    ? 'Tu comercio fue registrado con Google. Admin revisará tu solicitud para activar tu cuenta.'
                    : 'Tu comercio fue registrado correctamente. Admin revisará tu solicitud para activar tu cuenta.'
            );
    }
}