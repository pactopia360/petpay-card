<?php

namespace App\Models\Repartidor;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DriverIdentityProfile extends Model
{
    protected $connection = 'mysql_repartidor';

    protected $table = 'driver_identity_profiles';

    protected $fillable = [
        'uuid',
        'driver_user_id',
        'paternal_last_name',
        'maternal_last_name',
        'curp',
        'home_phone',
        'mobile_phone',
        'contact_email',
        'phone_verified',
        'phone_verified_at',
        'email_verified',
        'contact_email_verified_at',
        'data_processing_consent',
        'truth_declaration',
        'liveness_challenge',
        'liveness_evidence_path',
        'liveness_sha256',
        'status',
        'review_notes',
        'submitted_at',
        'review_started_at',
        'approved_at',
        'approved_by',
        'rejected_at',
        'rejected_by',
        'identity_locked_at',
        'identity_hash',
    ];

    protected function casts(): array
    {
        return [
            'phone_verified' => 'boolean',
            'phone_verified_at' => 'datetime',
            'email_verified' => 'boolean',
            'contact_email_verified_at' => 'datetime',
            'data_processing_consent' => 'boolean',
            'truth_declaration' => 'boolean',
            'submitted_at' => 'datetime',
            'review_started_at' => 'datetime',
            'approved_at' => 'datetime',
            'rejected_at' => 'datetime',
            'identity_locked_at' => 'datetime',
        ];
    }

    public function driver(): BelongsTo
    {
        return $this->belongsTo(DriverUser::class, 'driver_user_id');
    }

    public function documents(): HasMany
    {
        return $this->hasMany(
            DriverIdentityDocument::class,
            'identity_profile_id'
        )->latest('id');
    }

    public function events(): HasMany
    {
        return $this->hasMany(
            DriverIdentityEvent::class,
            'identity_profile_id'
        )->latest('occurred_at');
    }

    public function isApproved(): bool
    {
        return $this->status === 'approved'
            && $this->approved_at !== null;
    }

    public function isLocked(): bool
    {
        return $this->identity_locked_at !== null;
    }

    public function requiredDocumentTypes(): array
    {
        return [
            'ine_front',
            'ine_back',
            'curp',
            'proof_address',
            'selfie',
            'driver_license',
        ];
    }

    public function approvedRequiredDocumentTypes(): array
    {
        return $this->documents
            ->where('status', 'approved')
            ->pluck('document_type')
            ->unique()
            ->values()
            ->all();
    }

    public function uploadedRequiredDocumentTypes(): array
    {
        return $this->documents
            ->whereIn('status', ['pending', 'approved'])
            ->pluck('document_type')
            ->unique()
            ->values()
            ->all();
    }

    public function missingUploadedDocumentTypes(): array
    {
        return array_values(array_diff(
            $this->requiredDocumentTypes(),
            $this->uploadedRequiredDocumentTypes()
        ));
    }

    public function missingApprovedDocumentTypes(): array
    {
        return array_values(array_diff(
            $this->requiredDocumentTypes(),
            $this->approvedRequiredDocumentTypes()
        ));
    }

    public function isReadyForReview(): bool
    {
        return $this->missingUploadedDocumentTypes() === []
            && filled($this->curp)
            && filled($this->mobile_phone)
            && filled($this->contact_email)
            && $this->data_processing_consent
            && $this->truth_declaration;
    }

    public function isReadyForOperation(): bool
    {
        return $this->isApproved()
            && $this->missingApprovedDocumentTypes() === []
            && $this->identity_locked_at !== null
            && filled($this->identity_hash);
    }
}