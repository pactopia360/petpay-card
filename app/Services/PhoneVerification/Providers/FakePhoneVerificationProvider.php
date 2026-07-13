<?php

namespace App\Services\PhoneVerification\Providers;

use App\Contracts\PhoneVerification\PhoneVerificationProvider;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class FakePhoneVerificationProvider implements PhoneVerificationProvider
{
    public function send(
        string $phone,
        string $code,
        string $channel = 'voice'
    ): array {
        $reference = 'fake_'.Str::uuid()->toString();

        Log::info('PETPAY phone verification fake code', [
            'phone' => $this->maskPhone($phone),
            'code' => $code,
            'channel' => $channel,
            'provider_reference' => $reference,
        ]);

        return [
            'provider_reference' => $reference,
            'status' => 'sent',
            'metadata' => [
                'environment' => app()->environment(),
                'simulated' => true,
            ],
        ];
    }

    public function name(): string
    {
        return 'fake';
    }

    private function maskPhone(string $phone): string
    {
        $digits = preg_replace('/\D+/', '', $phone) ?? '';

        if (strlen($digits) < 4) {
            return '****';
        }

        return '******'.substr($digits, -4);
    }
}
