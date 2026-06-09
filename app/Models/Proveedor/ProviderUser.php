<?php

namespace App\Models\Proveedor;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class ProviderUser extends Authenticatable
{
    use Notifiable;

    protected $connection = 'mysql_proveedor';

    protected $table = 'provider_users';

    protected $fillable = [
        'first_name',
        'last_name',
        'name',
        'email',
        'phone',
        'password',
        'status',
        'business_name',
        'business_type',
        'business_phone',
        'business_email',
        'business_address',
        'business_latitude',
        'business_longitude',
        'approval_status',
        'is_open',
        'commission_percent',
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
            'business_latitude' => 'decimal:7',
            'business_longitude' => 'decimal:7',
            'is_open' => 'boolean',
            'commission_percent' => 'decimal:2',
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