<?php

namespace App\Http\Controllers\Comercio\Auth;

use App\Http\Controllers\Controller;
use App\Models\Comercio\CommerceUser;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\AbstractProvider;
use Throwable;

class GoogleController extends Controller
{
    public function redirect(): RedirectResponse
    {
        $provider = $this->googleProvider();

        return $provider->redirect();
    }

    public function callback(): RedirectResponse
    {
        try {
            $googleUser = $this->googleProvider()
                ->stateless()
                ->user();
        } catch (Throwable) {
            return redirect()
                ->route('comercio.login')
                ->withErrors([
                    'email_or_phone' => 'No pudimos iniciar sesión con Google. Intenta nuevamente.',
                ]);
        }

        $email = $googleUser->getEmail();

        if (! $email) {
            return redirect()
                ->route('comercio.login')
                ->withErrors([
                    'email_or_phone' => 'Tu cuenta de Google no compartió un correo electrónico válido.',
                ]);
        }

        $commerce = CommerceUser::query()
            ->where('email', $email)
            ->orWhere('google_id', $googleUser->getId())
            ->first();

        if (! $commerce) {
            $fullName = trim((string) $googleUser->getName());
            [$firstName, $lastName] = $this->splitName($fullName);

            $commerce = CommerceUser::create([
                'first_name' => $firstName ?: 'Comercio',
                'last_name' => $lastName,
                'name' => $fullName ?: $email,
                'email' => $email,
                'phone' => null,

                'password' => Hash::make(Str::random(40)),
                'google_id' => $googleUser->getId(),
                'google_avatar' => $googleUser->getAvatar(),
                'auth_provider' => 'google',
                'email_verified_at' => now(),

                'business_name' => $fullName ?: 'Comercio Petpay',
                'business_type' => null,
                'business_phone' => null,
                'business_email' => $email,
                'business_address' => null,
                'business_latitude' => null,
                'business_longitude' => null,

                'sells_products' => true,
                'offers_services' => false,
                'has_own_delivery' => false,
                'uses_petpay_delivery' => true,

                'approval_status' => 'pending',
                'status' => 'pending',
                'is_open' => false,
                'commission_percent' => 0,
            ]);
        } else {
            $commerce->forceFill([
                'google_id' => $commerce->google_id ?: $googleUser->getId(),
                'google_avatar' => $googleUser->getAvatar() ?: $commerce->google_avatar,
                'auth_provider' => $commerce->auth_provider === 'email' ? 'google' : $commerce->auth_provider,
                'email_verified_at' => $commerce->email_verified_at ?: now(),
            ])->save();
        }

        if (! $commerce->canAccessPortal()) {
            return redirect()
                ->route('comercio.login')
                ->withErrors([
                    'email_or_phone' => $this->inactiveMessage($commerce),
                ]);
        }

        Auth::guard('comercio')->login($commerce, true);

        request()->session()->regenerate();

        $commerce->forceFill([
            'last_login_at' => now(),
        ])->save();

        return redirect()->route('comercio.dashboard');
    }

    private function googleProvider(): AbstractProvider
    {
        config([
            'services.google.redirect' => config('services.google.redirect_comercio'),
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
        $name = trim($name);

        if ($name === '') {
            return ['', null];
        }

        $parts = preg_split('/\s+/', $name) ?: [];

        $firstName = array_shift($parts) ?: $name;
        $lastName = count($parts) > 0 ? implode(' ', $parts) : null;

        return [$firstName, $lastName];
    }

    private function inactiveMessage(CommerceUser $commerce): string
    {
        if ($commerce->isPendingApproval()) {
            return 'Tu cuenta de comercio fue creada con Google y está pendiente de aprobación por el administrador.';
        }

        if ($commerce->isRejected()) {
            return 'Tu cuenta de comercio fue rechazada. Contacta a soporte para más información.';
        }

        if ($commerce->isSuspended()) {
            return 'Tu cuenta de comercio está suspendida. Contacta a soporte.';
        }

        return 'Tu cuenta de comercio no está activa. Contacta a soporte.';
    }
}