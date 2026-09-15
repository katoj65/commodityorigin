<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LotBatchFarmCollectionResource extends JsonResource
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
            'farm_collection_id' => $this->farm_collection_id,
            'status' => $this->status,
            'batch' => $this->whenLoaded('batch', fn (): ?array => $this->batch ? BatchResource::make($this->batch)->resolve() : null),
            'farm_collection' => $this->whenLoaded('farmCollection', fn (): ?array => $this->farmCollection ? FarmCollectionResource::make($this->farmCollection)->resolve() : null),
            'created_at' => optional($this->created_at)?->toDateTimeString(),
            'updated_at' => optional($this->updated_at)?->toDateTimeString(),
        ];
    }
}
