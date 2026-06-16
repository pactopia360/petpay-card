<?php

namespace App\Models\Cliente;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class CustomerUser extends Authenticatable
{
    use Notifiable;

    protected $connection = 'mysql_cliente';

    protected $table = 'customer_users';

    protected $fillable = [
        'first_name',
        'last_name',
        'name',
        'email',
        'google_id',
        'avatar',
        'auth_provider',
        'phone',
        'password',
        'status',
        'main_address',
        'main_latitude',
        'main_longitude',
        'pawpoints_balance',
        'is_petpay_plus',
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
            'main_latitude' => 'decimal:7',
            'main_longitude' => 'decimal:7',
            'pawpoints_balance' => 'integer',
            'is_petpay_plus' => 'boolean',
            'email_verified_at' => 'datetime',
            'last_login_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function registeredWithGoogle(): bool
    {
        return $this->auth_provider === 'google' && filled($this->google_id);
    }
}