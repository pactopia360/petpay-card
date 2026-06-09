<?php

namespace App\Models\Repartidor;

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
        'status',
        'vehicle_type',
        'vehicle_plate',
        'operation_zone',
        'current_latitude',
        'current_longitude',
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
            'is_available' => 'boolean',
            'delivery_commission_percent' => 'decimal:2',
            'email_verified_at' => 'datetime',
            'last_login_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }
}