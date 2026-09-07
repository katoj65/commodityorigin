<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserFarmOwnershipResource extends JsonResource
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
            'user_id' => $this->user_id,
            'first_name' => $this->whenLoaded('user', fn () => $this->user->first_name),
            'last_name' => $this->whenLoaded('user', fn () => $this->user->last_name),
            'name' => $this->whenLoaded('user', fn () => $this->user->name),
            'email' => $this->whenLoaded('user', fn () => $this->user->email),
            'tel' => $this->whenLoaded('user', fn () => $this->user->telephone),
            'national_id' => $this->whenLoaded('user', fn () => $this->user->national_id),
            'ownership_percentage' => $this->ownership_percentage !== null ? (float) $this->ownership_percentage : null,
            'is_primary' => (bool) $this->is_primary,
            'created_at' => optional($this->created_at)?->toDateTimeString(),
        ];
    }
}
