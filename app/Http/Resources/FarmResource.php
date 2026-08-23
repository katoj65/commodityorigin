<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FarmResource extends JsonResource
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
            'farmer_id' => $this->farmer_id,
            'name' => $this->name,
            'farm_code' => $this->farm_code,
            'country' => $this->country,
            'region' => $this->region,
            'district' => $this->district,
            'county' => $this->county,
            'subcounty' => $this->subcounty,
            'parish' => $this->parish,
            'village' => $this->village,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'elevation' => $this->elevation,
            'total_area' => $this->total_area,
            'coffee_area' => $this->coffee_area,
            'coffee_type' => $this->coffee_type,
            'tel' => $this->tel,
            'email' => $this->email,
            'status' => $this->status,
            'water_conservation_percentage' => $this->water_conservation_percentage,
            'carbon_sequestration' => $this->carbon_sequestration,
            'soil_health_index' => $this->soil_health_index,
            'soil_type' => $this->soil_type,
            'soil_metadata_id' => $this->soil_metadata_id,
            'climate_zone_metadata_id' => $this->climate_zone_metadata_id,
            'soil' => $this->whenLoaded('soil', fn () => $this->soil ? SoilMetadataResource::make($this->soil)->resolve() : null),
            'climate_zone' => $this->whenLoaded('climateZone', fn () => $this->climateZone ? ClimateZoneMetadataResource::make($this->climateZone)->resolve() : null),
            'crop_varieties' => $this->whenLoaded('cropVarieties', fn () => $this->cropVarieties->map(fn ($v) => [
                'id' => $v->id,
                'name' => $v->name,
                'description' => $v->description,
            ])->all()),
            'certifications' => $this->whenLoaded('certifications', fn () => $this->certifications->map(fn ($c) => [
                'id' => $c->id,
                'slug' => $c->slug,
                'name' => $c->name,
                'description' => $c->description,
            ])->all()),
            'harvests_count' => $this->when(isset($this->harvests_count), $this->harvests_count),
            'created_at' => optional($this->created_at)?->toDateTimeString(),
            'updated_at' => optional($this->updated_at)?->toDateTimeString(),
            'farmer' => $this->whenLoaded('farmer', fn () => $this->farmer ? FarmerResource::make($this->farmer)->resolve() : null),
        ];
    }
}
