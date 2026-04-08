<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FarmerResource extends JsonResource
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
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'telephone' => $this->telephone,
            'email' => $this->email,
            'district' => $this->district,
            'sub_county' => $this->sub_county,
            'coffee_type' => $this->coffee_type,
            'cooperative' => $this->cooperative,
            'farm_size' => $this->farm_size,
            'notes' => $this->notes,
            'farms_count' => $this->when(isset($this->farms_count), (int) $this->farms_count),
            'created_at' => optional($this->created_at)?->toDateTimeString(),
            'updated_at' => optional($this->updated_at)?->toDateTimeString(),
            'farms' => $this->whenLoaded('farms', fn () => FarmResource::collection($this->farms)->resolve()),
        ];
    }
}
