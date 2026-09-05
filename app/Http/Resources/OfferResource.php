<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OfferResource extends JsonResource
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
            'offer_number' => $this->offer_number,
            'seller_id' => $this->seller_id,
            'buyer_id' => $this->buyer_id,
            'seller_name' => $this->whenLoaded('seller', fn () => $this->seller?->name),
            'buyer_name' => $this->whenLoaded('buyer', fn () => $this->buyer?->name),
            'crop_type' => $this->crop_type,
            'variety' => $this->variety,
            'grade' => $this->grade,
            'quantity' => (float) $this->quantity,
            'unit_price' => (float) $this->unit_price,
            'total_amount' => (float) $this->total_amount,
            'currency' => $this->currency,
            'notes' => $this->notes,
            'status' => $this->status,
            'created_at' => optional($this->created_at)?->toDateTimeString(),
            'updated_at' => optional($this->updated_at)?->toDateTimeString(),
        ];
    }
}
