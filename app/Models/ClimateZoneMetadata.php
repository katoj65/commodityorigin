<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClimateZoneMetadata extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'description',
        'altitude_min',
        'altitude_max',
        'rainfall_range',
        'temperature_range',
        'humidity_range',
        'coffee_suitability',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'is_active'    => 'boolean',
        'altitude_min' => 'integer',
        'altitude_max' => 'integer',
        'sort_order'   => 'integer',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true)->orderBy('sort_order');
    }
}
