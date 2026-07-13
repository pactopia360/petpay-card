<?php

namespace App\Http\Controllers\Repartidor\Auth;

use App\Http\Controllers\Controller;
use App\Models\Repartidor\DriverUser;
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
        $validated = $request->validate([
            'email_or_phone' => ['required', 'string', 'max:255'],
            'password' => ['required', 'string'],
        ], [
            'email_or_phone.required' => 'Ingresa tu correo electrónico o número de teléfono.',
            'password.required' => 'Ingresa tu contraseña.',
        ]);

        $identifier = trim((string) $validated['email_or_phone']);
        $loginField = filter_var($identifier, FILTER_VALIDATE_EMAIL) ? 'email' : 'phone';

        if (! Auth::guard('repartidor')->attempt([
            $loginField => $identifier,
            'password' => $validated['password'],
        ], $request->boolean('remember'))) {
            return back()
                ->withInput($request->only('email_or_phone'))
                ->withErrors(['email_or_phone' => 'Las credenciales no son correctas.']);
        }

        $request->session()->regenerate();

        $driver = Auth::guard('repartidor')->user();

        if (! $driver instanceof DriverUser || ! $driver->canAccessPortal()) {
            Auth::guard('repartidor')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return back()
                ->withInput($request->only('email_or_phone'))
                ->withErrors(['email_or_phone' => $this->inactiveMessage($driver)]);
        }

        $driver->forceFill(['last_login_at' => now()])->save();

        return redirect()->route('repartidor.dashboard');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::guard('repartidor')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('repartidor.login');
    }

    private function inactiveMessage(?DriverUser $driver): string
    {
        if (! $driver) {
            return 'No pudimos validar tu perfil. Intenta nuevamente.';
        }

        if ($driver->isPendingApproval()) {
            return 'Tu perfil aún está pendiente de aprobación por Admin.';
        }

        if ($driver->isRejected()) {
            return 'Tu solicitud fue rechazada. Contacta a soporte para más información.';
        }

        if ($driver->isSuspended()) {
            return 'Tu perfil está suspendido temporalmente. Contacta a soporte.';
        }

        return 'Tu perfil todavía no puede acceder a la plataforma.';
    }
}
