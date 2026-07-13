<?php

namespace App\Models\Repartidor;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class DriverUser extends Authenticatable
{
    use Notifiable;

    protected $connection = 'mysql_repartidor';

    protected $table = 'driver_users';

    protected $fillable = [
        'first_name',
        'last_name',
        'name',
        'email',
        'phone',
        'password',
        'google_id',
        'google_avatar',
        'auth_provider',
        'status',
        'vehicle_type',
        'vehicle_make',
        'vehicle_model',
        'vehicle_plate',
        'license_number',
        'operation_zone',
        'state',
        'city',
        'availability_type',
        'whatsapp_enabled',
        'terms_accepted_at',
        'current_latitude',
        'current_longitude',
        'registration_latitude',
        'registration_longitude',
        'registration_accuracy_meters',
        'registration_location_source',
        'registration_address_detected',
        'registration_location_captured_at',
        'registration_ip',
        'registration_user_agent',
        'terms_version',
        'privacy_version',
        'privacy_accepted_at',
        'approval_status',
        'is_available',
        'delivery_commission_percent',
        'email_verified_at',
        'last_login_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'current_latitude' => 'decimal:7',
            'current_longitude' => 'decimal:7',
            'registration_latitude' => 'decimal:7',
            'registration_longitude' => 'decimal:7',
            'registration_accuracy_meters' => 'decimal:2',
            'registration_location_captured_at' => 'datetime',
            'privacy_accepted_at' => 'datetime',
            'is_available' => 'boolean',
            'whatsapp_enabled' => 'boolean',
            'delivery_commission_percent' => 'decimal:2',
            'terms_accepted_at' => 'datetime',
            'email_verified_at' => 'datetime',
            'last_login_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function vehicles(): HasMany
    {
        return $this->hasMany(
            DriverVehicle::class,
            'driver_user_id'
        )->latest('is_primary')->latest('id');
    }

    public function primaryVehicle(): HasOne
    {
        return $this->hasOne(
            DriverVehicle::class,
            'driver_user_id'
        )
            ->where('is_primary', true)
            ->latestOfMany();
    }
    public function identityProfile(): HasOne
    {
        return $this->hasOne(
            DriverIdentityProfile::class,
            'driver_user_id'
        );
    }

    public function addresses(): HasMany
    {
        return $this->hasMany(
            DriverAddress::class,
            'driver_user_id'
        )->latest('is_primary')->latest('id');
    }

    public function primaryAddress(): HasOne
    {
        return $this->hasOne(
            DriverAddress::class,
            'driver_user_id'
        )
            ->where('is_primary', true)
            ->where('is_active', true)
            ->latestOfMany();
    }

    public function emergencyContacts(): HasMany
    {
        return $this->hasMany(
            DriverEmergencyContact::class,
            'driver_user_id'
        )->orderBy('position');
    }

    public function emergencyContact(): HasOne
    {
        return $this->hasOne(
            DriverEmergencyContact::class,
            'driver_user_id'
        )
            ->where('position', 1)
            ->latestOfMany();
    }

    public function personalReferences(): HasMany
    {
        return $this->hasMany(
            DriverPersonalReference::class,
            'driver_user_id'
        )->orderBy('position');
    }

    public function identityDocuments(): HasMany
    {
        return $this->hasMany(
            DriverIdentityDocument::class,
            'driver_user_id'
        )->latest('id');
    }

    public function isApproved(): bool
    {
        return $this->approval_status === 'approved';
    }

    public function isPendingApproval(): bool
    {
        return $this->approval_status === 'pending'
            || $this->status === 'pending';
    }

    public function isRejected(): bool
    {
        return $this->approval_status === 'rejected'
            || $this->status === 'rejected';
    }

    public function isSuspended(): bool
    {
        return $this->approval_status === 'suspended'
            || $this->status === 'suspended';
    }

    public function isActive(): bool
    {
        return $this->status === 'active'
            && $this->isApproved();
    }

    public function canAccessPortal(): bool
    {
        return $this->isActive();
    }
}

