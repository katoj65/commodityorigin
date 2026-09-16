<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BatchStorageResource extends JsonResource
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
            'location' => $this->location,
            'storage_bay' => $this->storage_bay,
            'date_stored' => optional($this->date_stored)?->toDateString(),
            'quantity_stored_kg' => $this->quantity_stored_kg,
            'climate_ambient' => $this->climate_ambient,
            'physical_pallet' => $this->physical_pallet,
            'packaging_spec' => $this->packaging_spec,
            'created_at' => optional($this->created_at)?->toDateTimeString(),
            'updated_at' => optional($this->updated_at)?->toDateTimeString(),
        ];
    }
}
