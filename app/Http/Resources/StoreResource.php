<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StoreResource extends JsonResource
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
            'owner_name' => $this->whenLoaded('user', fn () => $this->user?->name),
            'verification_status' => $this->verification_status,
            'verified_by' => $this->whenLoaded('verifiedBy', fn () => $this->verifiedBy?->name),
            'verified_at' => optional($this->verified_at)?->toDateTimeString(),
            'rejection_reason' => $this->rejection_reason,
            'items_count' => $this->when(isset($this->items_count), (int) $this->items_count),
            'created_at' => optional($this->created_at)?->toDateTimeString(),
            'updated_at' => optional($this->updated_at)?->toDateTimeString(),
        ];
    }
}
