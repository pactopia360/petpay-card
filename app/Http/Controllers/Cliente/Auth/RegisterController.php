<?php

namespace App\Http\Controllers\Cliente\Auth;

use App\Http\Controllers\Controller;
use App\Models\Cliente\CustomerUser;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class RegisterController extends Controller
{
    public function register(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'first_name' => ['required', 'string', 'max:120'],
            'last_name' => ['nullable', 'string', 'max:120'],

            'email' => [
                'required',
                'email',
                'max:191',
                Rule::unique('mysql_cliente.customer_users', 'email'),
            ],

            'phone' => [
                'required',
                'string',
                'max:30',
                Rule::unique('mysql_cliente.customer_users', 'phone'),
            ],

            'main_address' => ['nullable', 'string', 'max:255'],

            'password' => ['required', 'confirmed', Password::min(8)],
        ], [
            'first_name.required' => 'Ingresa tu nombre.',
            'email.required' => 'Ingresa tu correo electrónico.',
            'email.email' => 'Ingresa un correo electrónico válido.',
            'email.unique' => 'Este correo ya está registrado.',
            'phone.required' => 'Ingresa tu teléfono.',
            'phone.unique' => 'Este teléfono ya está registrado.',
            'password.required' => 'Crea una contraseña.',
            'password.confirmed' => 'La confirmación de contraseña no coincide.',
        ]);

        $cliente = CustomerUser::create([
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'] ?? null,
            'name' => trim($data['first_name'] . ' ' . ($data['last_name'] ?? '')),
            'email' => $data['email'],
            'phone' => $data['phone'],
            'main_address' => $data['main_address'] ?? null,
            'password' => $data['password'],
            'status' => 'active',
            'pawpoints_balance' => 0,
            'is_petpay_plus' => false,
            'auth_provider' => 'email',
        ]);

        Auth::guard('cliente')->login($cliente);

        $request->session()->regenerate();

        return redirect()->route('cliente.dashboard');
    }
}