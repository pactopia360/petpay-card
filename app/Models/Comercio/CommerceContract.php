<?php

namespace App\Models\Comercio;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class CommerceContract extends Model
{
    use SoftDeletes;

    protected $connection = 'mysql_comercio';

    protected $table = 'commerce_contracts';

    protected $fillable = [
        'uuid',
        'commerce_user_id',
        'branch_id',
        'template_key',
        'group_key',
        'is_required',
        'title',
        'contract_type',
        'version',
        'document_year',
        'sort_order',
        'status',
        'representative_name',
        'representative_email',
        'representative_position',
        'signature_method',
        'signature_image_path',
        'camera_evidence_path',
        'certificate_rfc',
        'certificate_serial',
        'certificate_subject',
        'certificate_valid_from',
        'certificate_valid_to',
        'cryptographic_signature',
        'signature_metadata',
        'original_path',
        'signed_path',
        'content_html',
        'content_hash',
        'notes',
        'rejection_reason',
        'effective_from',
        'effective_to',
        'submitted_at',
        'signed_at',
        'signed_ip',
        'signed_user_agent',
    ];

    protected $casts = [
        'is_required' => 'boolean',
        'signature_metadata' => 'array',
        'certificate_valid_from' => 'datetime',
        'certificate_valid_to' => 'datetime',
        'document_year' => 'integer',
        'sort_order' => 'integer',
        'effective_from' => 'date',
        'effective_to' => 'date',
        'submitted_at' => 'datetime',
        'signed_at' => 'datetime',
    ];

    public function branch(): BelongsTo
    {
        return $this->belongsTo(CommerceBranch::class, 'branch_id');
    }

    public function documents(): HasMany
    {
        return $this->hasMany(CommerceContractDocument::class, 'contract_id');
    }

    public function events(): HasMany
    {
        return $this->hasMany(CommerceContractEvent::class, 'contract_id')->latest('occurred_at');
    }
}