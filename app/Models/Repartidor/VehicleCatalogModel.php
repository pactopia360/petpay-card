<?php

namespace App\Models\Repartidor;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VehicleCatalogModel extends Model
{
    protected $connection = 'mysql_repartidor';

    protected $table = 'vehicle_catalog_models';

    protected $fillable = [
        'vehicle_catalog_make_id',
        'name',
        'slug',
        'year_from',
        'year_to',
        'is_active',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'year_from' => 'integer',
            'year_to' => 'integer',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function make(): BelongsTo
    {
        return $this->belongsTo(
            VehicleCatalogMake::class,
            'vehicle_catalog_make_id'
        );
    }
}
