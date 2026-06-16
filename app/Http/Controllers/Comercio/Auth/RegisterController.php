<?php

namespace App\Http\Controllers\Comercio\Auth;

use App\Http\Controllers\Controller;
use App\Models\Comercio\CommerceUser;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class RegisterController extends Controller
{
    public function showRegistrationForm(): View
    {
        return view('auth.comercio.register');
    }

    public function register(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:120'],
            'last_name' => ['nullable', 'string', 'max:120'],

            'business_name' => ['required', 'string', 'max:180'],
            'business_type' => ['required', 'string', 'max:120'],

            'email' => [
                'required',
                'email',
                'max:180',
                Rule::unique('mysql_comercio.commerce_users', 'email'),
            ],
            'phone' => [
                'required',
                'string',
                'max:30',
                Rule::unique('mysql_comercio.commerce_users', 'phone'),
            ],

            'business_phone' => ['nullable', 'string', 'max:30'],
            'business_email' => ['nullable', 'email', 'max:180'],
            'business_address' => ['required', 'string', 'max:500'],

            'sells_products' => ['nullable', 'boolean'],
            'offers_services' => ['nullable', 'boolean'],
            'has_own_delivery' => ['nullable', 'boolean'],
            'uses_petpay_delivery' => ['nullable', 'boolean'],

            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ], [
            'first_name.required' => 'Ingresa el nombre del responsable.',
            'business_name.required' => 'Ingresa el nombre comercial.',
            'business_type.required' => 'Selecciona el tipo de comercio.',
            'email.required' => 'Ingresa el correo electrónico.',
            'email.email' => 'Ingresa un correo electrónico válido.',
            'email.unique' => 'Este correo ya está registrado.',
            'phone.required' => 'Ingresa el teléfono o WhatsApp del responsable.',
            'phone.unique' => 'Este teléfono ya está registrado.',
            'business_email.email' => 'Ingresa un correo del comercio válido.',
            'business_address.required' => 'Ingresa la dirección del comercio.',
            'password.required' => 'Crea una contraseña.',
            'password.min' => 'La contraseña debe tener al menos 8 caracteres.',
            'password.confirmed' => 'La confirmación de contraseña no coincide.',
        ]);

        $sellsProducts = $request->boolean('sells_products', true);
        $offersServices = $request->boolean('offers_services', false);

        if (! $sellsProducts && ! $offersServices) {
            return back()
                ->withInput()
                ->withErrors([
                    'sells_products' => 'Selecciona al menos si vendes productos u ofreces servicios.',
                ]);
        }

        CommerceUser::create([
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'] ?? null,
            'name' => trim($validated['first_name'] . ' ' . ($validated['last_name'] ?? '')),

            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'password' => Hash::make($validated['password']),

            'business_name' => $validated['business_name'],
            'business_type' => $validated['business_type'],
            'business_phone' => $validated['business_phone'] ?? $validated['phone'],
            'business_email' => $validated['business_email'] ?? $validated['email'],
            'business_address' => $validated['business_address'],

            'sells_products' => $sellsProducts,
            'offers_services' => $offersServices,
            'has_own_delivery' => $request->boolean('has_own_delivery', false),
            'uses_petpay_delivery' => $request->boolean('uses_petpay_delivery', true),

            'status' => 'pending',
            'approval_status' => 'pending',
            'is_open' => false,
            'commission_percent' => 0,
        ]);

        return redirect()
            ->route('comercio.login')
            ->with('status', 'Tu comercio fue registrado correctamente. Admin revisará tu solicitud para activar tu cuenta.');
    }
}