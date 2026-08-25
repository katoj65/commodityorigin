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
            'collection_code' => $this->collection_code,
            'status' => $this->status,
            'farm_id' => $this->farm_id,
            'user_id' => $this->user_id,
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
            'can_manage' => $request->user() ? $request->user()->can('update', $this->resource) : false,
            'created_at' => optional($this->created_at)?->toDateTimeString(),
            'updated_at' => optional($this->updated_at)?->toDateTimeString(),
            'farm' => $this->whenLoaded('farm', fn (): array => FarmResource::make($this->farm)->resolve()),
            'user' => $this->whenLoaded('user', fn (): array => [
                'id' => $this->user->id,
                'name' => $this->user->name,
            ]),
        ];
    }
}
