<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StoreItemStatusLogResource extends JsonResource
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
            'from_status' => $this->from_status,
            'to_status' => $this->to_status,
            'changed_by' => $this->whenLoaded('changedBy', fn () => $this->changedBy?->name),
            'notes' => $this->notes,
            'created_at' => optional($this->created_at)?->toDateTimeString(),
        ];
    }
}
