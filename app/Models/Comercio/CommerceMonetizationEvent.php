<?php

namespace App\Models\Comercio;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CommerceMonetizationEvent extends Model
{
    protected $connection = 'mysql_comercio';

    protected $table = 'commerce_monetization_events';

    protected $fillable = [
        'campaign_id',
        'commerce_user_id',
        'event_type',
        'amount',
        'reference',
        'metadata',
        'occurred_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'metadata' => 'array',
        'occurred_at' => 'datetime',
    ];

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(CommerceMonetizationCampaign::class, 'campaign_id');
    }
}