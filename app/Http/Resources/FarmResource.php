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
            'location' => $this->location,
            'size' => $this->size,
            'altitude' => $this->altitude,
            'variety' => $this->variety,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'status' => $this->status,
            'notes' => $this->notes,
            'created_at' => optional($this->created_at)?->toDateTimeString(),
            'updated_at' => optional($this->updated_at)?->toDateTimeString(),
            'farmer' => $this->whenLoaded('farmer', function (): array {
                return [
                    'id' => $this->farmer?->id,
                    'first_name' => $this->farmer?->first_name,
                    'last_name' => $this->farmer?->last_name,
                    'telephone' => $this->farmer?->telephone,
                    'email' => $this->farmer?->email,
                    'district' => $this->farmer?->district,
                    'sub_county' => $this->farmer?->sub_county,
                    'coffee_type' => $this->farmer?->coffee_type,
                    'cooperative' => $this->farmer?->cooperative,
                    'farm_size' => $this->farmer?->farm_size,
                ];
            }),
        ];
    }
}
