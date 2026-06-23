<?php

namespace App\Models\Comercio;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CommerceContact extends Model
{
    use HasFactory;

    protected $connection = 'mysql_comercio';

    protected $table = 'commerce_contacts';

    protected $fillable = [
        'commerce_user_id',

        'first_name',
        'last_name_paternal',
        'last_name_maternal',

        'street',
        'neighborhood',
        'postal_code',
        'state',

        'phone',
        'email',

        'phone_verified_at',
        'email_verified_at',

        'is_primary',
    ];

    protected $casts = [
        'phone_verified_at' => 'datetime',
        'email_verified_at' => 'datetime',
        'is_primary' => 'boolean',
    ];

    public function commerce(): BelongsTo
    {
        return $this->belongsTo(CommerceUser::class, 'commerce_user_id');
    }

    public function getFullNameAttribute(): string
    {
        return trim(collect([
            $this->first_name,
            $this->last_name_paternal,
            $this->last_name_maternal,
        ])->filter()->implode(' '));
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
}