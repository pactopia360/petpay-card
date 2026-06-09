<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomerProfile extends Model
{
    protected $fillable = [
        'user_id',
        'main_address',
        'main_latitude',
        'main_longitude',
        'pawpoints_balance',
        'is_petpay_plus',
    ];

    protected function casts(): array
    {
        return [
            'main_latitude' => 'decimal:7',
            'main_longitude' => 'decimal:7',
            'pawpoints_balance' => 'integer',
            'is_petpay_plus' => 'boolean',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function pets()
    {
        return $this->hasMany(CustomerPetProfile::class);
    }
}