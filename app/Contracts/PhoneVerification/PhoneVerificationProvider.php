<?php

namespace App\Contracts\PhoneVerification;

interface PhoneVerificationProvider
{
    /**
     * @return array{
     *     provider_reference:string,
     *     status:string,
     *     metadata:array<string,mixed>
     * }
     */
    public function send(
        string $phone,
        string $code,
        string $channel = 'voice'
    ): array;

    public function name(): string;
}
