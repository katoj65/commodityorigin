<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LotResource extends JsonResource
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
            'lot_number' => $this->lot_number,
            'lot_name' => $this->lot_name,
            'description' => $this->description,
            'image' => $this->image,
            'process' => $this->process,
            'grade' => $this->grade,
            'net_weight_kg' => $this->net_weight_kg,
            'quantity_bags' => $this->quantity_bags,
            'bag_weight_kg' => $this->bag_weight_kg,
            'price' => $this->price,
            'quality_score' => $this->quality_score,
            'packaging_type' => $this->packaging_type,
            'status' => $this->status,
            'notes' => $this->notes,
            'created_at' => optional($this->created_at)?->toDateTimeString(),
            'updated_at' => optional($this->updated_at)?->toDateTimeString(),
            'lot_batches' => $this->whenLoaded('lotBatches', fn (): array => LotBatchResource::collection($this->lotBatches)->resolve()),
        ];
    }
}
