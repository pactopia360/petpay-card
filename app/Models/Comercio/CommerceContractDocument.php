<?php

namespace App\Models\Comercio;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CommerceContractDocument extends Model
{
    protected $connection = 'mysql_comercio';

    protected $table = 'commerce_contract_documents';

    protected $fillable = [
        'contract_id',
        'commerce_user_id',
        'document_type',
        'name',
        'path',
        'mime_type',
        'size_bytes',
        'sha256',
        'is_required',
        'status',
        'review_notes',
    ];

    protected $casts = [
        'is_required' => 'boolean',
    ];

    public function contract(): BelongsTo
    {
        return $this->belongsTo(CommerceContract::class, 'contract_id');
    }
}