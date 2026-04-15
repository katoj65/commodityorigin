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
            'pick_method' => $this->pick_method,
            'price' => $this->price,
            'weight' => $this->weight,
            'ripeness_grade' => $this->ripeness_grade,
            'created_at' => optional($this->created_at)?->toDateTimeString(),
            'updated_at' => optional($this->updated_at)?->toDateTimeString(),
            'farm' => $this->whenLoaded('farm', function (): array {
                return [
                    'id' => $this->farm?->id,
                    'name' => $this->farm?->name,
                    'location' => $this->farm?->location,
                    'variety' => $this->farm?->variety,
                    'farmer_name' => trim(collect([
                        $this->farm?->farmer?->first_name,
                        $this->farm?->farmer?->last_name,
                    ])->filter()->implode(' ')) ?: null,
                ];
            }),
        ];
    }
}
