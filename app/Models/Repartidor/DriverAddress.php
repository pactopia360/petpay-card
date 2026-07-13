<?php

namespace App\Models\Repartidor;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class DriverAddress extends Model
{
    use SoftDeletes;

    protected $connection = 'mysql_repartidor';

    protected $table = 'driver_addresses';

    protected $fillable = [
        'driver_user_id',
        'country',
        'postal_code',
        'state',
        'municipality',
        'city',
        'neighborhood',
        'street',
        'exterior_number',
        'interior_number',
        'references',
        'latitude',
        'longitude',
        'is_primary',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
            'is_primary' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function driver(): BelongsTo
    {
        return $this->belongsTo(
            DriverUser::class,
            'driver_user_id'
        );
    }

    public function fullAddress(): string
    {
        return collect([
            trim(
                (string) $this->street.' '.
                (string) $this->exterior_number.
                ($this->interior_number ? ' Int. '.$this->interior_number : '')
            ),
            $this->neighborhood,
            $this->postal_code ? 'CP '.$this->postal_code : null,
            $this->municipality,
            $this->city,
            $this->state,
            $this->country,
        ])
            ->filter()
            ->implode(', ');
    }
}