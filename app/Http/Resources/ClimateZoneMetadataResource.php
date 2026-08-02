<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ClimateZoneMetadataResource extends JsonResource
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
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'altitude_min' => $this->altitude_min,
            'altitude_max' => $this->altitude_max,
            'rainfall_range' => $this->rainfall_range,
            'temperature_range' => $this->temperature_range,
            'humidity_range' => $this->humidity_range,
            'coffee_suitability' => $this->coffee_suitability,
        ];
    }
}
