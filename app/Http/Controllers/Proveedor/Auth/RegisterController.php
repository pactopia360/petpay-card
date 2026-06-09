<?php

namespace App\Http\Controllers\Proveedor\Auth;

use App\Http\Controllers\Controller;
use App\Models\Proveedor\ProviderUser;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Password;

class RegisterController extends Controller
{
    public function register(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'business_name' => ['required', 'string', 'max:191'],
            'business_type' => ['nullable', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:191', 'unique:mysql_proveedor.provider_users,email'],
            'phone' => ['nullable', 'string', 'max:30'],
            'business_address' => ['nullable', 'string', 'max:255'],
            'password' => ['required', 'confirmed', Password::min(8)],
        ]);

        ProviderUser::create([
            'first_name' => $data['business_name'],
            'last_name' => null,
            'name' => $data['business_name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'business_name' => $data['business_name'],
            'business_type' => $data['business_type'] ?? null,
            'business_phone' => $data['phone'] ?? null,
            'business_email' => $data['email'],
            'business_address' => $data['business_address'] ?? null,
            'password' => $data['password'],
            'status' => 'pending',
            'approval_status' => 'pending',
            'is_open' => false,
            'commission_percent' => 0,
        ]);

        return redirect()
            ->route('proveedor.login')
            ->with('status', 'Registro enviado. Admin debe aprobar tu negocio antes de ingresar.');
    }
}