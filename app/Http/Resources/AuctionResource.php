<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AuctionResource extends JsonResource
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
            'lot_id' => $this->lot_id,
            'user_id' => $this->user_id,
            'seller_name' => $this->whenLoaded('user', fn () => $this->user?->name),
            'lot_code' => $this->lot_code,
            'name' => $this->name,
            'origin' => $this->origin,
            'type' => $this->type,
            'process' => $this->process,
            'quality_score' => $this->quality_score !== null ? (float) $this->quality_score : null,
            'quantity' => (float) $this->quantity,
            'price_per_kg' => (float) $this->price_per_kg,
            'demand' => $this->demand,
            'badges' => $this->badges ?? [],
            'target_market' => $this->target_market,
            'status' => $this->status,
            'notes' => $this->notes,
            'image' => $this->image,
            'created_at' => optional($this->created_at)?->toDateTimeString(),
            'updated_at' => optional($this->updated_at)?->toDateTimeString(),
        ];
    }
}
