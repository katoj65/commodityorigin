<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
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
            'order_number' => $this->order_number,
            'type' => $this->type,
            'buyer_id' => $this->buyer_id,
            'seller_id' => $this->seller_id,
            'buyer_name' => $this->whenLoaded('buyer', fn () => $this->buyer?->name),
            'buyer_email' => $this->whenLoaded('buyer', fn () => $this->buyer?->email),
            'buyer_phone' => $this->whenLoaded('buyer', fn () => $this->buyer?->telephone),
            'seller_name' => $this->whenLoaded('seller', fn () => $this->seller?->name),
            'seller_email' => $this->whenLoaded('seller', fn () => $this->seller?->email),
            'seller_phone' => $this->whenLoaded('seller', fn () => $this->seller?->telephone),
            'crop_type' => $this->crop_type,
            'variety' => $this->variety,
            'grade' => $this->grade,
            'quantity' => (float) $this->quantity,
            'unit_price' => (float) $this->unit_price,
            'total_amount' => (float) $this->total_amount,
            'currency' => $this->currency,
            'payment_method' => $this->payment_method,
            'notes' => $this->notes,
            'status' => $this->status,
            'created_at' => optional($this->created_at)?->toDateTimeString(),
            'updated_at' => optional($this->updated_at)?->toDateTimeString(),
        ];
    }
}
