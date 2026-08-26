<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FarmerFarmResource extends JsonResource
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
            'farmer_id' => $this->farmer_id,
            'farm_code' => $this->farm_code,
            'status' => $this->status,
            'farm' => $this->whenLoaded('farm', fn (): array => FarmResource::make($this->farm)->resolve()),
            'farmer' => $this->whenLoaded('farmer', fn (): array => FarmerResource::make($this->farmer)->resolve()),
            'created_at' => optional($this->created_at)?->toDateTimeString(),
            'updated_at' => optional($this->updated_at)?->toDateTimeString(),
        ];
    }
}
