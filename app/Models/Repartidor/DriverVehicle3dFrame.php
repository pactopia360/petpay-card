<?php

namespace App\Models\Repartidor;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class DriverVehicle3dFrame extends Model
{
    use SoftDeletes;

    protected $connection = 'mysql_repartidor';

    protected $table = 'driver_vehicle_3d_frames';

    protected $fillable = [
        'uuid',
        'driver_vehicle_3d_job_id',
        'driver_vehicle_id',
        'driver_user_id',
        'sequence',
        'angle_degrees',
        'elevation',
        'path',
        'thumbnail_path',
        'original_name',
        'mime_type',
        'sha256',
        'width',
        'height',
        'size_bytes',
        'blur_score',
        'brightness_score',
        'overlap_score',
        'accepted',
        'rejection_reason',
    ];

    protected function casts(): array
    {
        return [
            'sequence' => 'integer',
            'angle_degrees' => 'decimal:2',
            'width' => 'integer',
            'height' => 'integer',
            'size_bytes' => 'integer',
            'blur_score' => 'decimal:3',
            'brightness_score' => 'decimal:3',
            'overlap_score' => 'decimal:3',
            'accepted' => 'boolean',
        ];
    }

    public function job(): BelongsTo
    {
        return $this->belongsTo(
            DriverVehicle3dJob::class,
            'driver_vehicle_3d_job_id'
        );
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
