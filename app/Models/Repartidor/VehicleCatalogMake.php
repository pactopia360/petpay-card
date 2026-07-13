<?php

namespace App\Models\Repartidor;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class VehicleCatalogMake extends Model
{
    protected $connection = 'mysql_repartidor';

    protected $table = 'vehicle_catalog_makes';

    protected $fillable = [
        'vehicle_type',
        'name',
        'slug',
        'is_active',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function models(): HasMany
    {
        return $this->hasMany(
            VehicleCatalogModel::class,
            'vehicle_catalog_make_id'
        );
    }
}
