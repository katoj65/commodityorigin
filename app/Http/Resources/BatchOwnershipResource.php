<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BatchOwnershipResource extends JsonResource
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
            'owner_id' => $this->owner_id,
            'owner_type' => $this->owner_type,
            'created_at' => optional($this->created_at)?->toDateTimeString(),
            'updated_at' => optional($this->updated_at)?->toDateTimeString(),
            'batch' => $this->whenLoaded('batch', function (): ?array {
                if (!$this->batch) {
                    return null;
                }

                return [
                    'id' => $this->batch->id,
                    'batch_number' => $this->batch->batch_number,
                    'status' => $this->batch->status,
                ];
            }),
        ];
    }
}
