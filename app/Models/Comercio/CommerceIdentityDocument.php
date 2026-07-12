<?php

namespace App\Models\Comercio;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CommerceIdentityDocument extends Model
{
    protected $connection = 'mysql_comercio';

    protected $table = 'commerce_identity_documents';

    protected $fillable = [
        'identity_profile_id',
        'commerce_user_id',
        'document_type',
        'original_name',
        'path',
        'mime_type',
        'size_bytes',
        'sha256',
        'is_required',
        'status',
        'review_notes',
        'reviewed_at',
        'reviewed_by',
    ];

    protected $casts = [
        'is_required' => 'boolean',
        'reviewed_at' => 'datetime',
    ];

    public function profile(): BelongsTo
    {
        return $this->belongsTo(CommerceIdentityProfile::class, 'identity_profile_id');
    }
}
