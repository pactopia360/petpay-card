<?php

namespace App\Models\Repartidor;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class DriverVehiclePhoto extends Model
{
    use SoftDeletes;

    protected $connection = 'mysql_repartidor';

    protected $table = 'driver_vehicle_photos';

    protected $fillable = [
        'uuid',
        'driver_vehicle_id',
        'driver_user_id',
        'position',
        'path',
        'thumbnail_path',
        'original_name',
        'original_mime_type',
        'mime_type',
        'sha256',
        'width',
        'height',
        'size_bytes',
        'sequence',
        'is_plate_visible',
        'is_primary',
        'status',
        'review_notes',
        'reviewed_at',
        'reviewed_by',
    ];

    protected function casts(): array
    {
        return [
            'width' => 'integer',
            'height' => 'integer',
            'size_bytes' => 'integer',
            'sequence' => 'integer',
            'is_plate_visible' => 'boolean',
            'is_primary' => 'boolean',
            'reviewed_at' => 'datetime',
        ];
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(
            DriverVehicle::class,
            'driver_vehicle_id'
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
