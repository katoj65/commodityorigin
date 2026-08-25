<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BatchFarmCollectionResource extends JsonResource
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
            'farm_collection_id' => $this->farm_collection_id,
            'farm_collection_code' => $this->farm_collection_code,
            'user_id' => $this->user_id,
            'status' => $this->status,
            'batch' => $this->whenLoaded('batch', fn (): array => BatchResource::make($this->batch)->resolve()),
            'farm_collection' => $this->whenLoaded('farmCollection', fn (): array => FarmCollectionResource::make($this->farmCollection)->resolve()),
            'created_at' => optional($this->created_at)?->toDateTimeString(),
            'updated_at' => optional($this->updated_at)?->toDateTimeString(),
        ];
    }
}
