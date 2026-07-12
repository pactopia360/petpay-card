<?php

namespace App\Models\Comercio;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class CommerceMonetizationCampaign extends Model
{
    use SoftDeletes;

    protected $connection = 'mysql_comercio';

    protected $table = 'commerce_monetization_campaigns';

    protected $fillable = [
        'commerce_user_id',
        'branch_id',
        'name',
        'slug',
        'type',
        'status',
        'scope',
        'budget',
        'spent',
        'discount_value',
        'discount_type',
        'coupon_code',
        'minimum_purchase',
        'usage_limit',
        'usage_count',
        'clicks',
        'impressions',
        'orders',
        'attributed_sales',
        'cashback_percentage',
        'targeting',
        'product_ids',
        'category_ids',
        'description',
        'rejection_reason',
        'starts_at',
        'ends_at',
        'submitted_at',
        'approved_at',
    ];

    protected $casts = [
        'budget' => 'decimal:2',
        'spent' => 'decimal:2',
        'discount_value' => 'decimal:2',
        'minimum_purchase' => 'decimal:2',
        'attributed_sales' => 'decimal:2',
        'cashback_percentage' => 'decimal:4',
        'targeting' => 'array',
        'product_ids' => 'array',
        'category_ids' => 'array',
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'submitted_at' => 'datetime',
        'approved_at' => 'datetime',
    ];

    public function branch(): BelongsTo
    {
        return $this->belongsTo(CommerceBranch::class, 'branch_id');
    }

    public function events(): HasMany
    {
        return $this->hasMany(CommerceMonetizationEvent::class, 'campaign_id');
    }

    public function getRoiAttribute(): float
    {
        $spent = (float) $this->spent;

        return $spent > 0
            ? round((((float) $this->attributed_sales - $spent) / $spent) * 100, 2)
            : 0;
    }

    public function getConversionRateAttribute(): float
    {
        $clicks = (int) $this->clicks;

        return $clicks > 0
            ? round(((int) $this->orders / $clicks) * 100, 2)
            : 0;
    }

    public function getCtrAttribute(): float
    {
        $impressions = (int) $this->impressions;

        return $impressions > 0
            ? round(((int) $this->clicks / $impressions) * 100, 2)
            : 0;
    }
}