<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OfferOrderResource extends JsonResource
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
            'offer_id' => $this->offer_id,
            'market_id' => $this->market_id,
            'quantity' => (float) $this->quantity,
            'unit_price' => (float) $this->unit_price,
            'incoterm' => $this->incoterm,
            'notes' => $this->notes,
            'status' => $this->status,
            'executed_at' => optional($this->executed_at)?->toDateTimeString(),
            'created_at' => optional($this->created_at)?->toDateTimeString(),
            'updated_at' => optional($this->updated_at)?->toDateTimeString(),
            'user' => $this->whenLoaded('user', fn () => [
                'id' => $this->user->id,
                'name' => $this->user->name,
            ]),
            'offer' => $this->whenLoaded('offer', fn () => [
                'id' => $this->offer->id,
                'offer_number' => $this->offer->offer_number,
            ]),
            'market' => $this->whenLoaded('market', fn () => [
                'id' => $this->market->id,
                'name' => $this->market->name,
                'origin' => $this->market->origin,
            ]),
        ];
    }
}
