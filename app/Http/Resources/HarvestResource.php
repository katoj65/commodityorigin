<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

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
            'season_id' => $this->season_id,
            'variety' => $this->variety,
            'date_planted' => optional($this->date_planted)?->toDateString(),
            'harvest_date' => optional($this->harvest_date)?->toDateString(),
            'harvest_season' => $this->harvest_season,
            'status' => $this->status,
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
            'season' => $this->whenLoaded('season', fn (): ?array => $this->season
                ? SeasonResource::make($this->season)->resolve()
                : null),
            'sustainability' => $this->whenLoaded('sustainability', fn (): array => [
                'organicCertified' => (bool) $this->sustainability?->organic_certified,
                'climateSmart' => (bool) $this->sustainability?->climate_smart,
                'shadeGrown' => (bool) $this->sustainability?->shade_grown,
                'waterManagement' => (bool) $this->sustainability?->water_management,
                'soilConservation' => (bool) $this->sustainability?->soil_conservation,
                'lowCarbon' => (bool) $this->sustainability?->low_carbon,
                'fairWages' => (bool) $this->sustainability?->fair_wages,
                'notes' => $this->sustainability?->notes,
            ]),
            'harvest_documents' => $this->whenLoaded('documents', fn () => $this->documents->map(
                fn ($document): array => [
                    'id' => $document->id,
                    'title' => $document->title,
                    'document_type' => $document->document_type,
                    'original_name' => $document->original_name,
                    'file_url' => Storage::disk('public')->url($document->file_path),
                    'created_at' => optional($document->created_at)?->toDateTimeString(),
                ],
            )->values()->all()),
        ];
    }
}
