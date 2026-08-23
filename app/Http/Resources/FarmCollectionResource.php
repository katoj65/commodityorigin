<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FarmCollectionResource extends JsonResource
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
            'collection_date' => optional($this->collection_date)?->toDateString(),
            'coffee_type' => $this->coffee_type,
            'variety' => $this->variety,
            'harvest_season' => $this->harvest_season,
            'quantity' => $this->quantity,
            'unit' => $this->unit,
            'initial_moisture' => $this->initial_moisture,
            'initial_defects' => $this->initial_defects,
            'initial_grade' => $this->initial_grade,
            'initial_quality_score' => $this->initial_quality_score,
            'collection_price' => $this->collection_price,
            'currency' => $this->currency,
            'payment_status' => $this->payment_status,
            'reference' => $this->reference,
            'notes' => $this->notes,
            'created_at' => optional($this->created_at)?->toDateTimeString(),
            'updated_at' => optional($this->updated_at)?->toDateTimeString(),
            'farm' => $this->whenLoaded('farm', fn (): array => FarmResource::make($this->farm)->resolve()),
        ];
    }
}
