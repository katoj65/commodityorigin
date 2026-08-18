<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MarketListingResource extends JsonResource
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
            'lot_code' => $this->lot_code,
            'name' => $this->name,
            'origin' => $this->origin,
            'type' => $this->type,
            'process' => $this->process,
            'quality_score' => (float) ($this->quality_score ?? 0),
            'quantity' => (float) ($this->quantity ?? 0),
            'price_per_kg' => (float) ($this->price_per_kg ?? 0),
            'demand' => $this->demand,
            'badges' => $this->badges ?? [],
            'image' => $this->image,
        ];
    }
}
