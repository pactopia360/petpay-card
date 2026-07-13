<?php

namespace App\Models\Repartidor;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class DriverVehicle extends Model
{
    use SoftDeletes;

    protected $connection = 'mysql_repartidor';

    protected $table = 'driver_vehicles';

    protected $fillable = [
        'uuid',
        'driver_user_id',
        'vehicle_code',
        'vehicle_type',
        'alias',
        'make',
        'model',
        'year',
        'color',
        'color_scale',
        'plates',
        'is_primary',
        'insurer',
        'policy_number',
        'coverage_type',
        'insurance_status',
        'insurance_starts_at',
        'insurance_expires_at',
        'insurance_cost',
        'assistance_phone',
        'policy_path',
        'policy_original_name',
        'policy_mime_type',
        'policy_sha256',
        'receipt_path',
        'receipt_original_name',
        'receipt_mime_type',
        'receipt_sha256',
        'expiration_alert_days',
        'internal_notes',
        'status',
        'review_notes',
        'submitted_at',
        'reviewed_at',
        'reviewed_by',
    ];

    protected function casts(): array
    {
        return [
            'year' => 'integer',
            'is_primary' => 'boolean',
            'insurance_starts_at' => 'date',
            'insurance_expires_at' => 'date',
            'insurance_cost' => 'decimal:2',
            'expiration_alert_days' => 'integer',
            'submitted_at' => 'datetime',
            'reviewed_at' => 'datetime',
        ];
    }

    public function driver(): BelongsTo
    {
        return $this->belongsTo(DriverUser::class, 'driver_user_id');
    }

    public function isLocked(): bool
    {
        return in_array(
            $this->status,
            ['submitted', 'under_review', 'approved'],
            true
        );
    }

    public function photos(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(
            DriverVehiclePhoto::class,
            'driver_vehicle_id'
        )->orderBy('sequence');
    }

    public function primaryPhoto(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(
            DriverVehiclePhoto::class,
            'driver_vehicle_id'
        )->where('is_primary', true);
    }

    public function reconstructionJobs(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(
            DriverVehicle3dJob::class,
            'driver_vehicle_id'
        )->latest('id');
    }

    public function latestReconstructionJob(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(
            DriverVehicle3dJob::class,
            'driver_vehicle_id'
        )->latestOfMany();
    }
}


