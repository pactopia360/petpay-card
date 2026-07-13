<?php

namespace App\Models\Repartidor;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DriverIdentityEvent extends Model
{
    protected $connection = 'mysql_repartidor';

    protected $table = 'driver_identity_events';

    protected $fillable = [
        'identity_profile_id',
        'driver_user_id',
        'event_type',
        'actor_type',
        'actor_id',
        'description',
        'metadata',
        'ip_address',
        'user_agent',
        'occurred_at',
    ];

    protected function casts(): array
    {
        return [
            'metadata' => 'array',
            'occurred_at' => 'datetime',
        ];
    }

    public function profile(): BelongsTo
    {
        return $this->belongsTo(
            DriverIdentityProfile::class,
            'identity_profile_id'
        );
    }

    public function driver(): BelongsTo
    {
        return $this->belongsTo(
            DriverUser::class,
            'driver_user_id'
        );
    }
}