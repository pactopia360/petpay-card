<?php

namespace App\Models\Repartidor;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DriverUpdateRequest extends Model
{
    protected $connection = 'mysql_repartidor';

    protected $table = 'driver_update_requests';

    protected $fillable = [
        'uuid',
        'driver_user_id',
        'identity_profile_id',
        'field_name',
        'current_value',
        'requested_value',
        'reason',
        'evidence_path',
        'evidence_original_name',
        'evidence_mime_type',
        'evidence_sha256',
        'status',
        'admin_notes',
        'reviewed_by',
        'reviewed_at',
        'applied_at',
    ];

    protected function casts(): array
    {
        return [
            'reviewed_at' => 'datetime',
            'applied_at' => 'datetime',
        ];
    }

    public function driver(): BelongsTo
    {
        return $this->belongsTo(DriverUser::class, 'driver_user_id');
    }

    public function profile(): BelongsTo
    {
        return $this->belongsTo(
            DriverIdentityProfile::class,
            'identity_profile_id'
        );
    }

    public function isOpen(): bool
    {
        return in_array(
            $this->status,
            ['pending', 'under_review'],
            true
        );
    }
}
