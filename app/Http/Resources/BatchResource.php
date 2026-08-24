<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BatchResource extends JsonResource
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
            'batch_number' => $this->batch_number,
            'variety' => $this->variety,
            'warehouse_location' => $this->warehouse_location,
            'quantity_bags' => $this->quantity,
            'net_weight_kg' => $this->weight,
            'price' => $this->price,
            'currency' => $this->currency,
            'moisture_content' => $this->moisture_content,
            'processing_date' => optional($this->processing_date)?->toDateString(),
            'processing_method' => $this->processing_method,
            'drying_method' => $this->drying_method,
            'drying_duration' => $this->drying_duration,
            'milling_status' => $this->milling_status,
            'screen_size' => $this->screen_size,
            'defect_count' => $this->defect_count,
            'cup_score' => $this->cup_score,
            'status' => $this->status,
            'notes' => $this->notes,
            'can_manage' => $request->user() ? $request->user()->can('update', $this->resource) : false,
            'compliances' => $this->whenLoaded('compliances', fn (): array => BatchComplianceResource::collection($this->compliances)->resolve()),
            'created_at' => optional($this->created_at)?->toDateTimeString(),
            'updated_at' => optional($this->updated_at)?->toDateTimeString(),
        ];
    }
}
