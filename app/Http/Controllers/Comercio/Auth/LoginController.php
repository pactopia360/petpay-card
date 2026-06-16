<?php

namespace App\Http\Controllers\Comercio\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\View\View;

class LoginController extends Controller
{
    public function showLoginForm(): View
    {
        return view('auth.comercio.login');
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

        $credentials = [
            $loginField => $identifier,
            'password' => (string) $validated['password'],
        ];

        if (! Auth::guard('comercio')->attempt($credentials, $request->boolean('remember'))) {
            return back()
                ->withInput($request->only('email_or_phone'))
                ->withErrors([
                    'email_or_phone' => 'Las credenciales no son correctas.',
                ]);
        }

        $request->session()->regenerate();

        $comercio = Auth::guard('comercio')->user();

        if (! $this->commerceCanAccess($comercio)) {
            Auth::guard('comercio')->logout();

            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return back()
                ->withInput($request->only('email_or_phone'))
                ->withErrors([
                    'email_or_phone' => $this->inactiveMessage($comercio),
                ]);
        }

        if ($comercio instanceof Model) {
            $comercio->forceFill([
                'last_login_at' => now(),
            ])->save();
        }

        return redirect()->route('comercio.dashboard');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::guard('comercio')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('comercio.login');
    }

    private function commerceCanAccess(?Authenticatable $comercio): bool
    {
        if (! $comercio) {
            return false;
        }

        $approvalStatus = Str::lower((string) data_get($comercio, 'approval_status', ''));
        $status = Str::lower((string) data_get($comercio, 'status', ''));

        return $approvalStatus === 'approved' && $status === 'active';
    }

    private function inactiveMessage(?Authenticatable $comercio): string
    {
        if (! $comercio) {
            return 'No pudimos validar tu comercio. Intenta nuevamente.';
        }

        $approvalStatus = Str::lower((string) data_get($comercio, 'approval_status', ''));
        $status = Str::lower((string) data_get($comercio, 'status', ''));

        if ($approvalStatus === 'pending' || $status === 'pending') {
            return 'Tu comercio aún está pendiente de aprobación por Admin.';
        }

        if ($approvalStatus === 'rejected' || $status === 'rejected') {
            return 'Tu solicitud de comercio fue rechazada. Contacta a soporte para más información.';
        }

        if ($approvalStatus === 'suspended' || $status === 'suspended') {
            return 'Tu comercio está suspendido temporalmente. Contacta a soporte.';
        }

        if ($status === 'blocked') {
            return 'Tu comercio está bloqueado. Contacta a soporte.';
        }

        return 'Tu comercio aún no puede acceder a la plataforma.';
    }
}