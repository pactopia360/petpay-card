<?php

namespace App\Models\Comercio;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CommerceIdentityEvent extends Model
{
    protected $connection = 'mysql_comercio';

    protected $table = 'commerce_identity_events';

    protected $fillable = [
        'identity_profile_id',
        'commerce_user_id',
        'event_type',
        'actor_type',
        'actor_id',
        'description',
        'metadata',
        'ip_address',
        'user_agent',
        'occurred_at',
    ];

    protected $casts = [
        'metadata' => 'array',
        'occurred_at' => 'datetime',
    ];

    public function profile(): BelongsTo
    {
        return $this->belongsTo(CommerceIdentityProfile::class, 'identity_profile_id');
    }
}
