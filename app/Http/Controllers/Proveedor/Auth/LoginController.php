<?php

namespace App\Http\Controllers\Proveedor\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class LoginController extends Controller
{
    public function showLoginForm(): View
    {
        return view('auth.proveedor.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (! Auth::guard('proveedor')->attempt($credentials, $request->boolean('remember'))) {
            return back()->withInput($request->only('email'))->withErrors([
                'email' => 'Las credenciales no son correctas.',
            ]);
        }

        $request->session()->regenerate();

        $proveedor = Auth::guard('proveedor')->user();

        if (! $proveedor->isActive()) {
            Auth::guard('proveedor')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return back()->withErrors([
                'email' => 'Tu cuenta aún está pendiente de aprobación por Admin.',
            ]);
        }

        $proveedor->forceFill(['last_login_at' => now()])->save();

        return redirect()->route('proveedor.dashboard');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::guard('proveedor')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('proveedor.login');
    }
}