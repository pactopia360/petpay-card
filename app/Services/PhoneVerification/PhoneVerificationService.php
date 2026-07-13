<?php

namespace App\Services\PhoneVerification;

use App\Models\Repartidor\DriverIdentityProfile;
use App\Models\Repartidor\DriverPhoneVerification;
use App\Models\Repartidor\DriverUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use RuntimeException;

class PhoneVerificationService
{
    public function requestCode(
        DriverUser $driver,
        DriverIdentityProfile $profile,
        Request $request,
        string $phone
    ): DriverPhoneVerification {
        $normalizedPhone = $this->normalizeMexicanPhone($phone);
        $phoneHash = hash('sha256', $normalizedPhone);

        $latest = DriverPhoneVerification::query()
            ->where('driver_user_id', $driver->id)
            ->latest('id')
            ->first();

        if (
            $latest !== null
            && $latest->created_at !== null
            && $latest->created_at->gt(now()->subMinute())
        ) {
            throw new RuntimeException(
                'Espera un minuto antes de solicitar otro código.'
            );
        }

        $hourlyCount = DriverPhoneVerification::query()
            ->where('phone_hash', $phoneHash)
            ->where('created_at', '>=', now()->subHour())
            ->count();

        if ($hourlyCount >= 3) {
            throw new RuntimeException(
                'Este número alcanzó el límite de verificaciones por hora.'
            );
        }

        $dailyCount = DriverPhoneVerification::query()
            ->where('driver_user_id', $driver->id)
            ->where('created_at', '>=', now()->startOfDay())
            ->count();

        if ($dailyCount >= 5) {
            throw new RuntimeException(
                'Alcanzaste el límite diario de verificaciones.'
            );
        }

        $code = app()->environment(['local', 'testing'])
            ? '123456'
            : (string) random_int(100000, 999999);

        return DriverPhoneVerification::query()->create([
            'driver_user_id' => $driver->id,
            'identity_profile_id' => $profile->id,
            'target_type' => 'driver',
            'target_id' => $driver->id,
            'phone' => $normalizedPhone,
            'phone_masked' => $this->maskPhone($normalizedPhone),
            'phone_hash' => $phoneHash,
            'channel' => 'voice',
            'provider' => 'fake',
            'provider_reference' => 'fake_'.Str::uuid(),
            'code_hash' => Hash::make($code),
            'status' => 'sent',
            'verification_attempts' => 0,
            'sent_at' => now(),
            'expires_at' => now()->addMinutes(10),
            'ip_address' => $request->ip(),
            'user_agent' => (string) $request->userAgent(),
            'metadata' => [
                'simulated' => true,
                'environment' => app()->environment(),
            ],
        ]);
    }

    public function verifyCode(
        DriverUser $driver,
        DriverPhoneVerification $verification,
        string $code
    ): DriverPhoneVerification {
        if ((int) $verification->driver_user_id !== (int) $driver->id) {
            throw new RuntimeException(
                'La verificación no pertenece al usuario autenticado.'
            );
        }

        if ($verification->status === 'verified') {
            return $verification;
        }

        if ($verification->isLocked()) {
            throw new RuntimeException(
                'La verificación está bloqueada temporalmente.'
            );
        }

        if ($verification->isExpired()) {
            $verification->forceFill([
                'status' => 'expired',
            ])->save();

            throw new RuntimeException(
                'El código expiró. Solicita uno nuevo.'
            );
        }

        $attempts = $verification->verification_attempts + 1;

        if (! Hash::check($code, $verification->code_hash)) {
            $blocked = $attempts >= 5;

            $verification->forceFill([
                'verification_attempts' => $attempts,
                'status' => $blocked ? 'blocked' : 'failed',
                'locked_until' => $blocked
                    ? now()->addMinutes(30)
                    : null,
            ])->save();

            throw new RuntimeException(
                $blocked
                    ? 'Superaste el número permitido de intentos.'
                    : 'El código ingresado no es correcto.'
            );
        }

        $verification->forceFill([
            'status' => 'verified',
            'verification_attempts' => $attempts,
            'verified_at' => now(),
            'locked_until' => null,
        ])->save();

        return $verification->fresh();
    }

    public function normalizeMexicanPhone(string $phone): string
    {
        $digits = preg_replace('/\D+/', '', $phone) ?? '';

        if (str_starts_with($digits, '52') && strlen($digits) === 12) {
            $digits = substr($digits, 2);
        }

        if (strlen($digits) !== 10) {
            throw new RuntimeException(
                'El teléfono debe contener exactamente 10 dígitos.'
            );
        }

        return '+52'.$digits;
    }

    private function maskPhone(string $phone): string
    {
        $digits = preg_replace('/\D+/', '', $phone) ?? '';

        return '+52 ******'.substr($digits, -4);
    }
}
