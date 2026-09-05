<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OfferResponseResource extends JsonResource
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
            'offer_id' => $this->offer_id,
            'user_id' => $this->user_id,
            'user_name' => $this->whenLoaded('user', fn () => $this->user?->name),
            'order_owner_id' => $this->order_owner_id,
            'owner_name' => $this->whenLoaded('owner', fn () => $this->owner?->name),
            'offer_number' => $this->whenLoaded('offer', fn () => $this->offer?->offer_number),
            'crop_type' => $this->whenLoaded('offer', fn () => $this->offer?->crop_type),
            'variety' => $this->whenLoaded('offer', fn () => $this->offer?->variety),
            'grade' => $this->whenLoaded('offer', fn () => $this->offer?->grade),
            'quantity' => $this->whenLoaded('offer', fn () => (float) $this->offer?->quantity),
            'total_amount' => $this->whenLoaded('offer', fn () => (float) $this->offer?->total_amount),
            'currency' => $this->whenLoaded('offer', fn () => $this->offer?->currency),
            'message' => $this->message,
            'status' => $this->status,
            'created_at' => optional($this->created_at)?->toDateTimeString(),
        ];
    }
}
