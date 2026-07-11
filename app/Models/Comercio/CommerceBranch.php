<?php

namespace App\Models\Comercio;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CommerceBranch extends Model
{
    use HasFactory;

    protected $connection = 'mysql_comercio';

    protected $table = 'commerce_branches';

    protected $fillable = [
        'commerce_user_id',

        'chain_name',
        'branch_name',
        'branch_code',

        'google_coordinates',
        'latitude',
        'longitude',

        'street',
        'neighborhood',
        'postal_code',
        'state',

        'phone',
        'email',
        'website',
        'whatsapp_phone',

        'service_days',
        'service_open_time',
        'service_close_time',

        'phone_verified',
        'email_verified',

        'is_open',
        'delivery_radius_km',
        'preparation_minutes',
        'missing_fields',
        'status_flag',
    ];

    protected $casts = [
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'service_days' => 'array',
        'service_open_time' => 'datetime:H:i',
        'service_close_time' => 'datetime:H:i',
        'phone_verified' => 'boolean',
        'email_verified' => 'boolean',
        'is_open' => 'boolean',
        'delivery_radius_km' => 'decimal:2',
        'preparation_minutes' => 'integer',
        'missing_fields' => 'array',
    ];

    public function commerce(): BelongsTo
    {
        return $this->belongsTo(CommerceUser::class, 'commerce_user_id');
    }

    public function catalogStocks(): HasMany
    {
        return $this->hasMany(CommerceCatalogBranchStock::class, 'branch_id');
    }

    public function getFullAddressAttribute(): string
    {
        return collect([
            $this->street,
            $this->neighborhood,
            $this->postal_code ? 'CP ' . $this->postal_code : null,
            $this->state,
        ])->filter()->implode(', ');
    }

    public function getServiceScheduleAttribute(): string
    {
        $days = collect($this->service_days ?? [])
            ->filter()
            ->implode(', ');

        if (! $days && ! $this->service_open_time && ! $this->service_close_time) {
            return '';
        }

        $open = $this->service_open_time?->format('H:i');
        $close = $this->service_close_time?->format('H:i');

        return trim($days . ' ' . ($open && $close ? "{$open} - {$close}" : ''));
    }

    public function getIsCompleteAttribute(): bool
    {
        return $this->status_flag === 'complete';
    }
}