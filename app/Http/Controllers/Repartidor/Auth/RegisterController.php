<?php

namespace App\Http\Controllers\Repartidor\Auth;

use App\Http\Controllers\Controller;
use App\Models\Repartidor\DriverUser;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Password;

class RegisterController extends Controller
{
    public function register(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'first_name' => ['required', 'string', 'max:120'],
            'last_name' => ['nullable', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:191', 'unique:mysql_repartidor.driver_users,email'],
            'phone' => ['nullable', 'string', 'max:30'],
            'vehicle_type' => ['nullable', 'string', 'max:80'],
            'password' => ['required', 'confirmed', Password::min(8)],
        ]);

        DriverUser::create([
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'] ?? null,
            'name' => trim($data['first_name'] . ' ' . ($data['last_name'] ?? '')),
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'vehicle_type' => $data['vehicle_type'] ?? null,
            'password' => $data['password'],
            'status' => 'pending',
            'approval_status' => 'pending',
            'is_available' => false,
            'delivery_commission_percent' => 0,
        ]);

        return redirect()
            ->route('repartidor.login')
            ->with('status', 'Registro enviado. Admin debe aprobar tu perfil antes de ingresar.');
    }
}