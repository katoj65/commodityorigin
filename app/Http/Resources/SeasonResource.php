<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SeasonResource extends JsonResource
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
            'name' => $this->name,
            'region' => $this->region,
            'harvests_count' => $this->harvests_count !== null ? (int) $this->harvests_count : null,
            'harvests_sum_weight' => $this->harvests_sum_weight !== null ? (float) $this->harvests_sum_weight : null,
            'harvests_sum_price' => $this->harvests_sum_price !== null ? (float) $this->harvests_sum_price : null,
            'start_date' => optional($this->start_date)?->toDateString(),
            'end_date' => optional($this->end_date)?->toDateString(),
            'status' => $this->status,
            'notes' => $this->notes,
            'created_at' => optional($this->created_at)?->toDateTimeString(),
            'updated_at' => optional($this->updated_at)?->toDateTimeString(),
        ];
    }
}
