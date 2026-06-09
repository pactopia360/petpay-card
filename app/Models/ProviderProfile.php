<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProviderProfile extends Model
{
    protected $fillable = [
        'user_id',
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
    ];

    protected function casts(): array
    {
        return [
            'business_latitude' => 'decimal:7',
            'business_longitude' => 'decimal:7',
            'is_open' => 'boolean',
            'commission_percent' => 'decimal:2',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}