<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StoreItemResource extends JsonResource
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
            'store_id' => $this->store_id,
            'name' => $this->name,
            'sku' => $this->sku,
            'category' => $this->category,
            'description' => $this->description,
            'price' => (float) $this->price,
            'currency_code' => $this->currency_code,
            'quantity' => $this->quantity,
            'unit' => $this->unit,
            'image_url' => $this->image_url,
            'status' => $this->status,
            'notes' => $this->notes,
            'seller_name' => $this->whenLoaded('store', fn () => $this->store?->user?->name),
            'status_logs' => $this->whenLoaded('statusLogs', fn () => StoreItemStatusLogResource::collection($this->statusLogs)->resolve()),
            'created_at' => optional($this->created_at)?->toDateTimeString(),
            'updated_at' => optional($this->updated_at)?->toDateTimeString(),
        ];
    }
}
