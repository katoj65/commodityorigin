<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EscrowAccountResource extends JsonResource
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
            'order_id' => $this->order_id,
            'order_type' => $this->order_type,
            'order_number' => $this->whenLoaded('order', fn () => $this->order?->order_number),
            'crop_type' => $this->whenLoaded('order', fn () => $this->order?->crop_type),
            'buyer_id' => $this->buyer_id,
            'buyer_name' => $this->whenLoaded('buyer', fn () => $this->buyer?->name),
            'seller_id' => $this->seller_id,
            'seller_name' => $this->whenLoaded('seller', fn () => $this->seller?->name),
            'amount' => $this->amount,
            'currency' => $this->currency,
            'status' => $this->status,
            'held_at' => optional($this->held_at)?->toDateTimeString(),
            'released_at' => optional($this->released_at)?->toDateTimeString(),
            'released_by_name' => $this->whenLoaded('releasedBy', fn () => $this->releasedBy?->name),
            'created_at' => optional($this->created_at)?->toDateTimeString(),
        ];
    }
}
