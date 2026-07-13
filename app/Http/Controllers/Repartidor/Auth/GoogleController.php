<?php

namespace App\Http\Controllers\Repartidor\Auth;

use App\Http\Controllers\Controller;
use App\Models\Repartidor\DriverUser;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\AbstractProvider;
use Throwable;

class GoogleController extends Controller
{
    public function redirect(Request $request): RedirectResponse
    {
        $intent = $request->query('intent') === 'register' ? 'register' : 'login';
        $request->session()->put('driver_google_intent', $intent);

        return $this->googleProvider()->redirect();
    }

    public function callback(Request $request): RedirectResponse
    {
        $intent = $request->session()->pull('driver_google_intent', 'login');

        try {
            $googleUser = $this->googleProvider()->stateless()->user();
        } catch (Throwable) {
            return redirect()
                ->route($intent === 'register' ? 'repartidor.register' : 'repartidor.login')
                ->withErrors(['google' => 'No pudimos conectar con Google. Intenta nuevamente.']);
        }

        $email = strtolower(trim((string) $googleUser->getEmail()));
        $googleId = trim((string) $googleUser->getId());

        if ($email === '' || $googleId === '') {
            return redirect()
                ->route($intent === 'register' ? 'repartidor.register' : 'repartidor.login')
                ->withErrors(['google' => 'Google no proporcionó los datos necesarios para continuar.']);
        }

        return $intent === 'register'
            ? $this->prepareRegistration($request, $googleUser, $email, $googleId)
            : $this->loginExistingDriver($request, $googleUser, $email, $googleId);
    }

    private function loginExistingDriver(
        Request $request,
        object $googleUser,
        string $email,
        string $googleId
    ): RedirectResponse {
        $driver = DriverUser::query()
            ->where('google_id', $googleId)
            ->orWhere('email', $email)
            ->first();

        if (! $driver) {
            return redirect()
                ->route('repartidor.login')
                ->withErrors([
                    'google' => 'No existe un repartidor registrado con esta cuenta de Google.',
                ]);
        }

        if ($driver->google_id !== null && $driver->google_id !== $googleId) {
            return redirect()
                ->route('repartidor.login')
                ->withErrors([
                    'google' => 'Este correo ya está vinculado con otra cuenta de Google.',
                ]);
        }

        $driver->forceFill([
            'google_id' => $driver->google_id ?: $googleId,
            'google_avatar' => $googleUser->getAvatar() ?: $driver->google_avatar,
            'auth_provider' => 'google',
            'email_verified_at' => $driver->email_verified_at ?: now(),
        ])->save();

        if (! $driver->canAccessPortal()) {
            return redirect()
                ->route('repartidor.login')
                ->withErrors(['google' => $this->inactiveMessage($driver)]);
        }

        Auth::guard('repartidor')->login($driver, true);
        $request->session()->regenerate();

        $driver->forceFill(['last_login_at' => now()])->save();

        return redirect()->route('repartidor.dashboard');
    }

    private function prepareRegistration(
        Request $request,
        object $googleUser,
        string $email,
        string $googleId
    ): RedirectResponse {
        $existingDriver = DriverUser::query()
            ->where('google_id', $googleId)
            ->orWhere('email', $email)
            ->first();

        if ($existingDriver) {
            return redirect()
                ->route('repartidor.login')
                ->withErrors([
                    'google' => 'Esta cuenta de Google ya está registrada. Inicia sesión.',
                ]);
        }

        [$firstName, $lastName] = $this->splitName(trim((string) $googleUser->getName()));

        $request->session()->put('driver_google_registration', [
            'google_id' => $googleId,
            'google_avatar' => $googleUser->getAvatar(),
            'email' => $email,
            'first_name' => $firstName ?: 'Repartidor',
            'last_name' => $lastName,
        ]);

        return redirect()
            ->route('repartidor.register')
            ->with('status', 'Google fue conectado. Completa tus datos para finalizar el registro.');
    }

    private function googleProvider(): AbstractProvider
    {
        config([
            'services.google.redirect' => config('services.google.redirect_repartidor'),
        ]);

        /** @var AbstractProvider $provider */
        $provider = Socialite::driver('google');

        return $provider->setScopes(['openid', 'profile', 'email']);
    }

    private function splitName(string $name): array
    {
        if ($name === '') {
            return ['', null];
        }

        $parts = preg_split('/\s+/', $name) ?: [];
        $firstName = array_shift($parts) ?: $name;
        $lastName = $parts !== [] ? implode(' ', $parts) : null;

        return [$firstName, $lastName];
    }

    private function inactiveMessage(DriverUser $driver): string
    {
        if ($driver->isPendingApproval()) {
            return 'Tu cuenta está pendiente de aprobación por Admin.';
        }

        if ($driver->isRejected()) {
            return 'Tu cuenta fue rechazada. Contacta a soporte.';
        }

        if ($driver->isSuspended()) {
            return 'Tu cuenta está suspendida. Contacta a soporte.';
        }

        return 'Tu cuenta no está activa.';
    }
}
