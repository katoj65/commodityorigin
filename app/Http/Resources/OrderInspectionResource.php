<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderInspectionResource extends JsonResource
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
            'status' => $this->status,
            'requested_by' => $this->requested_by,
            'requested_by_name' => $this->whenLoaded('requestedBy', fn () => $this->requestedBy?->name),
            'order_number' => $this->whenLoaded('order', fn () => $this->order?->order_number),
            'crop_type' => $this->whenLoaded('order', fn () => $this->order?->crop_type),
            'buyer_id' => $this->whenLoaded('order', fn () => $this->order?->buyer_id),
            'buyer_name' => $this->whenLoaded('order', fn () => $this->order?->buyer?->name),
            'seller_id' => $this->whenLoaded('order', fn () => $this->order?->seller_id),
            'seller_name' => $this->whenLoaded('order', fn () => $this->order?->seller?->name),
            'buyer_acknowledged_at' => optional($this->buyer_acknowledged_at)?->toDateTimeString(),
            'completed_by_name' => $this->whenLoaded('completedBy', fn () => $this->completedBy?->name),
            'completed_at' => optional($this->completed_at)?->toDateTimeString(),
            'created_at' => optional($this->created_at)?->toDateTimeString(),
        ];
    }
}
