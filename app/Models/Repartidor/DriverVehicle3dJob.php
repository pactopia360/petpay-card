<?php

namespace App\Models\Repartidor;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class DriverVehicle3dJob extends Model
{
    use SoftDeletes;

    protected $connection = 'mysql_repartidor';

    protected $table = 'driver_vehicle_3d_jobs';

    protected $fillable = [
        'uuid',
        'driver_vehicle_id',
        'driver_user_id',
        'source_type',
        'status',
        'engine',
        'progress',
        'required_frames',
        'captured_frames',
        'quality_score',
        'model_glb_path',
        'model_glb_sha256',
        'model_glb_size',
        'poster_path',
        'map_icon_path',
        'error_message',
        'quality_report',
        'metadata',
        'capture_completed_at',
        'processing_started_at',
        'processing_completed_at',
        'approved_at',
        'approved_by',
    ];

    protected function casts(): array
    {
        return [
            'progress' => 'integer',
            'required_frames' => 'integer',
            'captured_frames' => 'integer',
            'quality_score' => 'decimal:2',
            'model_glb_size' => 'integer',
            'quality_report' => 'array',
            'metadata' => 'array',
            'capture_completed_at' => 'datetime',
            'processing_started_at' => 'datetime',
            'processing_completed_at' => 'datetime',
            'approved_at' => 'datetime',
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

    public function frames(): HasMany
    {
        return $this->hasMany(
            DriverVehicle3dFrame::class,
            'driver_vehicle_3d_job_id'
        )->orderBy('sequence');
    }

    public function isReady(): bool
    {
        return $this->status === 'ready'
            && filled($this->model_glb_path);
    }

    public function capturePercentage(): int
    {
        if ($this->required_frames < 1) {
            return 0;
        }

        return min(
            100,
            (int) round(
                ($this->captured_frames / $this->required_frames)
                * 100
            )
        );
    }
}
