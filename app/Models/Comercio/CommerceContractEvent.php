<?php

namespace App\Models\Comercio;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CommerceContractEvent extends Model
{
    protected $connection = 'mysql_comercio';

    protected $table = 'commerce_contract_events';

    protected $fillable = [
        'contract_id',
        'commerce_user_id',
        'event_type',
        'actor_type',
        'actor_id',
        'description',
        'metadata',
        'occurred_at',
    ];

    protected $casts = [
        'metadata' => 'array',
        'occurred_at' => 'datetime',
    ];

    public function contract(): BelongsTo
    {
        return $this->belongsTo(CommerceContract::class, 'contract_id');
    }
}