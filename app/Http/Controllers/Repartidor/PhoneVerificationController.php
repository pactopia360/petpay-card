<?php

namespace App\Http\Controllers\Repartidor;

use App\Http\Controllers\Controller;
use App\Models\Repartidor\DriverIdentityEvent;
use App\Models\Repartidor\DriverIdentityProfile;
use App\Models\Repartidor\DriverPhoneVerification;
use App\Models\Repartidor\DriverUser;
use App\Services\PhoneVerification\PhoneVerificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use RuntimeException;

class PhoneVerificationController extends Controller
{
    public function requestCode(
        Request $request,
        PhoneVerificationService $service
    ): JsonResponse {
        $driver = $this->driver($request);
        $profile = $this->profile($driver);

        $validated = $request->validate([
            'phone' => ['required', 'string', 'max:30'],
        ]);

        try {
            $verification = $service->requestCode(
                driver: $driver,
                profile: $profile,
                request: $request,
                phone: $validated['phone']
            );
        } catch (RuntimeException $exception) {
            return response()->json([
                'ok' => false,
                'message' => $exception->getMessage(),
            ], 422);
        }

        $this->event(
            profile: $profile,
            request: $request,
            eventType: 'phone_verification_requested',
            description: 'Se solicitó una verificación telefónica.',
            metadata: [
                'verification_id' => $verification->id,
                'phone_masked' => $verification->phone_masked,
            ]
        );

        return response()->json([
            'ok' => true,
            'message' => app()->environment(['local', 'testing'])
                ? 'Código local generado. Usa 123456.'
                : 'Código enviado correctamente.',
            'verification_id' => $verification->id,
            'phone_masked' => $verification->phone_masked,
            'development_code' => app()->environment(['local', 'testing'])
                ? '123456'
                : null,
        ]);
    }

    public function verifyCode(
        Request $request,
        DriverPhoneVerification $verification,
        PhoneVerificationService $service
    ): JsonResponse {
        $driver = $this->driver($request);
        $profile = $this->profile($driver);

        $validated = $request->validate([
            'code' => ['required', 'digits:6'],
        ]);

        try {
            $verification = $service->verifyCode(
                driver: $driver,
                verification: $verification,
                code: $validated['code']
            );
        } catch (RuntimeException $exception) {
            return response()->json([
                'ok' => false,
                'message' => $exception->getMessage(),
            ], 422);
        }

        $profile->forceFill([
            'mobile_phone' => $verification->phone,
            'phone_verified' => true,
            'phone_verified_at' => now(),
        ])->save();

        $driver->forceFill([
            'phone' => $verification->phone,
        ])->save();

        $this->event(
            profile: $profile,
            request: $request,
            eventType: 'phone_verified',
            description: 'El teléfono del repartidor fue verificado.',
            metadata: [
                'verification_id' => $verification->id,
                'phone_masked' => $verification->phone_masked,
            ]
        );

        return response()->json([
            'ok' => true,
            'message' => 'Teléfono verificado correctamente.',
            'phone_masked' => $verification->phone_masked,
        ]);
    }

    private function driver(Request $request): DriverUser
    {
        $driver = $request->user('repartidor');

        abort_unless($driver instanceof DriverUser, 401);

        return $driver;
    }

    private function profile(DriverUser $driver): DriverIdentityProfile
    {
        return DriverIdentityProfile::query()->firstOrCreate(
            [
                'driver_user_id' => $driver->id,
            ],
            [
                'uuid' => (string) Str::uuid(),
                'paternal_last_name' => $driver->last_name,
                'mobile_phone' => $driver->phone,
                'contact_email' => $driver->email,
                'status' => 'draft',
            ]
        );
    }

    private function event(
        DriverIdentityProfile $profile,
        Request $request,
        string $eventType,
        string $description,
        array $metadata = []
    ): void {
        DriverIdentityEvent::query()->create([
            'identity_profile_id' => $profile->id,
            'driver_user_id' => $profile->driver_user_id,
            'event_type' => $eventType,
            'actor_type' => 'driver',
            'actor_id' => $profile->driver_user_id,
            'description' => $description,
            'metadata' => $metadata,
            'ip_address' => $request->ip(),
            'user_agent' => (string) $request->userAgent(),
            'occurred_at' => now(),
        ]);
    }
}
