<?php

namespace App\Models\Repartidor;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DriverIdentityDocument extends Model
{
    protected $connection = 'mysql_repartidor';

    protected $table = 'driver_identity_documents';

    protected $fillable = [
        'identity_profile_id',
        'driver_user_id',
        'document_type',
        'original_name',
        'path',
        'mime_type',
        'size_bytes',
        'sha256',
        'is_required',
        'status',
        'analysis_status',
        'analysis_provider',
        'analysis_model',
        'ai_response_id',
        'detected_document_type',
        'analysis_confidence',
        'quality_score',
        'image_width',
        'image_height',
        'face_count',
        'face_detected',
        'extracted_data',
        'validation_results',
        'analysis_warnings',
        'analysis_error',
        'analyzed_at',
        'review_notes',
        'reviewed_at',
        'reviewed_by',
    ];

    protected function casts(): array
    {
        return [
            'is_required' => 'boolean',
            'face_detected' => 'boolean',
            'analysis_confidence' => 'decimal:2',
            'quality_score' => 'decimal:2',
            'extracted_data' => 'array',
            'validation_results' => 'array',
            'analysis_warnings' => 'array',
            'analyzed_at' => 'datetime',
            'reviewed_at' => 'datetime',
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

    public function analysisCompleted(): bool
    {
        return $this->analysis_status === 'completed';
    }

    public function analysisFailed(): bool
    {
        return $this->analysis_status === 'failed';
    }

    public function requiresManualReview(): bool
    {
        return $this->analysis_status === 'manual_review'
            || (float) ($this->analysis_confidence ?? 0) < 70;
    }
}