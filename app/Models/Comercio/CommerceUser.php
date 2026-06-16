<?php

namespace App\Models\Comercio;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class CommerceUser extends Authenticatable
{
    use HasFactory;
    use Notifiable;

    protected $connection = 'mysql_comercio';

    protected $table = 'commerce_users';

    protected $fillable = [
        'first_name',
        'last_name',
        'name',
        'email',
        'phone',
        'password',

        'business_name',
        'business_type',
        'business_phone',
        'business_email',
        'business_address',
        'business_latitude',
        'business_longitude',

        'sells_products',
        'offers_services',
        'has_own_delivery',
        'uses_petpay_delivery',

        'approval_status',
        'status',
        'is_open',
        'commission_percent',

        'approved_at',
        'approved_by',
        'rejection_reason',

        'last_login_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_login_at' => 'datetime',
            'approved_at' => 'datetime',

            'password' => 'hashed',

            'business_latitude' => 'decimal:8',
            'business_longitude' => 'decimal:8',
            'commission_percent' => 'decimal:2',

            'sells_products' => 'boolean',
            'offers_services' => 'boolean',
            'has_own_delivery' => 'boolean',
            'uses_petpay_delivery' => 'boolean',
            'is_open' => 'boolean',
        ];
    }

    public function isApproved(): bool
    {
        return $this->approval_status === 'approved';
    }

    public function isActive(): bool
    {
        return $this->status === 'active' && $this->isApproved();
    }

    public function isPendingApproval(): bool
    {
        return $this->approval_status === 'pending' || $this->status === 'pending';
    }

    public function isRejected(): bool
    {
        return $this->approval_status === 'rejected' || $this->status === 'rejected';
    }

    public function isSuspended(): bool
    {
        return $this->approval_status === 'suspended' || $this->status === 'suspended';
    }

    public function canAccessPortal(): bool
    {
        return $this->isActive();
    }
}