<?php

namespace App\Http\Controllers\Comercio\Auth;

use App\Http\Controllers\Controller;
use App\Models\Comercio\CommerceUser;
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
        $intent = $request->query('intent') === 'register'
            ? 'register'
            : 'login';

        $request->session()->put('commerce_google_intent', $intent);

        return $this->googleProvider()->redirect();
    }

    public function callback(Request $request): RedirectResponse
    {
        $intent = $request->session()->pull(
            'commerce_google_intent',
            'login'
        );

        try {
            $googleUser = $this->googleProvider()
                ->stateless()
                ->user();
        } catch (Throwable) {
            return redirect()
                ->route(
                    $intent === 'register'
                        ? 'comercio.register'
                        : 'comercio.login'
                )
                ->withErrors([
                    'google' => 'No pudimos conectar con Google. Intenta nuevamente.',
                ]);
        }

        $email = strtolower(trim((string) $googleUser->getEmail()));
        $googleId = trim((string) $googleUser->getId());

        if ($email === '' || $googleId === '') {
            return redirect()
                ->route(
                    $intent === 'register'
                        ? 'comercio.register'
                        : 'comercio.login'
                )
                ->withErrors([
                    'google' => 'Google no proporcionó los datos necesarios para continuar.',
                ]);
        }

        if ($intent === 'register') {
            return $this->prepareRegistration(
                $request,
                $googleUser,
                $email,
                $googleId
            );
        }

        return $this->loginExistingCommerce(
            $request,
            $googleUser,
            $email,
            $googleId
        );
    }

    private function loginExistingCommerce(
        Request $request,
        object $googleUser,
        string $email,
        string $googleId
    ): RedirectResponse {
        $commerce = CommerceUser::query()
            ->where('google_id', $googleId)
            ->orWhere('email', $email)
            ->first();

        if (! $commerce) {
            return redirect()
                ->route('comercio.login')
                ->withErrors([
                    'google' => 'No existe un comercio registrado con esta cuenta de Google. Registra primero tu comercio.',
                ]);
        }

        if (
            $commerce->google_id !== null &&
            $commerce->google_id !== $googleId
        ) {
            return redirect()
                ->route('comercio.login')
                ->withErrors([
                    'google' => 'Este correo ya está vinculado con otra cuenta de Google.',
                ]);
        }

        $commerce->forceFill([
            'google_id' => $commerce->google_id ?: $googleId,
            'google_avatar' => $googleUser->getAvatar()
                ?: $commerce->google_avatar,
            'auth_provider' => 'google',
            'email_verified_at' => $commerce->email_verified_at ?: now(),
        ])->save();

        if (! $commerce->canAccessPortal()) {
            return redirect()
                ->route('comercio.login')
                ->withErrors([
                    'google' => $this->inactiveMessage($commerce),
                ]);
        }

        Auth::guard('comercio')->login($commerce, true);

        $request->session()->regenerate();

        $commerce->forceFill([
            'last_login_at' => now(),
        ])->save();

        return redirect()->route('comercio.dashboard');
    }

    private function prepareRegistration(
        Request $request,
        object $googleUser,
        string $email,
        string $googleId
    ): RedirectResponse {
        $existingCommerce = CommerceUser::query()
            ->where('google_id', $googleId)
            ->orWhere('email', $email)
            ->first();

        if ($existingCommerce) {
            return redirect()
                ->route('comercio.login')
                ->withErrors([
                    'google' => 'Esta cuenta de Google ya está registrada. Inicia sesión desde el acceso de comercios.',
                ]);
        }

        [$firstName, $lastName] = $this->splitName(
            trim((string) $googleUser->getName())
        );

        $request->session()->put('commerce_google_registration', [
            'google_id' => $googleId,
            'google_avatar' => $googleUser->getAvatar(),
            'email' => $email,
            'first_name' => $firstName ?: 'Comercio',
            'last_name' => $lastName,
        ]);

        return redirect()
            ->route('comercio.register')
            ->with(
                'status',
                'Google fue conectado. Completa los datos de tu comercio para finalizar el registro.'
            );
    }

    private function googleProvider(): AbstractProvider
    {
        config([
            'services.google.redirect' => config(
                'services.google.redirect_comercio'
            ),
        ]);

        /** @var AbstractProvider $provider */
        $provider = Socialite::driver('google');

        return $provider->setScopes([
            'openid',
            'profile',
            'email',
        ]);
    }

    private function splitName(string $name): array
    {
        if ($name === '') {
            return ['', null];
        }

        $parts = preg_split('/\s+/', $name) ?: [];

        $firstName = array_shift($parts) ?: $name;
        $lastName = count($parts) > 0
            ? implode(' ', $parts)
            : null;

        return [$firstName, $lastName];
    }

    private function inactiveMessage(CommerceUser $commerce): string
    {
        if ($commerce->isPendingApproval()) {
            return 'Tu cuenta está pendiente de aprobación por el administrador.';
        }

        if ($commerce->isRejected()) {
            return 'Tu cuenta fue rechazada. Contacta a soporte para más información.';
        }

        if ($commerce->isSuspended()) {
            return 'Tu cuenta está suspendida. Contacta a soporte.';
        }

        return 'Tu cuenta de comercio no está activa. Contacta a soporte.';
    }
}
