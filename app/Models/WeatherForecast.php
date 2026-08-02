<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WeatherForecast extends Model
{
    protected $fillable = [
        'region',
        'forecast_date',
        'condition',
        'temperature_min',
        'temperature_max',
        'rainfall_mm',
        'humidity_percentage',
        'wind_speed_kmh',
        'advisory',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'forecast_date'        => 'date',
        'temperature_min'      => 'decimal:1',
        'temperature_max'      => 'decimal:1',
        'rainfall_mm'          => 'decimal:1',
        'humidity_percentage'  => 'integer',
        'wind_speed_kmh'       => 'decimal:1',
        'is_active'            => 'boolean',
        'sort_order'           => 'integer',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('forecast_date');
    }
}
