<?php

namespace App\Models\Repartidor;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DriverPersonalReference extends Model
{
    protected $connection = 'mysql_repartidor';

    protected $table = 'driver_personal_references';

    protected $fillable = [
        'driver_user_id',
        'position',
        'full_name',
        'relationship',
        'phone',
        'alternate_phone',
        'email',
        'is_verified',
        'verified_at',
    ];

    protected function casts(): array
    {
        return [
            'position' => 'integer',
            'is_verified' => 'boolean',
            'verified_at' => 'datetime',
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