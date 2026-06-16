<?php

namespace App\Http\Controllers\Cliente\Auth;

use App\Http\Controllers\Controller;
use App\Models\Cliente\CustomerUser;
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
                ->route('cliente.login')
                ->withErrors([
                    'email_or_phone' => 'No pudimos iniciar sesión con Google. Intenta nuevamente.',
                ]);
        }

        $email = $googleUser->getEmail();

        if (! $email) {
            return redirect()
                ->route('cliente.login')
                ->withErrors([
                    'email_or_phone' => 'Tu cuenta de Google no compartió un correo electrónico válido.',
                ]);
        }

        $customer = CustomerUser::query()
            ->where('email', $email)
            ->orWhere('google_id', $googleUser->getId())
            ->first();

        if (! $customer) {
            $fullName = trim((string) $googleUser->getName());
            [$firstName, $lastName] = $this->splitName($fullName);

            $customer = CustomerUser::create([
                'first_name' => $firstName ?: 'Cliente',
                'last_name' => $lastName,
                'name' => $fullName ?: $email,
                'email' => $email,
                'google_id' => $googleUser->getId(),
                'avatar' => $googleUser->getAvatar(),
                'auth_provider' => 'google',
                'password' => Hash::make(Str::random(40)),
                'status' => 'active',
                'pawpoints_balance' => 0,
                'is_petpay_plus' => false,
                'email_verified_at' => now(),
            ]);
        } else {
            $customer->forceFill([
                'google_id' => $customer->google_id ?: $googleUser->getId(),
                'avatar' => $googleUser->getAvatar() ?: $customer->avatar,
                'auth_provider' => $customer->auth_provider === 'email' ? 'google' : $customer->auth_provider,
                'email_verified_at' => $customer->email_verified_at ?: now(),
            ])->save();
        }

        if (! $customer->isActive()) {
            return redirect()
                ->route('cliente.login')
                ->withErrors([
                    'email_or_phone' => 'Tu cuenta de cliente no está activa. Contacta a soporte.',
                ]);
        }

        Auth::guard('cliente')->login($customer, true);

        request()->session()->regenerate();

        $customer->forceFill([
            'last_login_at' => now(),
        ])->save();

        return redirect()->route('cliente.dashboard');
    }

    private function googleProvider(): AbstractProvider
    {
        config([
            'services.google.redirect' => config('services.google.redirect_cliente'),
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
}