<?php

namespace App\Models\Comercio;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CommerceCatalogProduct extends Model
{
    protected $connection = 'mysql_comercio';

    protected $table = 'commerce_catalog_products';

    protected $fillable = [
        'commerce_user_id',
        'category_id',
        'brand_id',
        'item_type',
        'name',
        'slug',
        'sku',
        'barcode',
        'short_description',
        'description',
        'price',
        'cost',
        'sale_price',
        'sale_starts_at',
        'sale_ends_at',
        'unit',
        'supplier_name',
        'tags',
        'weight',
        'length',
        'width',
        'height',
        'image_path',
        'track_stock',
        'is_visible',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'cost' => 'decimal:2',
            'sale_price' => 'decimal:2',
            'sale_starts_at' => 'datetime',
            'sale_ends_at' => 'datetime',
            'tags' => 'array',
            'weight' => 'decimal:3',
            'length' => 'decimal:2',
            'width' => 'decimal:2',
            'height' => 'decimal:2',
            'track_stock' => 'boolean',
            'is_visible' => 'boolean',
        ];
    }

    public function commerce(): BelongsTo
    {
        return $this->belongsTo(CommerceUser::class, 'commerce_user_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(CommerceCatalogCategory::class, 'category_id');
    }

    public function brand(): BelongsTo
    {
        return $this->belongsTo(CommerceCatalogBrand::class, 'brand_id');
    }

    public function variants(): HasMany
    {
        return $this->hasMany(CommerceCatalogProductVariant::class, 'product_id');
    }

    public function stocks(): HasMany
    {
        return $this->hasMany(CommerceCatalogBranchStock::class, 'product_id');
    }

    public function getEffectivePriceAttribute(): float
    {
        $now = now();
        $promotionActive = $this->sale_price !== null
            && (! $this->sale_starts_at || $this->sale_starts_at->lte($now))
            && (! $this->sale_ends_at || $this->sale_ends_at->gte($now));

        return (float) ($promotionActive ? $this->sale_price : $this->price);
    }

    public function getMarginPercentageAttribute(): float
    {
        $price = (float) $this->effective_price;
        $cost = (float) $this->cost;

        if ($price <= 0) {
            return 0;
        }

        return round((($price - $cost) / $price) * 100, 2);
    }
}
