<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DriverProfile extends Model
{
    protected $fillable = [
        'user_id',
        'vehicle_type',
        'vehicle_plate',
        'operation_zone',
        'current_latitude',
        'current_longitude',
        'approval_status',
        'is_available',
        'delivery_commission_percent',
    ];

    protected function casts(): array
    {
        return [
            'current_latitude' => 'decimal:7',
            'current_longitude' => 'decimal:7',
            'is_available' => 'boolean',
            'delivery_commission_percent' => 'decimal:2',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}