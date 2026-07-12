<?php

namespace App\Models\Comercio;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CommerceIdentityProfile extends Model
{
    protected $connection = 'mysql_comercio';

    protected $table = 'commerce_identity_profiles';

    protected $fillable = [
        'uuid',
        'commerce_user_id',
        'person_type',
        'business_rfc',
        'business_legal_name',
        'representative_name',
        'representative_rfc',
        'representative_curp',
        'representative_email',
        'representative_phone',
        'representative_position',
        'address_line',
        'postal_code',
        'state',
        'municipality',
        'notarial_deed_number',
        'incorporation_date',
        'notary_name',
        'notary_number',
        'legal_powers_scope',
        'powers_declared_current',
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

    protected $casts = [
        'powers_declared_current' => 'boolean',
        'data_processing_consent' => 'boolean',
        'truth_declaration' => 'boolean',
        'incorporation_date' => 'date',
        'submitted_at' => 'datetime',
        'review_started_at' => 'datetime',
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
        'identity_locked_at' => 'datetime',
    ];

    public function commerce(): BelongsTo
    {
        return $this->belongsTo(CommerceUser::class, 'commerce_user_id');
    }

    public function documents(): HasMany
    {
        return $this->hasMany(CommerceIdentityDocument::class, 'identity_profile_id')
            ->latest('id');
    }

    public function events(): HasMany
    {
        return $this->hasMany(CommerceIdentityEvent::class, 'identity_profile_id')
            ->latest('occurred_at');
    }

    public function isApproved(): bool
    {
        return $this->status === 'approved' && $this->approved_at !== null;
    }

    public function requiredDocumentTypes(): array
    {
        if ($this->person_type === 'company') {
            return [
                'ine_front',
                'ine_back',
                'proof_address',
                'tax_certificate',
                'representative_tax_certificate',
                'selfie',
                'liveness',
                'articles_incorporation',
                'power_of_attorney',
            ];
        }

        return [
            'ine_front',
            'ine_back',
            'proof_address',
            'tax_certificate',
            'selfie',
            'liveness',
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

    public function missingRequiredDocumentTypes(): array
    {
        return array_values(array_diff(
            $this->requiredDocumentTypes(),
            $this->approvedRequiredDocumentTypes()
        ));
    }

    public function isReadyForSignature(): bool
    {
        return $this->isApproved()
            && $this->missingRequiredDocumentTypes() === []
            && filled($this->representative_rfc)
            && filled($this->representative_name)
            && $this->identity_locked_at !== null
            && filled($this->identity_hash);
    }
}
