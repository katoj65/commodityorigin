<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class HarvestResource extends JsonResource
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
            'user_id' => $this->user_id,
            'farm_id' => $this->farm_id,
            'variety' => $this->variety,
            'date_planted' => optional($this->date_planted)?->toDateString(),
            'harvest_date' => optional($this->harvest_date)?->toDateString(),
            'harvest_season' => $this->harvest_season,
            'pick_method' => $this->pick_method,
            'price' => $this->price,
            'weight' => $this->weight,
            'ripeness_percentage' => $this->ripeness_percentage !== null ? (float) $this->ripeness_percentage : null,
            'foreign_matter_present' => (bool) $this->foreign_matter_present,
            'pest_damage' => (bool) $this->pest_damage,
            'disease_signs' => (bool) $this->disease_signs,
            'visible_defects' => (bool) $this->visible_defects,
            'created_at' => optional($this->created_at)?->toDateTimeString(),
            'updated_at' => optional($this->updated_at)?->toDateTimeString(),
            'farm' => $this->whenLoaded('farm', fn (): array => FarmResource::make($this->farm)->resolve()),
        ];
    }
}
