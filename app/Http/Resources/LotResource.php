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
            'batch_id' => $this->batch_id,
            'user_id' => $this->user_id,
            'lot_number' => $this->lot_number,
            'lot_name' => $this->lot_name,
            'process' => $this->process,
            'grade' => $this->grade,
            'allocation_kg' => $this->allocation_kg,
            'net_weight_kg' => $this->net_weight_kg,
            'quantity_bags' => $this->quantity_bags,
            'bag_weight_kg' => $this->bag_weight_kg,
            'price' => $this->price,
            'quality_score' => $this->quality_score,
            'warehouse' => $this->warehouse,
            'packaging_type' => $this->packaging_type,
            'screen_size' => $this->screen_size,
            'altitude' => $this->altitude,
            'aroma_score' => $this->aroma_score,
            'acidity_score' => $this->acidity_score,
            'body_score' => $this->body_score,
            'target_market' => $this->target_market,
            'price_per_kg' => $this->price_per_kg,
            'tokenize' => $this->tokenize,
            'status' => $this->status,
            'notes' => $this->notes,
            'created_at' => optional($this->created_at)?->toDateTimeString(),
            'updated_at' => optional($this->updated_at)?->toDateTimeString(),
            'batch' => $this->whenLoaded('batch', function (): ?array {
                if (! $this->batch) {
                    return null;
                }

                return [
                    'id' => $this->batch->id,
                    'batch_number' => $this->batch->batch_number,
                    'variety' => $this->batch->variety,
                    'warehouse_location' => $this->batch->warehouse_location,
                ];
            }),
        ];
    }
}
