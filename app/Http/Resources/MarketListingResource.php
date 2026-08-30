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
            'name' => $this->title,
            'origin' => $this->origin,
            'type' => $this->type,
            'process' => $this->process,
            'quality_score' => (float) ($this->quality_score ?? 0),
            'quantity' => (float) ($this->quantity ?? 0),
            'available_quantity' => (float) ($this->available_quantity ?? 0),
            'unit' => $this->unit,
            'currency' => $this->currency,
            'price_per_kg' => (float) ($this->price_per_unit ?? 0),
            'pricing_type' => $this->pricing_type,
            'demand' => $this->demand,
            'badges' => $this->badges ?? [],
            'is_featured' => (bool) $this->is_featured,
            'image' => $this->image,
        ];
    }
}
