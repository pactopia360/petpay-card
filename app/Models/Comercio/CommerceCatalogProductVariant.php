<?php

namespace App\Models\Comercio;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CommerceCatalogProductVariant extends Model
{
    protected $connection = 'mysql_comercio';

    protected $table = 'commerce_catalog_product_variants';

    protected $fillable = [
        'commerce_user_id',
        'product_id',
        'name',
        'sku',
        'barcode',
        'attributes',
        'price',
        'sale_price',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'attributes' => 'array',
            'price' => 'decimal:2',
            'sale_price' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(CommerceCatalogProduct::class, 'product_id');
    }

    public function stocks(): HasMany
    {
        return $this->hasMany(CommerceCatalogBranchStock::class, 'variant_id');
    }
}
