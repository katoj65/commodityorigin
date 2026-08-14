<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CartItemResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $market = $this->whenLoaded('market');
        $unitPrice = (float) $this->unit_price;
        $currentPrice = $market ? (float) $market->price_per_kg : null;

        return [
            'id' => $this->id,
            'market_id' => $this->market_id,
            'status' => $this->status,
            'quantity' => (int) $this->quantity,
            'unit_price' => $unitPrice,
            'line_total' => round($unitPrice * $this->quantity, 2),
            'current_price' => $currentPrice,
            'available_quantity' => $market ? (float) $market->quantity : null,
            'lot_code' => $market?->lot_code,
            'name' => $market?->name,
            'origin' => $market?->origin,
            'type' => $market?->type,
            'process' => $market?->process,
            'quality_score' => $market?->quality_score !== null ? (float) $market->quality_score : null,
            'image' => $market?->image,
            'seller_name' => $this->whenLoaded('market', fn () => $market->user?->name),
            'created_at' => optional($this->created_at)?->toDateTimeString(),
        ];
    }
}
