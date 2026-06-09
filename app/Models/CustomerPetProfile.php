<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomerPetProfile extends Model
{
    protected $fillable = [
        'customer_profile_id',
        'name',
        'species',
        'breed',
        'birthdate',
        'size',
        'photo_path',
    ];

    protected function casts(): array
    {
        return [
            'birthdate' => 'date',
        ];
    }

    public function customerProfile()
    {
        return $this->belongsTo(CustomerProfile::class);
    }
}