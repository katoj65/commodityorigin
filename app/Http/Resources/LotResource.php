<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LotResource extends JsonResource
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
            'farm_id' => $this->farm_id,
            'lot_number' => $this->lot_number,
            'process' => $this->process,
            'grade' => $this->grade,
            'quantity_bags' => $this->quantity_bags,
            'bag_weight_kg' => $this->bag_weight_kg,
            'reserve_price' => $this->reserve_price,
            'quality_score' => $this->quality_score,
            'status' => $this->status,
            'notes' => $this->notes,
            'created_at' => optional($this->created_at)?->toDateTimeString(),
            'updated_at' => optional($this->updated_at)?->toDateTimeString(),
            'farm' => $this->whenLoaded('farm', function (): ?array {
                if (!$this->farm) {
                    return null;
                }

                return [
                    'id' => $this->farm->id,
                    'name' => $this->farm->name,
                    'location' => $this->farm->location,
                    'size' => $this->farm->size,
                    'altitude' => $this->farm->altitude,
                    'variety' => $this->farm->variety,
                    'status' => $this->farm->status,
                    'farmer' => $this->farm->relationLoaded('farmer') && $this->farm->farmer
                        ? [
                            'id' => $this->farm->farmer->id,
                            'first_name' => $this->farm->farmer->first_name,
                            'last_name' => $this->farm->farmer->last_name,
                            'telephone' => $this->farm->farmer->telephone,
                            'district' => $this->farm->farmer->district,
                            'sub_county' => $this->farm->farmer->sub_county,
                        ]
                        : null,
                ];
            }),
        ];
    }
}
