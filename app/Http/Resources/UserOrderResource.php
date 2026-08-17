<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserOrderResource extends JsonResource
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
            'items' => $this->items,
            'total_amount' => (float) $this->total_amount,
            'currency' => $this->currency,
            'payment_method' => $this->payment_method,
            'status' => $this->status,
            'cancelled_at' => optional($this->cancelled_at)?->toDateTimeString(),
            'created_at' => optional($this->created_at)?->toDateTimeString(),
        ];
    }
}
