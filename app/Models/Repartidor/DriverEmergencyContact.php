<?php

namespace App\Models\Repartidor;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DriverEmergencyContact extends Model
{
    protected $connection = 'mysql_repartidor';

    protected $table = 'driver_emergency_contacts';

    protected $fillable = [
        'driver_user_id',
        'position',
        'full_name',
        'relationship',
        'relationship_code',
        'lives_same_address',
        'phone',
        'phone_normalized',
        'phone_hash',
        'alternate_phone',
        'alternate_phone_normalized',
        'email',
        'contact_consent',
        'preferred_contact_time',
        'is_verified',
        'verification_status',
        'verification_attempts',
        'verified_at',
        'last_verification_at',
        'risk_status',
    ];

    protected function casts(): array
    {
        return [
            'position' => 'integer',
            'lives_same_address' => 'boolean',
            'contact_consent' => 'boolean',
            'is_verified' => 'boolean',
            'verification_attempts' => 'integer',
            'verified_at' => 'datetime',
            'last_verification_at' => 'datetime',
        ];
    }

    public function driver(): BelongsTo
    {
        return $this->belongsTo(
            DriverUser::class,
            'driver_user_id'
        );
    }
}
