<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FarmOwnerResource extends JsonResource
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
            'first_name' => $this->first_name,
            'middle_name' => $this->middle_name,
            'last_name' => $this->last_name,
            'name' => $this->name,
            'national_id' => $this->national_id,
            'tel' => $this->tel,
            'email' => $this->email,
            'ownership_percentage' => $this->ownership_percentage !== null ? (float) $this->ownership_percentage : null,
            'is_primary' => (bool) $this->is_primary,
            'recorded_by' => $this->whenLoaded('user', fn (): ?array => $this->user ? [
                'id' => $this->user->id,
                'name' => $this->user->name,
            ] : null),
            'created_at' => optional($this->created_at)?->toDateTimeString(),
        ];
    }
}
