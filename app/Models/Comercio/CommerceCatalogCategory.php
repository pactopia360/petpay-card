<?php

namespace App\Models\Comercio;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CommerceCatalogCategory extends Model
{
    protected $connection = 'mysql_comercio';

    protected $table = 'commerce_catalog_categories';

    protected $fillable = [
        'commerce_user_id',
        'name',
        'slug',
        'description',
        'sort_order',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function commerce(): BelongsTo
    {
        return $this->belongsTo(CommerceUser::class, 'commerce_user_id');
    }

    public function products(): HasMany
    {
        return $this->hasMany(CommerceCatalogProduct::class, 'category_id');
    }
}
