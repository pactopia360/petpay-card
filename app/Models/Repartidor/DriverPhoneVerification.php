<?php

namespace App\Models\Repartidor;

use Illuminate\Database\Eloquent\Model;

class DriverPhoneVerification extends Model
{
    protected $connection = 'mysql_repartidor';

    protected $table = 'driver_phone_verifications';

    protected $fillable = [
        'driver_user_id',
        'identity_profile_id',
        'target_type',
        'target_id',
        'phone',
        'phone_masked',
        'phone_hash',
        'channel',
        'provider',
        'provider_reference',
        'code_hash',
        'status',
        'verification_attempts',
        'sent_at',
        'expires_at',
        'verified_at',
        'locked_until',
        'ip_address',
        'user_agent',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'sent_at' => 'datetime',
            'expires_at' => 'datetime',
            'verified_at' => 'datetime',
            'locked_until' => 'datetime',
            'metadata' => 'array',
        ];
    }

    public function isExpired(): bool
    {
        return $this->expires_at !== null
            && $this->expires_at->isPast();
    }

    public function isLocked(): bool
    {
        return $this->locked_until !== null
            && $this->locked_until->isFuture();
    }
}
