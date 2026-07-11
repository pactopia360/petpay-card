<?php

namespace App\Models\Comercio;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CommerceCatalogBranchStock extends Model
{
    protected $connection = 'mysql_comercio';

    protected $table = 'commerce_catalog_branch_stocks';

    protected $fillable = [
        'commerce_user_id',
        'branch_id',
        'product_id',
        'variant_id',
        'is_assigned',
        'stock',
        'reserved_stock',
        'minimum_stock',
        'branch_price',
        'branch_sale_price',
        'branch_sale_starts_at',
        'branch_sale_ends_at',
        'max_purchase_quantity',
        'available_days',
        'available_from',
        'available_to',
        'fulfillment_priority',
        'coverage_radius_km',
        'allow_delivery',
        'allow_pickup',
        'is_available',
    ];

    protected function casts(): array
    {
        return [
            'is_assigned' => 'boolean',
            'stock' => 'decimal:3',
            'reserved_stock' => 'decimal:3',
            'minimum_stock' => 'decimal:3',
            'branch_price' => 'decimal:2',
            'branch_sale_price' => 'decimal:2',
            'branch_sale_starts_at' => 'datetime',
            'branch_sale_ends_at' => 'datetime',
            'max_purchase_quantity' => 'decimal:3',
            'available_days' => 'array',
            'available_from' => 'datetime:H:i',
            'available_to' => 'datetime:H:i',
            'fulfillment_priority' => 'integer',
            'coverage_radius_km' => 'decimal:2',
            'allow_delivery' => 'boolean',
            'allow_pickup' => 'boolean',
            'is_available' => 'boolean',
        ];
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(CommerceBranch::class, 'branch_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(CommerceCatalogProduct::class, 'product_id');
    }

    public function variant(): BelongsTo
    {
        return $this->belongsTo(CommerceCatalogProductVariant::class, 'variant_id');
    }

    public function getAvailableStockAttribute(): float
    {
        return max(0, (float) $this->stock - (float) $this->reserved_stock);
    }

    public function getEffectivePriceAttribute(): float
    {
        $now = now();
        $promotionActive = $this->branch_sale_price !== null
            && (! $this->branch_sale_starts_at || $this->branch_sale_starts_at->lte($now))
            && (! $this->branch_sale_ends_at || $this->branch_sale_ends_at->gte($now));

        if ($promotionActive) {
            return (float) $this->branch_sale_price;
        }

        if ($this->branch_price !== null) {
            return (float) $this->branch_price;
        }

        return (float) ($this->product?->effective_price ?? 0);
    }

    public function getCanSellAttribute(): bool
    {
        if (! $this->is_assigned || ! $this->is_available) {
            return false;
        }

        if ($this->product?->item_type === 'service' || ! $this->product?->track_stock) {
            return true;
        }

        return $this->available_stock > 0;
    }

    public function getIsLowStockAttribute(): bool
    {
        return $this->available_stock <= (float) $this->minimum_stock;
    }
}
