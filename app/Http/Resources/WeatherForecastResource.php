<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WeatherForecastResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'region' => $this->region,
            'forecast_date' => optional($this->forecast_date)?->toDateString(),
            'condition' => $this->condition,
            'temperature_min' => $this->temperature_min !== null ? (float) $this->temperature_min : null,
            'temperature_max' => $this->temperature_max !== null ? (float) $this->temperature_max : null,
            'rainfall_mm' => $this->rainfall_mm !== null ? (float) $this->rainfall_mm : null,
            'humidity_percentage' => $this->humidity_percentage,
            'wind_speed_kmh' => $this->wind_speed_kmh !== null ? (float) $this->wind_speed_kmh : null,
            'advisory' => $this->advisory,
        ];
    }
}
