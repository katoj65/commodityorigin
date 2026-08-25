<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LotBatchResource extends JsonResource
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
            'batch_id' => $this->batch_id,
            'batch_number' => $this->batch_number,
            'allocation_kg' => $this->allocation_kg,
            'user_id' => $this->user_id,
            'created_at' => optional($this->created_at)?->toDateTimeString(),
            'batch' => $this->whenLoaded('batch', fn (): ?array => $this->batch ? BatchResource::make($this->batch)->resolve() : null),
            'lot' => $this->whenLoaded('lot', fn (): ?array => $this->lot ? LotResource::make($this->lot)->resolve() : null),
        ];
    }
}
