<?php

namespace App\Http\Controllers\Repartidor\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class LoginController extends Controller
{
    public function showLoginForm(): View
    {
        return view('auth.repartidor.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (! Auth::guard('repartidor')->attempt($credentials, $request->boolean('remember'))) {
            return back()->withInput($request->only('email'))->withErrors([
                'email' => 'Las credenciales no son correctas.',
            ]);
        }

        $request->session()->regenerate();

        $repartidor = Auth::guard('repartidor')->user();

        if (! $repartidor->isActive()) {
            Auth::guard('repartidor')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return back()->withErrors([
                'email' => 'Tu perfil aún está pendiente de aprobación por Admin.',
            ]);
        }

        $repartidor->forceFill(['last_login_at' => now()])->save();

        return redirect()->route('repartidor.dashboard');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::guard('repartidor')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('repartidor.login');
    }
}